<?php

// Debug logging
error_log("analysis_result_viewer.php accessed");
error_log("resume_id from GET: " . ($_GET["resume_id"] ?? "NOT SET"));

if(!isset($_GET["resume_id"]) || empty($_GET["resume_id"])){
    error_log("Redirecting to candidate_my_status.php - resume_id missing or empty");
    header("Location: candidate_my_status.php");
    exit();
}
$resumeId = intval($_GET["resume_id"]);
error_log("resume_id after intval: " . $resumeId);

if($resumeId === 0){
    error_log("Redirecting to candidate_my_status.php - resume_id is 0");
    header("Location: candidate_my_status.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>ResumeIQ-X | Intelligence Console</title>
    <script src="assets/js/chart.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #030712;
            color: #e2e8f0;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: rgba(10, 15, 30, 0.97);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255,255,255,0.07);
            padding: 2rem 1.6rem;
            color: white;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .logo-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            box-shadow: 0 0 16px rgba(99,102,241,0.5);
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.65rem 0.9rem;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.88rem;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .menu:hover {
            background: rgba(99,102,241,0.2);
            color: white;
            transform: translateX(4px);
        }

        /* Main content */
        .main {
            margin-left: 260px;
            padding: 2rem 2.5rem;
        }

        .header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.9rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            border-left: 4px solid #6366f1;
            padding-left: 16px;
            margin-bottom: 1.5rem;
        }

        /* Loading shimmer */
        .loading-shimmer {
            background: linear-gradient(110deg, rgba(255,255,255,0.03) 8%, rgba(255,255,255,0.07) 18%, rgba(255,255,255,0.03) 33%);
            background-size: 200% 100%;
            animation: shimmer 1.2s linear infinite;
            border-radius: 18px;
            height: 300px;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* KPI cards */
        .kpi {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(16px);
            border-radius: 18px;
            padding: 1.4rem 1.2rem;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(99,102,241,0.4);
            box-shadow: 0 16px 32px rgba(0,0,0,0.4);
        }

        .metric {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            color: rgba(255,255,255,0.38);
            margin-bottom: 0.5rem;
        }

        .value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #a5b4fc, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 0.8rem;
        }

        .progress {
            background: rgba(255,255,255,0.07);
            border-radius: 40px;
            height: 5px;
            overflow: hidden;
        }

        .bar {
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            width: 0%;
            height: 100%;
            border-radius: 40px;
            transition: width 1s ease-out;
        }

        /* Panels */
        .panel {
            background: rgba(15,23,42,0.82);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 1.6rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255,255,255,0.07);
            transition: all 0.3s;
        }

        .panel:hover {
            border-color: rgba(99,102,241,0.3);
            box-shadow: 0 16px 32px rgba(0,0,0,0.3);
        }

        .title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 1.1rem;
            display: inline-block;
            background: linear-gradient(135deg, #f1f5f9, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .tag {
            background: rgba(99,102,241,0.18);
            border: 1px solid rgba(99,102,241,0.38);
            color: #a5b4fc;
            padding: 5px 14px;
            border-radius: 40px;
            display: inline-block;
            margin: 4px 6px 4px 0;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: default;
        }

        .tag.warn {
            background: rgba(245,158,11,0.15);
            border-color: rgba(245,158,11,0.4);
            color: #fbbf24;
        }

        .tag:hover { transform: scale(1.05); filter: brightness(1.15); }

        ul { padding-left: 1.4rem; }
        li { margin: 0.55rem 0; color: rgba(255,255,255,0.7); transition: transform 0.2s; }
        li:hover { transform: translateX(5px); color: #a5b4fc; }

        .summary { font-size: 0.95rem; line-height: 1.7; color: rgba(255,255,255,0.7); }

        canvas { max-height: 260px; }

        @media (max-width: 1100px) {
            .sidebar { width: 220px; }
            .main { margin-left: 220px; padding: 1.5rem; }
            .kpi { gap: 1rem; }
        }
        @media (max-width: 780px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .kpi { grid-template-columns: 1fr 1fr; }
            .grid2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo"><div class="logo-icon">⚡</div>ResumeIQ-X</div>
    <a href="index.html" class="menu">🏠 Home</a>
    <a href="about.html" class="menu">ℹ️ About</a>
    <a href="help.html" class="menu">❓ Help</a>
    <a href="../backend_php/logout.php" class="menu">🚪 Sign Out</a>
</div>

<div class="main">
    <div class="header">
        <h1>🧠 Candidate Intelligence Report</h1>
    </div>
    <div id="report">
        <div class="loading-shimmer"></div>
    </div>
</div>
<script>

async function loadDashboard(){

try{

/* ==============================
   FETCH INTELLIGENCE DATA
============================== */

console.log("Fetching analysis for resume_id: <?php echo $resumeId;?>");

const response = await fetch(
getApiUrl("get_analysis_preview.php") + "?resume_id=<?php echo $resumeId;?>",
{credentials:"include"}
);

const json = await response.json();

if(!json.status){

document.getElementById("report").innerHTML = `
<div class="panel" style="text-align:center;padding:3rem;">
<h2>⚠️ Analysis Not Available</h2>
<p>${json.message ?? "Analysis missing"}</p>
</div>`;
return;

}

const data = json.data;


/* ==============================
   SAFE DATA NORMALIZER ENGINE
============================== */

const objFromArray = arr =>
Array.isArray(arr)
? Object.assign({}, ...arr)
: (arr ?? {});

const domain = data.domain_distribution ?? {};
const semantic = objFromArray(data.semantic_role_scores);
const maturity = objFromArray(data.skill_maturity);

const capability = {
Experience:data.experience_years ?? 0,
Skills:(data.skills ?? []).length,
Education:(data.education ?? []).length
};

const signals = {
Strengths:(data.strengths ?? []).length,
Weaknesses:(data.weaknesses ?? []).length,
Recommendations:(data.recommendations ?? []).length
};

const reasoning = objFromArray(
(data.reasoning_signals?.strengths ?? [])
.map(x=>({[x]:1}))
);

const careerVector = objFromArray(
(data.career_direction_vector?.suitable_titles ?? [])
.map(x=>({[x]:1}))
);

const similar={};

(data.similar_candidates ?? []).forEach((x,i)=>{
similar["Candidate "+(i+1)] = x.score ?? 50;
});

const latentSkills =
data.latent_skill_report?.inferred_skills ?? [];

const missing = data.missing_dependencies ?? [];
const roadmap = data.learning_recommendations ?? [];

const trajectory =
data.trajectory_prediction?.predicted_roles ?? [];


/* ==============================
   UI HELPER ENGINE
============================== */

function kpi(label,val){

val=Math.round(val ?? 0);

return `
<div class="card">
<div class="metric">${label}</div>
<div class="value">${val}%</div>
<div class="progress">
<div class="bar" style="width:${val}%"></div>
</div>
</div>`;
}


function tag(text,warn=false){

return `<span class="tag ${warn?"warn":""}">
${text}
</span>`;

}


/* ==============================
   HEADER PANEL (NEW)
============================== */

const headerPanel=`

<div class="panel">

<div class="title">👤 Candidate Identity</div>

<div style="line-height:2">

<b>${data.candidate_name ?? "Unknown Candidate"}</b><br>

📧 ${data.candidate_email ?? "-"}<br>

📱 ${data.candidate_phone ?? "-"}<br>

🏢 Sector: ${data.detected_sector ?? "-"}<br>

⏱ Experience: ${data.experience_years ?? 0} yrs<br>

🤖 Model: ${data.llm_provider_used ?? "AI Engine"}

</div>

</div>

`;


/* ==============================
   KPI PANEL
============================== */

const kpiPanel=`

<div class="kpi">

${kpi("Resume Strength",data.resume_strength_score)}

${kpi("Confidence Score",data.confidence_score)}

${kpi("Career Readiness",data.career_readiness_score)}

<div class="card">
<div class="metric">Talent Category</div>
<div class="value" style="font-size:1rem">
${data.talent_category ?? "General"}
</div>
</div>

</div>

`;


/* ==============================
   BUILD PROFESSIONAL DASHBOARD
============================== */

const html=`

${headerPanel}

${kpiPanel}


<div class="panel">
<div class="title">📊 Domain Strength Distribution</div>
<canvas id="domainChart"></canvas>
</div>


<div class="grid2">

<div class="panel">
<div class="title">🎭 Semantic Role Alignment</div>
<canvas id="semanticChart"></canvas>
</div>


<div class="panel">
<div class="title">🧠 Skill Maturity Radar</div>
<canvas id="maturityChart"></canvas>
</div>

</div>


<div class="panel">
<div class="title">⚙ Capability Vector Intelligence</div>
<canvas id="capabilityChart"></canvas>
</div>


<div class="panel">
<div class="title">📡 Candidate Signal Profile</div>
<canvas id="signalChart"></canvas>
</div>


<div class="panel">
<div class="title">💡 Reasoning Signals</div>
<canvas id="reasoningChart"></canvas>
</div>


<div class="panel">
<div class="title">🧭 Career Direction Vector</div>
<canvas id="careerChart"></canvas>
</div>


<div class="panel">
<div class="title">👥 Similar Candidate Cluster Match</div>
<canvas id="similarChart"></canvas>
</div>


<div class="panel">

<div class="title">📌 Missing Skills</div>

${missing.length
? missing.map(x=>tag(x,true)).join("")
: "None detected"}

</div>


<div class="panel">

<div class="title">✨ Latent Skills Intelligence</div>

${latentSkills.length
? latentSkills.map(x=>tag(x)).join("")
: "None detected"}

</div>


<div class="panel">

<div class="title">📚 Learning Roadmap</div>

<ul>
${roadmap.map(x=>`<li>${x}</li>`).join("")}
</ul>

</div>


<div class="panel">

<div class="title">🔮 Career Trajectory Prediction</div>

${trajectory.map(x=>`<div>${x}</div>`).join("")}

</div>


<div class="panel summary">

<div class="title">📝 AI Summary</div>

${data.summary ?? "No summary generated"}

</div>

`;


document.getElementById("report").innerHTML=html;


/* ==============================
   ADVANCED CHART ENGINE
============================== */

function renderChart(id,type,obj){

if(!obj || !Object.keys(obj).length)return;

new Chart(document.getElementById(id),{

type,

data:{

labels:Object.keys(obj),

datasets:[{

data:Object.values(obj),

backgroundColor:[
"#6366f1",
"#8b5cf6",
"#06b6d4",
"#10b981",
"#f59e0b",
"#ef4444"
],

borderWidth:1.5,

fill:type==="radar"

}]

},

options:{

responsive:true,

plugins:{

legend:{
position:"bottom",
labels:{color:"#cbd5e1"}
}

},

scales:type==="radar" ? {

r:{
grid:{color:"rgba(255,255,255,0.08)"},
ticks:{color:"#94a3b8"},
pointLabels:{color:"#cbd5e1"}
}

}:undefined

}

});

}


renderChart("domainChart","doughnut",domain);

renderChart("semanticChart","radar",semantic);

renderChart("maturityChart","radar",maturity);

renderChart("capabilityChart","bar",capability);

renderChart("signalChart","polarArea",signals);

renderChart("reasoningChart","polarArea",reasoning);

renderChart("careerChart","radar",careerVector);

renderChart("similarChart","bar",similar);


/* ==============================
   SMOOTH PANEL ENTRANCE FX
============================== */

document.querySelectorAll(".panel,.card")
.forEach((el,i)=>{

el.style.opacity="0";

el.style.transform="translateY(20px)";

setTimeout(()=>{

el.style.transition="all .45s ease";

el.style.opacity="1";

el.style.transform="translateY(0)";

},i*35);

});


}catch(err){

console.error(err);

document.getElementById("report").innerHTML=
`<div class="panel">❌ Intelligence render failed</div>`;

}

}

loadDashboard();

// Helper function to construct proper API URLs
function getApiUrl(script) {
  const protocol = window.location.protocol;
  const host = window.location.host;
  const pathname = window.location.pathname;
  
  // Get current directory
  let currentDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);
  
  // Go up one level to project root (since we're in /frontend/)
  let projectRoot = currentDir.substring(0, currentDir.lastIndexOf('/', currentDir.length - 2) + 1);
  
  // Construct full API URL
  return `${protocol}//${host}${projectRoot}backend_php/${script}`;
}

</script>
</body>
</html>
