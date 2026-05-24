<?php
session_start();
if(!isset($_SESSION['admin_id'])){header('Location: admin_login.html');exit;}
$adminName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | ResumeIQ-X</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#030712;color:#e2e8f0;min-height:100vh;overflow-x:hidden}
#bgCanvas{position:fixed;inset:0;z-index:0}

/* HEADER */
header{
  position:fixed;top:0;left:0;right:0;z-index:100;
  padding:.9rem 2rem;display:flex;justify-content:space-between;align-items:center;
  background:rgba(3,7,18,0.85);backdrop-filter:blur(24px);
  border-bottom:1px solid rgba(139,92,246,.2);
}
.logo{display:flex;align-items:center;gap:.7rem;font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;background:linear-gradient(135deg,#fff,#c4b5fd);-webkit-background-clip:text;background-clip:text;color:transparent}
.logo-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#a855f7);display:flex;align-items:center;justify-content:center;font-size:1rem;box-shadow:0 0 20px rgba(139,92,246,.6)}
.admin-tag{background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.4);border-radius:100px;padding:.25rem .8rem;font-size:.7rem;font-weight:700;letter-spacing:1px;color:#c4b5fd;text-transform:uppercase}
.header-right{display:flex;align-items:center;gap:.8rem}
.admin-pill{display:flex;align-items:center;gap:.5rem;background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.2);border-radius:100px;padding:.3rem .9rem;font-size:.85rem;color:#c4b5fd}
.logout-btn{color:rgba(255,255,255,.5);text-decoration:none;font-size:.85rem;padding:.3rem .8rem;border-radius:8px;transition:all .2s;border:1px solid rgba(255,255,255,.08)}
.logout-btn:hover{color:#fff;background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.3)}

/* MAIN */
main{position:relative;z-index:10;padding:5.5rem 1.5rem 3rem;max-width:1400px;margin:0 auto}

/* GREETING */
.greeting{margin-bottom:2rem;animation:fadeUp .6s ease-out}
.greeting h1{font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:700;background:linear-gradient(135deg,#fff,#c4b5fd);-webkit-background-clip:text;background-clip:text;color:transparent;margin-bottom:.3rem}
.greeting p{color:rgba(255,255,255,.4);font-size:.9rem}

/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;animation:fadeUp .6s ease-out .1s both}
.stat-card{background:rgba(15,23,42,.7);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:1.2rem;transition:all .3s}
.stat-card:hover{border-color:rgba(139,92,246,.4);transform:translateY(-3px)}
.stat-label{font-size:.72rem;color:rgba(255,255,255,.4);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.5px}
.stat-value{font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700;background:linear-gradient(135deg,#c4b5fd,#38bdf8);-webkit-background-clip:text;background-clip:text;color:transparent}

/* QUEUE CARD */
.queue-card{background:rgba(10,15,30,.85);backdrop-filter:blur(24px);border:1px solid rgba(139,92,246,.2);border-radius:20px;padding:1.8rem;animation:fadeUp .6s ease-out .2s both}
.queue-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid rgba(139,92,246,.15)}
.queue-title{font-size:1rem;font-weight:600;color:#f1f5f9;display:flex;align-items:center;gap:.6rem}
.live-badge{display:flex;align-items:center;gap:.4rem;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:100px;padding:.25rem .7rem;font-size:.72rem;color:#86efac;font-weight:600}
.live-dot{width:6px;height:6px;background:#22c55e;border-radius:50%;box-shadow:0 0 6px #22c55e;animation:blink 2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

/* TABLE */
.table-wrap{overflow-x:auto;border-radius:12px;scrollbar-width:thin;scrollbar-color:#8b5cf6 #1e293b}
.table-wrap::-webkit-scrollbar{height:5px}
.table-wrap::-webkit-scrollbar-track{background:#1e293b;border-radius:10px}
.table-wrap::-webkit-scrollbar-thumb{background:#8b5cf6;border-radius:10px}
table{width:100%;border-collapse:separate;border-spacing:0 8px;white-space:nowrap}
thead th{
  text-align:left;padding:10px 16px;
  font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;
  color:rgba(255,255,255,.35);border-bottom:1px solid rgba(255,255,255,.06);
}
tbody tr{
  background:rgba(15,25,45,.6);
  border-radius:12px;
  transition:all .25s;
  animation:rowIn .4s ease backwards;
}
@keyframes rowIn{from{opacity:0;transform:translateX(-10px)}to{opacity:1;transform:translateX(0)}}
tbody tr:hover{background:rgba(30,45,75,.8);box-shadow:0 8px 20px rgba(0,0,0,.4),0 0 0 1px rgba(139,92,246,.2);transform:translateY(-2px)}
tbody td{padding:14px 16px;vertical-align:middle;color:#e2e8f0;font-size:.88rem}
tbody td:first-child{border-radius:12px 0 0 12px}
tbody td:last-child{border-radius:0 12px 12px 0}

/* STATUS BADGES */
.badge{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .8rem;border-radius:100px;font-size:.75rem;font-weight:600}
.badge-pending{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.4);color:#fbbf24}
.badge-processing{background:rgba(14,165,233,.12);border:1px solid rgba(14,165,233,.4);color:#38bdf8}
.badge-completed{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.4);color:#34d399}
.badge-failed{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.4);color:#f87171}

/* PROGRESS */
.prog-wrap{display:flex;align-items:center;gap:.6rem;min-width:140px}
.prog-num{font-size:.8rem;font-weight:700;color:#c4b5fd;min-width:36px}
.prog-track{flex:1;height:6px;background:rgba(255,255,255,.06);border-radius:100px;overflow:hidden}
.prog-fill{height:100%;background:linear-gradient(90deg,#7c3aed,#a855f7,#06b6d4);background-size:200% 100%;border-radius:100px;transition:width .4s ease;animation:shimmer 2s infinite linear}
@keyframes shimmer{0%{background-position:100% 0}100%{background-position:-100% 0}}

/* ACTION BUTTONS */
.actions{display:flex;gap:.4rem;flex-wrap:nowrap}
.btn-act{
  border:none;padding:.45rem .8rem;border-radius:8px;
  font-size:.72rem;font-weight:600;cursor:pointer;
  display:inline-flex;align-items:center;gap:.3rem;
  transition:all .2s;font-family:'Inter',sans-serif;
  white-space:nowrap;
}
.btn-analyze{background:rgba(99,102,241,.2);color:#a5b4fc;border:1px solid rgba(99,102,241,.4)}
.btn-analyze:hover{background:#6366f1;color:#fff;box-shadow:0 0 15px rgba(99,102,241,.5)}
.btn-reanalyze{background:rgba(20,184,166,.2);color:#5eead4;border:1px solid rgba(20,184,166,.4)}
.btn-reanalyze:hover{background:#0f766e;color:#fff}
.btn-download{background:rgba(59,130,246,.2);color:#93c5fd;border:1px solid rgba(59,130,246,.4)}
.btn-download:hover{background:#1d4ed8;color:#fff}
.btn-preview{background:rgba(245,158,11,.2);color:#fcd34d;border:1px solid rgba(245,158,11,.4)}
.btn-preview:hover{background:#b45309;color:#fff}
.btn-delete{background:rgba(239,68,68,.2);color:#fca5a5;border:1px solid rgba(239,68,68,.4)}
.btn-delete:hover{background:#dc2626;color:#fff;box-shadow:0 0 12px rgba(239,68,68,.4)}
.btn-act:disabled{opacity:.4;cursor:not-allowed;transform:none !important;box-shadow:none !important}

/* LOADING */
.loading-row td{text-align:center;padding:3rem;color:rgba(255,255,255,.4)}
.spinner{display:inline-block;width:28px;height:28px;border:3px solid rgba(139,92,246,.2);border-top-color:#a855f7;border-radius:50%;animation:spin .8s infinite linear;margin-right:.8rem;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}

/* EMPTY */
.empty-row td{text-align:center;padding:3rem;color:rgba(255,255,255,.3);font-size:.9rem}

@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

/* ── AI CHAT WIDGET ── */
.ai-chat-btn{
  position:fixed;bottom:2rem;right:2rem;z-index:9999;
  width:60px;height:60px;border-radius:50%;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  border:none;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:1.8rem;
  box-shadow:0 8px 30px rgba(99,102,241,.5);
  transition:all .3s;
}
.ai-chat-btn:hover{
  transform:scale(1.1);
  box-shadow:0 12px 40px rgba(99,102,241,.7);
}
.ai-chat-btn.active{
  background:linear-gradient(135deg,#ef4444,#dc2626);
}

.ai-chat-window{
  position:fixed;bottom:6rem;right:2rem;z-index:9998;
  width:380px;max-width:calc(100vw - 4rem);
  height:550px;max-height:calc(100vh - 10rem);
  background:#0f172a;
  border:1px solid rgba(99,102,241,.3);
  border-radius:20px;
  box-shadow:0 20px 60px rgba(0,0,0,.6);
  display:none;flex-direction:column;
  overflow:hidden;
  animation:slideUp .3s ease-out;
}
.ai-chat-window.visible{display:flex}

@keyframes slideUp{
  from{opacity:0;transform:translateY(20px)}
  to{opacity:1;transform:translateY(0)}
}

.chat-header{
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  padding:1rem 1.2rem;
  display:flex;align-items:center;justify-content:space-between;
}
.chat-header-left{display:flex;align-items:center;gap:.8rem}
.chat-avatar{
  width:36px;height:36px;border-radius:50%;
  background:rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;
  font-size:1.2rem;
}
.chat-header-info h3{
  font-size:.95rem;font-weight:600;margin:0;color:#fff;
}
.chat-header-info p{
  font-size:.7rem;color:rgba(255,255,255,.7);margin:.1rem 0 0;
}
.chat-close{
  background:none;border:none;color:#fff;
  font-size:1.5rem;cursor:pointer;
  width:32px;height:32px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  transition:background .2s;
}
.chat-close:hover{background:rgba(255,255,255,.15)}

.chat-messages{
  flex:1;overflow-y:auto;padding:1rem;
  display:flex;flex-direction:column;gap:.8rem;
  background:#030712;
}
.chat-messages::-webkit-scrollbar{width:6px}
.chat-messages::-webkit-scrollbar-track{background:transparent}
.chat-messages::-webkit-scrollbar-thumb{background:rgba(99,102,241,.3);border-radius:3px}

.chat-message{
  display:flex;gap:.6rem;
  animation:fadeIn .3s ease-out;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

.chat-message.user{flex-direction:row-reverse}
.chat-message-avatar{
  width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  display:flex;align-items:center;justify-content:center;
  font-size:1rem;flex-shrink:0;
}
.chat-message.user .chat-message-avatar{
  background:linear-gradient(135deg,#06b6d4,#38bdf8);
}
.chat-message-content{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.08);
  border-radius:12px;padding:.7rem 1rem;
  max-width:75%;
  font-size:.85rem;line-height:1.5;
  color:#e2e8f0;
}
.chat-message.user .chat-message-content{
  background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(139,92,246,.2));
  border-color:rgba(99,102,241,.3);
}

.chat-typing{
  display:flex;gap:.3rem;padding:.5rem 0;
}
.chat-typing span{
  width:6px;height:6px;border-radius:50%;
  background:#6366f1;
  animation:typing 1.4s infinite;
}
.chat-typing span:nth-child(2){animation-delay:.2s}
.chat-typing span:nth-child(3){animation-delay:.4s}
@keyframes typing{0%,60%,100%{opacity:.3;transform:translateY(0)}30%{opacity:1;transform:translateY(-8px)}}

.chat-input-area{
  padding:1rem;
  background:#0f172a;
  border-top:1px solid rgba(255,255,255,.08);
  display:flex;gap:.6rem;
}
.chat-input{
  flex:1;
  background:#030712;
  border:1px solid rgba(255,255,255,.1);
  border-radius:12px;
  padding:.7rem 1rem;
  color:#e2e8f0;
  font-size:.85rem;
  font-family:inherit;
  outline:none;
  transition:border-color .2s;
}
.chat-input:focus{border-color:#6366f1}
.chat-input::placeholder{color:rgba(255,255,255,.3)}
.chat-send-btn{
  width:40px;height:40px;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  border:none;border-radius:10px;
  color:#fff;font-size:1.1rem;
  cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  transition:all .2s;
}
.chat-send-btn:hover{transform:scale(1.05)}
.chat-send-btn:disabled{
  opacity:.5;cursor:not-allowed;
  transform:scale(1);
}

.chat-provider-badge{
  font-size:.65rem;
  color:rgba(255,255,255,.4);
  text-align:center;
  padding:.3rem;
  background:rgba(0,0,0,.2);
  border-top:1px solid rgba(255,255,255,.05);
}

@media(max-width:768px){header{padding:.8rem 1rem}main{padding:5rem 1rem 2rem}.greeting h1{font-size:1.5rem}
  .ai-chat-window{
    width:calc(100vw - 2rem);
    height:calc(100vh - 8rem);
    right:1rem;bottom:5rem;
  }
  .ai-chat-btn{right:1rem;bottom:1rem}
}
</style>
</head>
<body>
<canvas id="bgCanvas"></canvas>

<header>
  <div style="display:flex;align-items:center;gap:1rem">
    <div class="logo"><div class="logo-icon">👑</div>ResumeIQ-X</div>
    <span class="admin-tag">Enterprise</span>
  </div>
  <div class="header-right">
    <div class="admin-pill">🛡️ <?php echo htmlspecialchars($adminName); ?></div>
    <a href="../backend_php/admin_logout.php" class="logout-btn">Sign out</a>
  </div>
</header>

<main>
  <div class="greeting">
    <h1>Talent Intelligence Queue</h1>
    <p>Real-time AI analysis pipeline — manage and trigger resume evaluations</p>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="stat-label">Total Resumes</div><div class="stat-value" id="statTotal">—</div></div>
    <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value" id="statPending">—</div></div>
    <div class="stat-card"><div class="stat-label">Processing</div><div class="stat-value" id="statProcessing">—</div></div>
    <div class="stat-card"><div class="stat-label">Completed</div><div class="stat-value" id="statCompleted">—</div></div>
  </div>

  <div class="queue-card">
    <div class="queue-header">
      <div class="queue-title"><i class="fas fa-file-signature" style="color:#a855f7"></i> Resume Queue</div>
      <div class="live-badge"><div class="live-dot"></div>LIVE PIPELINE</div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th><i class="fas fa-user-tie"></i> Candidate</th>
            <th><i class="fas fa-envelope"></i> Email</th>
            <th><i class="fas fa-file-pdf"></i> Resume</th>
            <th><i class="fas fa-circle-nodes"></i> Status</th>
            <th><i class="fas fa-chart-bar"></i> Progress</th>
            <th><i class="fas fa-microchip"></i> Actions</th>
          </tr>
        </thead>
        <tbody id="resumeTable">
          <tr class="loading-row"><td colspan="6"><div class="spinner"></div><span>Loading queue...</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
// BG
const canvas=document.getElementById('bgCanvas');
const ctx=canvas.getContext('2d');
let W,H;
function resize(){W=canvas.width=window.innerWidth;H=canvas.height=window.innerHeight}
resize();window.addEventListener('resize',resize);
function draw(){
  ctx.clearRect(0,0,W,H);ctx.fillStyle='#030712';ctx.fillRect(0,0,W,H);
  [[.2,.2,500,'139,92,246',.1],[.8,.8,400,'99,102,241',.07],[.5,.1,300,'168,85,247',.08]].forEach(([x,y,r,c,a])=>{
    const g=ctx.createRadialGradient(x*W,y*H,0,x*W,y*H,r);
    g.addColorStop(0,`rgba(${c},${a})`);g.addColorStop(1,'transparent');
    ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
  });
  requestAnimationFrame(draw);
}
draw();

// WEBSOCKET (unchanged)
try{
  const ws=new WebSocket("ws://localhost:5000");
  ws.onmessage=(event)=>{
    const data=JSON.parse(event.data);
    if(!data.resume_id)return;
    const row=document.querySelector(`tr[data-id="${data.resume_id}"]`);
    if(!row)return;
    const pt=row.querySelector(".prog-num");if(pt)pt.textContent=data.progress+"%";
    const pf=row.querySelector(".prog-fill");if(pf)pf.style.width=data.progress+"%";
    const sc=row.querySelector(".statusCell");
    if(sc){sc.innerHTML=getBadge(data.status);sc.className="statusCell"}
  };
}catch(e){}

function getBadge(status){
  const s=status.toLowerCase();
  if(s.includes("processing"))return`<span class="badge badge-processing">⚙️ ${status}</span>`;
  if(s.includes("completed"))return`<span class="badge badge-completed">✓ ${status}</span>`;
  if(s.includes("failed"))return`<span class="badge badge-failed">✕ ${status}</span>`;
  return`<span class="badge badge-pending">⏳ ${status}</span>`;
}

// Track which resume IDs are currently being animated (don't let DB poll overwrite)
const analyzingIds = new Set();

// Track which resume IDs are locally marked as processing (for stats calculation)
const locallyProcessingIds = new Set();

// Track previous stats for smooth animations
let prevStats = {
  total: 0,
  pending: 0,
  processing: 0,
  completed: 0
};

// Animate number changes smoothly
function animateStatChange(elementId, targetValue) {
  const element = document.getElementById(elementId);
  const currentValue = parseInt(element.textContent) || 0;
  
  if (currentValue === targetValue) return;
  
  // Add pulse animation to parent card
  const card = element.closest('.stat-card');
  if (card) {
    card.style.transform = 'scale(1.05)';
    card.style.borderColor = 'rgba(139,92,246,.6)';
    card.style.boxShadow = '0 0 20px rgba(139,92,246,.4)';
    setTimeout(() => {
      card.style.transform = '';
      card.style.borderColor = '';
      card.style.boxShadow = '';
    }, 600);
  }
  
  const duration = 800; // ms
  const steps = 30;
  const stepValue = (targetValue - currentValue) / steps;
  const stepDuration = duration / steps;
  
  let currentStep = 0;
  
  const interval = setInterval(() => {
    currentStep++;
    if (currentStep >= steps) {
      element.textContent = targetValue;
      clearInterval(interval);
      return;
    }
    
    const newValue = Math.round(currentValue + (stepValue * currentStep));
    element.textContent = newValue;
  }, stepDuration);
}

async function loadQueue(){
  try {
    const res=await fetch("../backend_php/get_admin_dashboard_resumes.php",{credentials:"include"});
    const result=await res.json();
    const tbody=document.getElementById("resumeTable");
    const data=result.data||[];

    // Calculate current stats from database
    let dbStats = {
      total: data.length,
      pending: data.filter(r=>r.status==='pending').length,
      processing: data.filter(r=>{
        const s = r.status.toLowerCase();
        return s.includes('processing') || s === 'processing';
      }).length,
      completed: data.filter(r=>r.status.includes('completed')).length
    };

    // Add locally processing resumes that might not be in DB yet
    const localProcessingCount = locallyProcessingIds.size;
    
    // Adjust stats to include local processing state
    const currentStats = {
      total: dbStats.total,
      pending: Math.max(0, dbStats.pending),
      processing: Math.max(dbStats.processing, localProcessingCount), // Use whichever is higher
      completed: dbStats.completed
    };

    // Animate stats if they changed
    if (currentStats.total !== prevStats.total) {
      animateStatChange('statTotal', currentStats.total);
    }
    if (currentStats.pending !== prevStats.pending) {
      animateStatChange('statPending', currentStats.pending);
    }
    if (currentStats.processing !== prevStats.processing) {
      animateStatChange('statProcessing', currentStats.processing);
    }
    if (currentStats.completed !== prevStats.completed) {
      animateStatChange('statCompleted', currentStats.completed);
    }

    // Update previous stats
    prevStats = currentStats;

    if(!data.length){
      tbody.innerHTML='<tr class="empty-row"><td colspan="6">📭 No resumes in queue yet</td></tr>';
      return;
    }

    data.forEach((r,idx)=>{
      const existingRow = document.querySelector(`tr[data-id="${r.id}"]`);

      if(existingRow) {
        // NEVER overwrite progress if animation is running for this resume
        if(analyzingIds.has(r.id)) return;

        // Safe to update from DB
        const pt = existingRow.querySelector('.prog-num');
        const pf = existingRow.querySelector('.prog-fill');
        const sc = existingRow.querySelector('.statusCell');
        if(pt) pt.textContent = r.progress + '%';
        if(pf) pf.style.width = r.progress + '%';
        if(sc) sc.innerHTML = getBadge(r.status);
        return;
      }

      // Build new row
      const analyzeBtn = r.status==="completed"
        ?`<button class="btn-act btn-reanalyze" onclick="analyze(${r.id},this)"><i class="fas fa-rotate-right"></i> Re-Analyze</button>`
        :`<button class="btn-act btn-analyze" onclick="analyze(${r.id},this)"><i class="fas fa-play"></i> Analyze</button>`;
      const tr=document.createElement('tr');
      tr.setAttribute('data-id',r.id);
      tr.style.animationDelay=idx*.03+'s';
      tr.innerHTML=`
        <td><i class="fas fa-user-circle" style="color:#a855f7;margin-right:.5rem"></i>${r.name}</td>
        <td style="color:rgba(255,255,255,.6)">${r.email}</td>
        <td><i class="far fa-file-pdf" style="color:#f87171;margin-right:.4rem"></i>${r.file_name}</td>
        <td class="statusCell">${getBadge(r.status)}</td>
        <td>
          <div class="prog-wrap">
            <span class="prog-num">${r.progress}%</span>
            <div class="prog-track"><div class="prog-fill" style="width:${r.progress}%"></div></div>
          </div>
        </td>
        <td>
          <div class="actions">
            ${analyzeBtn}
            <button class="btn-act btn-download" onclick="downloadResume(${r.id})"><i class="fas fa-download"></i></button>
            <button class="btn-act btn-preview" onclick="preview(${r.id})"><i class="fas fa-eye"></i></button>
            <button class="btn-act btn-delete" onclick="deleteResume(${r.id})"><i class="fas fa-trash"></i></button>
          </div>
        </td>`;
      tbody.appendChild(tr);
    });

    // Remove stale rows
    tbody.querySelectorAll('tr[data-id]').forEach(row => {
      const id = parseInt(row.getAttribute('data-id'));
      if(!data.find(r=>r.id===id)) row.remove();
    });

  } catch(e) { console.error('loadQueue error:', e); }
}

// Smooth progress animation — runs entirely in JS, never touches DB
function animateProgress(resumeId) {
  const steps = [10, 20, 30, 40, 50, 60, 70, 80, 90, 95];
  let stepIdx = 0;

  const interval = setInterval(() => {
    if(stepIdx >= steps.length) { clearInterval(interval); return; }
    const pct = steps[stepIdx++];
    const pt = document.querySelector(`tr[data-id="${resumeId}"] .prog-num`);
    const pf = document.querySelector(`tr[data-id="${resumeId}"] .prog-fill`);
    const sc = document.querySelector(`tr[data-id="${resumeId}"] .statusCell`);
    if(pt) pt.textContent = pct + '%';
    if(pf) pf.style.width = pct + '%';
    if(sc) sc.innerHTML = getBadge('processing (' + pct + '%)');
  }, 1500);

  return interval;
}

async function analyze(id, btn){
  btn.disabled = true;
  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Analyzing...';

  // Mark as animating — prevents loadQueue from overwriting progress
  analyzingIds.add(id);
  
  // Mark as locally processing — ensures stats count includes this resume
  locallyProcessingIds.add(id);

  // Set initial state AND update stats immediately
  const sc = document.querySelector(`tr[data-id="${id}"] .statusCell`);
  const pt = document.querySelector(`tr[data-id="${id}"] .prog-num`);
  const pf = document.querySelector(`tr[data-id="${id}"] .prog-fill`);
  if(sc) sc.innerHTML = getBadge('processing (10%)');
  if(pt) pt.textContent = '10%';
  if(pf) pf.style.width = '10%';

  // Immediately increment processing count in UI
  const processingEl = document.getElementById('statProcessing');
  const currentProcessing = parseInt(processingEl.textContent) || 0;
  animateStatChange('statProcessing', currentProcessing + 1);
  
  // Decrement pending count if it was pending
  const pendingEl = document.getElementById('statPending');
  const currentPending = parseInt(pendingEl.textContent) || 0;
  if(currentPending > 0) {
    animateStatChange('statPending', currentPending - 1);
  }

  // Update prevStats to reflect the manual change
  prevStats.processing = currentProcessing + 1;
  prevStats.pending = Math.max(0, currentPending - 1);

  // Start smooth animation
  const progressInterval = animateProgress(id);

  try {
    const res = await fetch("../backend_php/start_analysis.php", {
      method: "POST",
      credentials: "include",
      headers: {"Content-Type": "application/x-www-form-urlencoded"},
      body: "resume_id=" + id
    });

    clearInterval(progressInterval);
    analyzingIds.delete(id); // Allow DB updates again
    locallyProcessingIds.delete(id); // Remove from local processing tracking

    const data = await res.json();

    if(data.status) {
      if(pt) pt.textContent = '100%';
      if(pf) pf.style.width = '100%';
      if(sc) sc.innerHTML = getBadge('completed');
      
      // Update stats: decrement processing, increment completed
      const currentProcessing = parseInt(processingEl.textContent) || 0;
      const completedEl = document.getElementById('statCompleted');
      const currentCompleted = parseInt(completedEl.textContent) || 0;
      
      animateStatChange('statProcessing', Math.max(0, currentProcessing - 1));
      animateStatChange('statCompleted', currentCompleted + 1);
      
      // Update prevStats
      prevStats.processing = Math.max(0, currentProcessing - 1);
      prevStats.completed = currentCompleted + 1;
      
      btn.innerHTML = '<i class="fas fa-check"></i> Done';
      btn.style.background = '#10b981';
      setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-rotate-right"></i> Re-Analyze';
        btn.className = 'btn-act btn-reanalyze';
        btn.style.background = '';
        btn.onclick = function(){ analyze(id, btn); };
        loadQueue();
      }, 2000);
    } else {
      if(pt) pt.textContent = '0%';
      if(pf) pf.style.width = '0%';
      if(sc) sc.innerHTML = getBadge('failed');
      
      // Update stats: decrement processing, increment pending (failed goes back to pending)
      const currentProcessing = parseInt(processingEl.textContent) || 0;
      const currentPending = parseInt(pendingEl.textContent) || 0;
      
      animateStatChange('statProcessing', Math.max(0, currentProcessing - 1));
      animateStatChange('statPending', currentPending + 1);
      
      // Update prevStats
      prevStats.processing = Math.max(0, currentProcessing - 1);
      prevStats.pending = currentPending + 1;
      
      btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Failed';
      btn.style.background = '#ef4444';
      alert('Analysis failed: ' + (data.message || 'Unknown error'));
      setTimeout(() => { btn.disabled=false; btn.innerHTML=orig; btn.style.background=''; loadQueue(); }, 3000);
    }
  } catch(err) {
    clearInterval(progressInterval);
    analyzingIds.delete(id);
    locallyProcessingIds.delete(id); // Remove from local processing tracking
    if(sc) sc.innerHTML = getBadge('failed');
    
    // Update stats: decrement processing on error
    const currentProcessing = parseInt(processingEl.textContent) || 0;
    animateStatChange('statProcessing', Math.max(0, currentProcessing - 1));
    
    // Update prevStats
    prevStats.processing = Math.max(0, currentProcessing - 1);
    
    btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
    btn.style.background = '#ef4444';
    alert('Network error: ' + err.message);
    setTimeout(() => { btn.disabled=false; btn.innerHTML=orig; btn.style.background=''; }, 3000);
  }
}

async function deleteResume(id){
  if(!confirm("⚠️ Permanently remove this resume?"))return;
  await fetch("../backend_php/delete_resume.php",{method:"POST",credentials:"include",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"resume_id="+id});
  const row=document.querySelector(`tr[data-id="${id}"]`);
  if(row){row.style.transition="all .25s";row.style.opacity="0";row.style.transform="scale(.98)";setTimeout(()=>row?.remove(),200)}
}

function downloadResume(id){window.open("../backend_php/download_resume.php?resume_id="+id)}
function preview(id){window.location="analysis_result_viewer.php?resume_id="+id}

setInterval(loadQueue,5000);
loadQueue();
</script>

<!-- AI CHAT ASSISTANT -->
<button class="ai-chat-btn" id="aiChatBtn" title="Chat with AI Assistant">🤖</button>

<div class="ai-chat-window" id="aiChatWindow">
  <div class="chat-header">
    <div class="chat-header-left">
      <div class="chat-avatar">🤖</div>
      <div class="chat-header-info">
        <h3>ResumeIQ-X Assistant</h3>
        <p id="chatStatus">Online • Ready to help</p>
      </div>
    </div>
    <button class="chat-close" id="chatCloseBtn">×</button>
  </div>
  
  <div class="chat-messages" id="chatMessages">
    <div class="chat-message">
      <div class="chat-message-avatar">🤖</div>
      <div class="chat-message-content">
        Hi! I'm your ResumeIQ-X AI assistant. 👋
        <br><br>
        <strong>ResumeIQ-X</strong> was created by <strong>MAYUR GOPAL KOVE</strong>, a visionary developer who built this AI-powered platform to revolutionize resume analysis.
        <br><br>
        I can help you with:
        <br>• Understanding how ResumeIQ-X works
        <br>• Navigating the admin dashboard
        <br>• Resume analysis insights
        <br>• Answering your questions
        <br><br>How can I help you today?
      </div>
    </div>
  </div>
  
  <div class="chat-input-area">
    <input 
      type="text" 
      class="chat-input" 
      id="chatInput" 
      placeholder="Ask me anything..."
      autocomplete="off"
    >
    <button class="chat-send-btn" id="chatSendBtn">➤</button>
  </div>
  
  <div class="chat-provider-badge" id="chatProviderBadge">
    Powered by AI • Created by MAYUR GOPAL KOVE
  </div>
</div>

<script>
// ── AI CHAT ASSISTANT ──
const chatBtn = document.getElementById('aiChatBtn');
const chatWindow = document.getElementById('aiChatWindow');
const chatCloseBtn = document.getElementById('chatCloseBtn');
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const chatSendBtn = document.getElementById('chatSendBtn');
const chatStatus = document.getElementById('chatStatus');
const chatProviderBadge = document.getElementById('chatProviderBadge');

let conversationHistory = [];
let isProcessing = false;

// Toggle chat window
chatBtn.addEventListener('click', () => {
  const isVisible = chatWindow.classList.contains('visible');
  if (isVisible) {
    chatWindow.classList.remove('visible');
    chatBtn.classList.remove('active');
  } else {
    chatWindow.classList.add('visible');
    chatBtn.classList.add('active');
    chatInput.focus();
  }
});

chatCloseBtn.addEventListener('click', () => {
  chatWindow.classList.remove('visible');
  chatBtn.classList.remove('active');
});

// Send message
async function sendChatMessage() {
  const message = chatInput.value.trim();
  if (!message || isProcessing) return;

  // Add user message to UI
  addMessageToUI('user', message);
  chatInput.value = '';
  
  // Add to history
  conversationHistory.push({ role: 'user', content: message });

  // Show typing indicator
  isProcessing = true;
  chatSendBtn.disabled = true;
  const typingIndicator = addTypingIndicator();

  try {
    // Call AI backend - use absolute path for Railway deployment
    const apiUrl = `${window.location.origin}/backend_php/ai_chat.php`;
    
    console.log('Calling API:', apiUrl); // Debug log
    
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message: message,
        history: conversationHistory
      })
    });

    // Check if response is OK
    if (!response.ok) {
      console.error('API Response Status:', response.status);
      console.error('API Response URL:', response.url);
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();

    // Remove typing indicator
    typingIndicator.remove();

    if (data.success) {
      // Add AI response to UI
      addMessageToUI('assistant', data.message);
      
      // Add to history
      conversationHistory.push({ role: 'assistant', content: data.message });

      // Update provider badge
      if (data.provider) {
        const providerNames = {
          'openai': 'OpenAI GPT-4',
          'groq': 'Groq Llama',
          'gemini': 'Google Gemini',
          'anthropic': 'Claude',
          'deepseek': 'DeepSeek',
          'ollama': 'Local Ollama'
        };
        chatProviderBadge.textContent = `Powered by ${providerNames[data.provider] || 'AI'} • Created by MAYUR GOPAL KOVE`;
      }
    } else {
      addMessageToUI('assistant', data.message || 'Sorry, I encountered an error. Please try again.');
    }
  } catch (error) {
    typingIndicator.remove();
    console.error('Chat API Error:', error);
    addMessageToUI('assistant', 'Sorry, I\'m having trouble connecting. Please check the console for details.');
    console.error('Error details:', error);
  } finally {
    isProcessing = false;
    chatSendBtn.disabled = false;
    chatInput.focus();
  }
}

function addMessageToUI(role, content) {
  const messageDiv = document.createElement('div');
  messageDiv.className = `chat-message ${role}`;
  
  const avatar = document.createElement('div');
  avatar.className = 'chat-message-avatar';
  avatar.textContent = role === 'user' ? '👤' : '🤖';
  
  const contentDiv = document.createElement('div');
  contentDiv.className = 'chat-message-content';
  contentDiv.innerHTML = content.replace(/\n/g, '<br>');
  
  messageDiv.appendChild(avatar);
  messageDiv.appendChild(contentDiv);
  chatMessages.appendChild(messageDiv);
  
  // Scroll to bottom
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addTypingIndicator() {
  const messageDiv = document.createElement('div');
  messageDiv.className = 'chat-message';
  messageDiv.id = 'typingIndicator';
  
  const avatar = document.createElement('div');
  avatar.className = 'chat-message-avatar';
  avatar.textContent = '🤖';
  
  const contentDiv = document.createElement('div');
  contentDiv.className = 'chat-message-content';
  contentDiv.innerHTML = '<div class="chat-typing"><span></span><span></span><span></span></div>';
  
  messageDiv.appendChild(avatar);
  messageDiv.appendChild(contentDiv);
  chatMessages.appendChild(messageDiv);
  
  chatMessages.scrollTop = chatMessages.scrollHeight;
  
  return messageDiv;
}

// Event listeners
chatSendBtn.addEventListener('click', sendChatMessage);
chatInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendChatMessage();
  }
});
</script>

</body>
</html>
