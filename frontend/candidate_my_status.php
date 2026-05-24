<?php
require_once "../backend_php/session_guard.php";
requireAnyRole(["user","candidate"]);
$userName = $_SESSION['user_name'] ?? 'Candidate';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analysis Status | ResumeIQ-X</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#030712;color:#e2e8f0;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.5rem;overflow:hidden}
#bgCanvas{position:fixed;inset:0;z-index:0}
.wrap{position:relative;z-index:10;width:100%;max-width:500px}
.card{
  background:rgba(15,23,42,0.85);backdrop-filter:blur(32px);
  border:1px solid rgba(99,102,241,.2);border-radius:24px;
  padding:2.5rem 2rem;text-align:center;
  box-shadow:0 40px 80px rgba(0,0,0,.5);
  animation:slideUp .6s cubic-bezier(.16,1,.3,1);
}
@keyframes slideUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.logo{display:flex;align-items:center;justify-content:center;gap:.6rem;margin-bottom:2rem;font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;background:linear-gradient(135deg,#fff,#a5b4fc);-webkit-background-clip:text;background-clip:text;color:transparent}
.logo-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:1rem;box-shadow:0 0 20px rgba(99,102,241,.5)}
.user-name{
  display:inline-block;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);
  border-radius:100px;padding:.3rem 1rem;font-size:.85rem;color:#a5b4fc;margin-bottom:2rem;
}

/* STATUS DISPLAY */
.status-icon{font-size:3.5rem;display:block;margin-bottom:1rem;animation:float 3s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.status-title{font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:.5rem}
.status-sub{color:rgba(255,255,255,.4);font-size:.9rem;margin-bottom:1.5rem;line-height:1.6}

/* PROGRESS RING */
.ring-wrap{position:relative;width:100px;height:100px;margin:0 auto 1.5rem}
.ring-svg{transform:rotate(-90deg)}
.ring-bg{fill:none;stroke:rgba(255,255,255,.06);stroke-width:8}
.ring-fill{fill:none;stroke:url(#ringGrad);stroke-width:8;stroke-linecap:round;transition:stroke-dashoffset .5s ease}
.ring-text{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk',sans-serif;font-size:1.2rem;font-weight:700;background:linear-gradient(135deg,#a5b4fc,#38bdf8);-webkit-background-clip:text;background-clip:text;color:transparent}

/* STATUS BADGE */
.status-badge{
  display:inline-flex;align-items:center;gap:.5rem;
  padding:.4rem 1rem;border-radius:100px;font-size:.85rem;font-weight:600;margin-bottom:1.5rem;
}
.badge-pending{background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.4);color:#fbbf24}
.badge-processing{background:rgba(14,165,233,.15);border:1px solid rgba(14,165,233,.4);color:#38bdf8}
.badge-completed{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.4);color:#34d399}
.badge-error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.4);color:#f87171}

/* SPINNER */
.spinner{display:inline-block;width:24px;height:24px;border:3px solid rgba(99,102,241,.2);border-top-color:#6366f1;border-radius:50%;animation:spin .8s infinite linear;margin-right:.5rem;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}

.btn{
  display:inline-flex;align-items:center;gap:.5rem;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  color:#fff;border:none;border-radius:10px;
  padding:.75rem 1.5rem;font-size:.9rem;font-weight:600;cursor:pointer;
  font-family:'Inter',sans-serif;box-shadow:0 0 20px rgba(99,102,241,.3);transition:all .25s;
  text-decoration:none;margin:.3rem;
}
.btn:hover{transform:translateY(-2px);box-shadow:0 0 35px rgba(99,102,241,.5)}
.btn-outline{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);box-shadow:none}
.btn-outline:hover{background:rgba(255,255,255,.1)}
</style>
</head>
<body>
<canvas id="bgCanvas"></canvas>
<div class="wrap">
  <div class="card">
    <div class="logo"><div class="logo-icon">⚡</div>ResumeIQ-X</div>
    <div class="user-name">👤 <?php echo htmlspecialchars($userName); ?></div>

    <span class="status-icon" id="statusIcon">⏳</span>
    <div class="status-title" id="statusTitle">Checking Status...</div>
    <p class="status-sub" id="statusSub">Connecting to AI pipeline...</p>

    <div id="badgeWrap"></div>
    <div id="actionWrap"></div>
  </div>
</div>

<script>
const canvas=document.getElementById('bgCanvas');
const ctx=canvas.getContext('2d');
let W,H;
function resize(){W=canvas.width=window.innerWidth;H=canvas.height=window.innerHeight}
resize();window.addEventListener('resize',resize);
function draw(){
  ctx.clearRect(0,0,W,H);ctx.fillStyle='#030712';ctx.fillRect(0,0,W,H);
  [[.2,.3,400,'99,102,241',.1],[.8,.7,350,'6,182,212',.07]].forEach(([x,y,r,c,a])=>{
    const g=ctx.createRadialGradient(x*W,y*H,0,x*W,y*H,r);
    g.addColorStop(0,`rgba(${c},${a})`);g.addColorStop(1,'transparent');
    ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
  });
  requestAnimationFrame(draw);
}
draw();

function setStatus(icon,title,sub,badgeClass,badgeText,actions){
  document.getElementById('statusIcon').textContent=icon;
  document.getElementById('statusTitle').textContent=title;
  document.getElementById('statusSub').textContent=sub;
  document.getElementById('badgeWrap').innerHTML=badgeText?`<div class="status-badge ${badgeClass}">${badgeText}</div>`:'';
  document.getElementById('actionWrap').innerHTML=actions||'';
}

async function checkStatus(){
  try{
    const res=await fetch(apiUrl('check_status.php'),{method:"GET",credentials:"include"});
    if(!res.ok)throw new Error("Server unavailable");
    const result=await res.json();
    if(!result.status){setStatus('❌','Error',result.message,'badge-error','✕ Error','<button class="btn btn-outline" onclick="checkStatus()">↻ Retry</button>');return}
    if(result.analysis_status==="completed"){
      localStorage.setItem("resume_analysis",JSON.stringify(result.analysis));
      const rid=result.resume_id||'';
      console.log('Resume ID from API:',rid); // Debug log
      if(!rid){
        setStatus('⚠️','Analysis Complete (ID Missing)','Report available but resume ID is missing. Please contact support.','badge-error','⚠️ ID Error',`<button class="btn btn-outline" onclick="checkStatus()">↻ Retry</button>`);
        return;
      }
      setStatus('✅','Analysis Complete!','Your AI intelligence report is ready to view.','badge-completed','✓ Completed',`<a href="https://resumeiq-x-production.up.railway.app/analysis_result_viewer.php?resume_id=${rid}" class="btn">📊 View Report</a><a href="upload_resume.php" class="btn btn-outline">📤 Upload New</a>`);
      return;
    }
    if(result.analysis_status==="processing"){
      setStatus('⚙️','AI Analysis Running','Our 7-layer intelligence pipeline is processing your resume...','badge-processing','⚙️ Processing','<button class="btn btn-outline" onclick="checkStatus()">↻ Refresh</button>');
      return;
    }
    if(result.analysis_status==="pending"){
      setStatus('⏳','Awaiting Admin Review','Your resume has been uploaded and is in the queue for AI analysis.','badge-pending','⏳ Pending','<button class="btn btn-outline" onclick="checkStatus()">↻ Refresh</button>');
      return;
    }
    if(result.analysis_status==="no_resume"){
      setStatus('📭','No Resume Found','Upload your resume to start the AI analysis process.','badge-error','📭 No Resume',`<a href="upload_resume.php" class="btn">🚀 Upload Resume</a>`);
      return;
    }
    setStatus('❓','Unknown Status','Please refresh to check your status.','badge-error','? Unknown','<button class="btn btn-outline" onclick="checkStatus()">↻ Refresh</button>');
  }catch(e){
    setStatus('🔌','Connection Error','Unable to reach the server. Please check your connection.','badge-error','✕ Error','<button class="btn btn-outline" onclick="checkStatus()">↻ Retry</button>');
  }
}

setInterval(checkStatus,10000);
checkStatus();

function apiUrl(script){
  const parts=window.location.pathname.split('/');
  parts.pop();parts.pop();
  return window.location.origin+parts.join('/')+'/backend_php/'+script;
}
</script>

<!-- AI Chat Assistant Widget - Updated: 2026-05-04 -->
<?php include 'components/ai_chat_widget.php'; ?>

</body>
</html>
