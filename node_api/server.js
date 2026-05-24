/*
==================================================
ResumeIQ-X Node Pipeline Server v17
PHP + Node + Railway DB + Cloudinary
WebSocket realtime progress engine
==================================================
*/

require("dotenv").config({ path: require("path").resolve(__dirname, "../.env") });

const express   = require("express");
const mysql     = require("mysql2/promise");
const multer    = require("multer");
const cors      = require("cors");
const path      = require("path");
const http      = require("http");
const WebSocket = require("ws");
const { spawn, execFile } = require("child_process");

const app    = express();
const server = http.createServer(app);
const wss    = new WebSocket.Server({ server });

app.use(cors({ origin: "*" }));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const upload = multer({ dest: path.join(__dirname, "uploads") });

/* ── PORT ─────────────────────────────────────────────────────────────────── */
const PORT = process.env.NODE_API_PORT || 5000;

/* ── PYTHON PATHS ─────────────────────────────────────────────────────────── */
const PROJECT_ROOT = path.resolve(__dirname, "..");
const isWindows    = process.platform === "win32";

const defaultPython = isWindows
    ? path.join(PROJECT_ROOT, "ai_engine_python", "venv", "Scripts", "python.exe")
    : path.join(PROJECT_ROOT, "ai_engine_python", "venv", "bin", "python3");

const defaultScript = path.join(PROJECT_ROOT, "ai_engine_python", "pipelines", "run_analysis.py");

function resolvePath(envVar, fallback) {
    const raw = process.env[envVar];
    if (!raw) return fallback;
    return path.isAbsolute(raw) ? raw : path.join(PROJECT_ROOT, raw);
}

const PYTHON_PATH = resolvePath("PYTHON_EXECUTABLE",     defaultPython);
const SCRIPT_PATH = resolvePath("PYTHON_PIPELINE_SCRIPT", defaultScript);

/* ── RAILWAY DB POOL ──────────────────────────────────────────────────────── */
// Uses MYSQLHOST / MYSQLPORT / MYSQLUSER / MYSQLPASSWORD / MYSQL_DATABASE
// On Railway cloud: these are set as env vars automatically
// On local dev:     they come from .env (public proxy host)
const dbPool = mysql.createPool({
    host:              process.env.MYSQLHOST     || process.env.DB_HOST,
    port:    parseInt(process.env.MYSQLPORT      || process.env.DB_PORT || "3306"),
    user:              process.env.MYSQLUSER     || process.env.DB_USER,
    password:          process.env.MYSQLPASSWORD || process.env.DB_PASS,
    database:          process.env.MYSQL_DATABASE || process.env.DB_NAME,
    waitForConnections: true,
    connectionLimit:    10,
    queueLimit:         0,
    connectTimeout:     20000,
});

// Test DB connection on startup
dbPool.getConnection()
    .then(conn => {
        console.log("✅ Railway DB connected successfully");
        conn.release();
    })
    .catch(err => {
        console.error("❌ Railway DB connection failed:", err.message);
        console.error("   Check MYSQLHOST / MYSQLPORT / MYSQLUSER / MYSQLPASSWORD / MYSQL_DATABASE in .env");
    });

/* ── WEBSOCKET CLIENT REGISTRY ────────────────────────────────────────────── */
const clients = new Map();

wss.on("connection", ws => {
    ws.on("message", msg => {
        try {
            const data = JSON.parse(msg);
            if (data.resume_id) clients.set(String(data.resume_id), ws);
        } catch {}
    });
    ws.on("close", () => {
        clients.forEach((v, k) => { if (v === ws) clients.delete(k); });
    });
});

/* ── STATUS UPDATE ────────────────────────────────────────────────────────── */
async function updateStatus(resume_id, status, progress) {
    try {
        await dbPool.execute(
            "UPDATE resumes SET analysis_status=?, analysis_progress=? WHERE id=?",
            [status, progress, resume_id]
        );
        const ws = clients.get(String(resume_id));
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ status, progress }));
        }
    } catch (err) {
        console.error("Status update failed:", err.message);
    }
}

/* ── PYTHON PIPELINE ──────────────────────────────────────────────────────── */
function runPipeline(resume_id, file_url) {
    updateStatus(resume_id, "processing", 10);
    console.log(`🐍 Starting pipeline for resume ${resume_id}: ${file_url}`);

    const python = spawn(PYTHON_PATH, [SCRIPT_PATH, resume_id, file_url]);

    python.stdout.on("data", d => console.log("[Python]", d.toString().trim()));
    python.stderr.on("data", d => console.error("[Python ERR]", d.toString().trim()));

    python.on("close", code => {
        if (code !== 0) {
            console.error(`Pipeline failed for resume ${resume_id} (exit ${code})`);
            updateStatus(resume_id, "failed", 0);
        } else {
            console.log(`✅ Pipeline complete for resume ${resume_id}`);
            updateStatus(resume_id, "completed", 100);
        }
    });
}

/* ── HEALTH CHECK ─────────────────────────────────────────────────────────── */
app.get("/health", async (req, res) => {
    let dbOk = false;
    try {
        const conn = await dbPool.getConnection();
        await conn.query("SELECT 1");
        conn.release();
        dbOk = true;
    } catch {}

    res.json({
        status:  "ok",
        server:  "ResumeIQ-X Node v17",
        db:      dbOk ? "connected" : "disconnected",
        python:  PYTHON_PATH,
        port:    PORT,
    });
});

/* ── PIPELINE TRIGGER ROUTE ───────────────────────────────────────────────── */
app.post("/api/upload", upload.single("resume"), async (req, res) => {
    try {
        const resume_id = req.body.resume_id;
        if (!resume_id) return res.json({ status: false, message: "resume_id required" });

        const [rows] = await dbPool.execute(
            "SELECT file_path FROM resumes WHERE id=?", [resume_id]
        );
        if (!rows.length) return res.json({ status: false, message: "Resume not found in DB" });

        const file_url = rows[0].file_path;

        // Respond instantly, run pipeline async
        res.json({ status: true, message: "Pipeline started" });
        setImmediate(() => runPipeline(resume_id, file_url));

    } catch (err) {
        console.error("Pipeline route error:", err.message);
        res.json({ status: false, message: err.message });
    }
});

/* ── START SERVER ─────────────────────────────────────────────────────────── */
server.listen(PORT, () => {
    console.log(`🚀 ResumeIQ-X Node server running on port ${PORT}`);
    console.log(`   DB Host: ${process.env.MYSQLHOST || process.env.DB_HOST}`);
    console.log(`   Python:  ${PYTHON_PATH}`);
    console.log(`   Health:  http://localhost:${PORT}/health`);
});
