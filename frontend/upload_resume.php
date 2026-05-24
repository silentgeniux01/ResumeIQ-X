<?php
session_start();
if(!isset($_SESSION['user_id'])){header('Location: user_login.html');exit;}
$userName = $_SESSION['user_name'] ?? 'Candidate';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Resume | ResumeIQ-X</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#030712;min-height:100vh;color:#e2e8f0;overflow-x:hidden}
#bgCanvas{position:fixed;inset:0;z-index:0}

/* HEADER */
header{
  position:fixed;top:0;left:0;right:0;z-index:100;
  padding:.9rem 2rem;display:flex;justify-content:space-between;align-items:center;
  background:rgba(3,7,18,0.8);backdrop-filter:blur(24px);
  border-bottom:1px solid rgba(99,102,241,.15);
}
.logo{display:flex;align-items:center;gap:.6rem;font-family:'Space Grotesk',sans-serif;font-size:1.2rem;font-weight:700;background:linear-gradient(135deg,#fff,#a5b4fc);-webkit-background-clip:text;background-clip:text;color:transparent;text-decoration:none}
.logo-icon{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:.9rem;box-shadow:0 0 16px rgba(99,102,241,.5)}
.header-right{display:flex;align-items:center;gap:1rem}
.user-pill{
  display:flex;align-items:center;gap:.5rem;
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
  border-radius:100px;padding:.35rem .9rem;font-size:.85rem;color:rgba(255,255,255,.7);
}
.logout-btn{
  color:rgba(255,255,255,.5);text-decoration:none;font-size:.85rem;
  padding:.35rem .8rem;border-radius:8px;transition:all .2s;
  border:1px solid rgba(255,255,255,.08);
}
.logout-btn:hover{color:#fff;background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.3)}

/* MAIN */
main{
  position:relative;z-index:10;
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  padding:6rem 1.5rem 3rem;
}
.upload-wrap{width:100%;max-width:560px}

/* GREETING */
.greeting{
  text-align:center;margin-bottom:2.5rem;
  animation:fadeUp .6s ease-out;
}
.greeting h1{
  font-family:'Space Grotesk',sans-serif;
  font-size:2rem;font-weight:700;
  background:linear-gradient(135deg,#fff,#a5b4fc);
  -webkit-background-clip:text;background-clip:text;color:transparent;
  margin-bottom:.4rem;
}
.greeting p{color:rgba(255,255,255,.4);font-size:.95rem}

/* UPLOAD CARD */
.upload-card{
  background:rgba(15,23,42,0.85);backdrop-filter:blur(32px);
  border:1px solid rgba(99,102,241,.2);border-radius:24px;
  padding:2.5rem;
  box-shadow:0 40px 80px rgba(0,0,0,.5);
  animation:fadeUp .6s ease-out .1s both;
}

/* DROP ZONE */
.drop-zone{
  border:2px dashed rgba(99,102,241,.4);
  border-radius:16px;
  padding:3rem 2rem;
  text-align:center;
  cursor:pointer;
  transition:all .3s;
  position:relative;
  background:rgba(99,102,241,.03);
}
.drop-zone:hover,.drop-zone.drag-over{
  border-color:#6366f1;
  background:rgba(99,102,241,.08);
  transform:scale(1.01);
}
.drop-icon{font-size:3rem;display:block;margin-bottom:1rem;animation:float 3s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.drop-title{font-size:1.1rem;font-weight:600;color:#f1f5f9;margin-bottom:.4rem}
.drop-sub{font-size:.85rem;color:rgba(255,255,255,.4);margin-bottom:1rem}
.format-pills{display:flex;gap:.4rem;justify-content:center;flex-wrap:wrap}
.format-pill{
  background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);
  border-radius:100px;padding:.2rem .7rem;font-size:.75rem;font-weight:600;color:#a5b4fc;
}

/* FILE SELECTED STATE */
.file-selected{
  display:none;
  background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.3);
  border-radius:12px;padding:1rem 1.2rem;margin-top:1rem;
  align-items:center;gap:.8rem;
}
.file-selected.show{display:flex}
.file-icon{font-size:1.5rem}
.file-info{flex:1}
.file-name{font-size:.9rem;font-weight:600;color:#86efac}
.file-size{font-size:.78rem;color:rgba(255,255,255,.4)}
.file-remove{background:none;border:none;color:rgba(255,255,255,.3);cursor:pointer;font-size:1.1rem;transition:color .2s}
.file-remove:hover{color:#fca5a5}

/* PROGRESS */
.progress-wrap{margin-top:1.2rem;display:none}
.progress-label{display:flex;justify-content:space-between;font-size:.8rem;color:rgba(255,255,255,.4);margin-bottom:.4rem}
.progress-track{height:6px;background:rgba(255,255,255,.08);border-radius:100px;overflow:hidden}
.progress-fill{
  height:100%;width:0%;
  background:linear-gradient(90deg,#6366f1,#8b5cf6,#06b6d4);
  background-size:200% 100%;
  border-radius:100px;
  transition:width .4s ease;
  animation:shimmer 2s infinite linear;
}
@keyframes shimmer{0%{background-position:100% 0}100%{background-position:-100% 0}}

/* ANALYZE BTN */
.analyze-btn{
  width:100%;padding:1rem;margin-top:1.5rem;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  color:#fff;border:none;border-radius:12px;
  font-size:1rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;
  box-shadow:0 0 30px rgba(99,102,241,.4);transition:all .25s;
  display:flex;align-items:center;justify-content:center;gap:.6rem;
}
.analyze-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 0 50px rgba(99,102,241,.6)}
.analyze-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}

#status{text-align:center;margin-top:.8rem;font-size:.85rem;color:rgba(255,255,255,.5)}
.error-msg{
  text-align:center;margin-top:.6rem;
  color:#fca5a5;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);
  padding:.5rem 1rem;border-radius:8px;font-size:.85rem;display:none;
}

/* FORMATS INFO */
.formats-info{
  display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-top:1.5rem;
}
.format-card{
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);
  border-radius:10px;padding:.7rem;text-align:center;
}
.format-card .fmt{font-size:1.2rem;display:block;margin-bottom:.2rem}
.format-card span{font-size:.75rem;color:rgba(255,255,255,.4)}

@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<canvas id="bgCanvas"></canvas>

<header>
  <a href="candidate_my_status.php" class="logo">
    <div class="logo-icon">⚡</div>
    ResumeIQ-X
  </a>
  <div class="header-right">
    <div class="user-pill">👤 <?php echo htmlspecialchars($userName); ?></div>
    <a href="../backend_php/logout.php" class="logout-btn">Sign out</a>
  </div>
</header>

<main>
  <div class="upload-wrap">
    <div class="greeting">
      <h1>Upload Your Resume</h1>
      <p>Our AI engine will analyze your resume across 7 intelligence layers</p>
    </div>

    <div class="upload-card">
      <div class="drop-zone" id="dropZone" onclick="document.getElementById('resumeFile').click()">
        <span class="drop-icon">📄</span>
        <div class="drop-title">Drop your resume here</div>
        <div class="drop-sub">or click to browse files</div>
        <div class="format-pills">
          <span class="format-pill">PDF</span>
          <span class="format-pill">DOCX</span>
          <span class="format-pill">TXT</span>
          <span class="format-pill">PNG</span>
          <span class="format-pill">JPG</span>
        </div>
      </div>
      <input type="file" id="resumeFile" accept=".txt,.pdf,.doc,.docx,.png,.jpg,.jpeg" style="display:none">

      <div class="file-selected" id="fileSelected">
        <span class="file-icon">📎</span>
        <div class="file-info">
          <div class="file-name" id="fileName">—</div>
          <div class="file-size" id="fileSize">—</div>
        </div>
        <button class="file-remove" onclick="clearFile()">✕</button>
      </div>

      <div class="progress-wrap" id="progressWrap">
        <div class="progress-label"><span id="progressLabel">Uploading...</span><span id="progressPct">0%</span></div>
        <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>
      </div>

      <button class="analyze-btn" id="analyzeBtn">
        <span>🚀</span> Analyze Resume
      </button>
      <div id="status"></div>
      <div class="error-msg" id="errorMsg"></div>

      <div class="formats-info">
        <div class="format-card"><span class="fmt">☁️</span><span>Cloud Storage</span></div>
        <div class="format-card"><span class="fmt">🔒</span><span>Secure Upload</span></div>
        <div class="format-card"><span class="fmt">⚡</span><span>AI Analysis</span></div>
      </div>
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
  [[.1,.2,400,'99,102,241',.1],[.9,.8,350,'6,182,212',.07]].forEach(([x,y,r,c,a])=>{
    const g=ctx.createRadialGradient(x*W,y*H,0,x*W,y*H,r);
    g.addColorStop(0,`rgba(${c},${a})`);g.addColorStop(1,'transparent');
    ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
  });
  requestAnimationFrame(draw);
}
draw();

// DRAG & DROP
const dropZone=document.getElementById('dropZone');
const fileInput=document.getElementById('resumeFile');
dropZone.addEventListener('dragover',e=>{e.preventDefault();dropZone.classList.add('drag-over')});
dropZone.addEventListener('dragleave',()=>dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop',e=>{
  e.preventDefault();dropZone.classList.remove('drag-over');
  if(e.dataTransfer.files[0]){fileInput.files=e.dataTransfer.files;showFile(e.dataTransfer.files[0])}
});
fileInput.addEventListener('change',()=>{if(fileInput.files[0])showFile(fileInput.files[0])});

function showFile(f){
  document.getElementById('fileName').textContent=f.name;
  document.getElementById('fileSize').textContent=(f.size/1024).toFixed(1)+' KB';
  document.getElementById('fileSelected').classList.add('show');
}
function clearFile(){
  fileInput.value='';
  document.getElementById('fileSelected').classList.remove('show');
  document.getElementById('fileName').textContent='—';
}

function setProgress(pct,label){
  document.getElementById('progressWrap').style.display='block';
  document.getElementById('progressFill').style.width=pct+'%';
  document.getElementById('progressPct').textContent=pct+'%';
  document.getElementById('progressLabel').textContent=label||'Uploading...';
}

async function uploadResume(){
  const errorMsg=document.getElementById('errorMsg');
  const status=document.getElementById('status');
  const btn=document.getElementById('analyzeBtn');
  errorMsg.style.display='none';status.textContent='';
  if(!fileInput.files.length){errorMsg.textContent='Please select a resume file first.';errorMsg.style.display='block';return}
  btn.disabled=true;btn.innerHTML='<span>⏳</span> Uploading...';
  setProgress(20,'Uploading to cloud...');
  const formData=new FormData();
  formData.append('resume',fileInput.files[0]);
  try{
    setProgress(40,'Sending to Cloudinary...');
    const res=await fetch(apiUrl('upload_resume.php'),{method:'POST',body:formData,credentials:'include'});
    if(!res.ok)throw new Error('Server not responding');
    setProgress(70,'Processing...');
    const result=await res.json();
    if(!result.status)throw new Error(result.message);
    localStorage.setItem('resume_upload_status',JSON.stringify(result));
    setProgress(100,'Upload complete!');
    status.textContent='✅ Resume uploaded successfully! Redirecting...';
    setTimeout(()=>window.location.href='candidate_my_status.php',1200);
  }catch(err){
    errorMsg.textContent=err.message;errorMsg.style.display='block';
    document.getElementById('progressWrap').style.display='none';
    btn.disabled=false;btn.innerHTML='<span>🚀</span> Analyze Resume';
  }
}
document.getElementById('analyzeBtn').addEventListener('click',uploadResume);

// Dynamic backend URL — works on localhost/ResumeIQ-X AND any cloud deployment
function apiUrl(script){
  const parts=window.location.pathname.split('/');
  parts.pop();parts.pop();
  return window.location.origin+parts.join('/')+'/backend_php/'+script;
}
</script>
</body>
</html>
