<?php
session_start();
if(!isset($_SESSION['recruiter_id'])){header('Location: recruiter_login.html');exit;}
$recruiterName = $_SESSION['user_name'] ?? 'Recruiter';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recruiter Dashboard | ResumeIQ-X</title>
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
  border-bottom:1px solid rgba(14,165,233,.2);
}
.logo{display:flex;align-items:center;gap:.7rem;font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;background:linear-gradient(135deg,#fff,#38bdf8);-webkit-background-clip:text;background-clip:text;color:transparent}
.logo-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:1rem;box-shadow:0 0 20px rgba(14,165,233,.6)}
.recruiter-tag{background:rgba(14,165,233,.15);border:1px solid rgba(14,165,233,.4);border-radius:100px;padding:.25rem .8rem;font-size:.7rem;font-weight:700;letter-spacing:1px;color:#38bdf8;text-transform:uppercase}
.header-right{display:flex;align-items:center;gap:.8rem}
.recruiter-pill{display:flex;align-items:center;gap:.5rem;background:rgba(14,165,233,.1);border:1px solid rgba(14,165,233,.2);border-radius:100px;padding:.3rem .9rem;font-size:.85rem;color:#38bdf8}
.logout-btn{color:rgba(255,255,255,.5);text-decoration:none;font-size:.85rem;padding:.3rem .8rem;border-radius:8px;transition:all .2s;border:1px solid rgba(255,255,255,.08)}
.logout-btn:hover{color:#fff;background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.3)}

/* MAIN */
main{position:relative;z-index:10;padding:5.5rem 1.5rem 3rem;max-width:1400px;margin:0 auto}

/* GREETING */
.greeting{margin-bottom:2rem;animation:fadeUp .6s ease-out}
.greeting h1{font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:700;background:linear-gradient(135deg,#fff,#38bdf8);-webkit-background-clip:text;background-clip:text;color:transparent;margin-bottom:.3rem}
.greeting p{color:rgba(255,255,255,.4);font-size:.9rem}

/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;animation:fadeUp .6s ease-out .1s both}
.stat-card{background:rgba(15,23,42,.7);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:1.2rem;transition:all .3s}
.stat-card:hover{border-color:rgba(14,165,233,.4);transform:translateY(-3px)}
.stat-label{font-size:.72rem;color:rgba(255,255,255,.4);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.5px}
.stat-value{font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700;background:linear-gradient(135deg,#38bdf8,#06b6d4);-webkit-background-clip:text;background-clip:text;color:transparent}

/* QUEUE CARD */
.queue-card{background:rgba(10,15,30,.85);backdrop-filter:blur(24px);border:1px solid rgba(14,165,233,.2);border-radius:20px;padding:1.8rem;animation:fadeUp .6s ease-out .2s both}
.queue-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid rgba(14,165,233,.15)}
.queue-title{font-size:1rem;font-weight:600;color:#f1f5f9;display:flex;align-items:center;gap:.6rem}
.live-badge{display:flex;align-items:center;gap:.4rem;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:100px;padding:.25rem .7rem;font-size:.72rem;color:#86efac;font-weight:600}
.live-dot{width:6px;height:6px;background:#22c55e;border-radius:50%;box-shadow:0 0 6px #22c55e;animation:blink 2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

/* TABLE */
.table-wrap{overflow-x:auto;border-radius:12px;scrollbar-width:thin;scrollbar-color:#0ea5e9 #1e293b}
.table-wrap::-webkit-scrollbar{height:5px}
.table-wrap::-webkit-scrollbar-track{background:#1e293b;border-radius:10px}
.table-wrap::-webkit-scrollbar-thumb{background:#0ea5e9;border-radius:10px}
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
tbody tr:hover{background:rgba(30,45,75,.8);box-shadow:0 8px 20px rgba(0,0,0,.4),0 0 0 1px rgba(14,165,233,.2);transform:translateY(-2px)}
tbody td{padding:14px 16px;vertical-align:middle;color:#e2e8f0;font-size:.88rem}
tbody td:first-child{border-radius:12px 0 0 12px}
tbody td:last-child{border-radius:0 12px 12px 0}

/* STATUS BADGES */
.badge{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .8rem;border-radius:100px;font-size:.75rem;font-weight:600}
.badge-pending{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.4);color:#fbbf24}
.badge-processing{background:rgba(14,165,233,.12);border:1px solid rgba(14,165,233,.4);color:#38bdf8}
.badge-completed{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.4);color:#34d399}
.badge-failed{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.4);color:#f87171}

/* ACTION BUTTONS */
.actions{display:flex;gap:.4rem;flex-wrap:nowrap}
.btn-act{
  border:none;padding:.45rem .8rem;border-radius:8px;
  font-size:.72rem;font-weight:600;cursor:pointer;
  display:inline-flex;align-items:center;gap:.3rem;
  transition:all .2s;font-family:'Inter',sans-serif;
  white-space:nowrap;
}
.btn-view{background:rgba(99,102,241,.2);color:#a5b4fc;border:1px solid rgba(99,102,241,.4)}
.btn-view:hover{background:#6366f1;color:#fff;box-shadow:0 0 15px rgba(99,102,241,.5)}
.btn-email{background:rgba(16,185,129,.2);color:#34d399;border:1px solid rgba(16,185,129,.4)}
.btn-email:hover{background:#10b981;color:#fff;box-shadow:0 0 15px rgba(16,185,129,.5)}
.btn-download{background:rgba(59,130,246,.2);color:#93c5fd;border:1px solid rgba(59,130,246,.4)}
.btn-download:hover{background:#1d4ed8;color:#fff}
.btn-act:disabled{opacity:.4;cursor:not-allowed;transform:none !important;box-shadow:none !important}

/* LOADING */
.loading-row td{text-align:center;padding:3rem;color:rgba(255,255,255,.4)}
.spinner{display:inline-block;width:28px;height:28px;border:3px solid rgba(14,165,233,.2);border-top-color:#0ea5e9;border-radius:50%;animation:spin .8s infinite linear;margin-right:.8rem;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}

/* EMPTY */
.empty-row td{text-align:center;padding:3rem;color:rgba(255,255,255,.3);font-size:.9rem}

/* MODAL */
.modal{display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:1rem;animation:fadeIn .3s}
.modal.show{display:flex}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-content{background:rgba(15,23,42,.95);border:1px solid rgba(14,165,233,.3);border-radius:20px;padding:2rem;max-width:600px;width:100%;max-height:90vh;overflow-y:auto;animation:slideUp .4s cubic-bezier(.16,1,.3,1);box-shadow:0 40px 80px rgba(0,0,0,.8)}
@keyframes slideUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid rgba(14,165,233,.2)}
.modal-title{font-size:1.3rem;font-weight:700;color:#f1f5f9}
.modal-close{background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.4);color:#f87171;border-radius:8px;padding:.4rem .8rem;cursor:pointer;font-size:.85rem;font-weight:600;transition:all .2s}
.modal-close:hover{background:#dc2626;color:#fff}
.modal-body{color:#cbd5e1}
.form-group{margin-bottom:1.2rem}
.form-label{display:block;font-size:.85rem;font-weight:600;color:#94a3b8;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.5px}
.form-input,.form-textarea,.form-select{width:100%;padding:.8rem 1rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#f1f5f9;font-size:.9rem;font-family:'Inter',sans-serif;outline:none;transition:all .2s}
.form-input:focus,.form-textarea:focus,.form-select:focus{background:rgba(14,165,233,.1);border-color:rgba(14,165,233,.5);box-shadow:0 0 0 3px rgba(14,165,233,.15)}
.form-textarea{resize:vertical;min-height:120px}
.form-select{appearance:none;cursor:pointer}
.btn-send{width:100%;padding:.9rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:600;cursor:pointer;transition:all .25s;box-shadow:0 0 20px rgba(16,185,129,.3)}
.btn-send:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 0 30px rgba(16,185,129,.5)}
.btn-send:disabled{opacity:.5;cursor:not-allowed;transform:none}
.modal-msg{text-align:center;margin-top:1rem;font-size:.85rem;min-height:2rem}
.modal-error{color:#fca5a5;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);padding:.5rem 1rem;border-radius:8px;display:inline-block}
.modal-success{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);padding:.5rem 1rem;border-radius:8px;display:inline-block}

@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:768px){header{padding:.8rem 1rem}main{padding:5rem 1rem 2rem}.greeting h1{font-size:1.5rem}}
</style>
</head>
<body>
<canvas id="bgCanvas"></canvas>

<header>
  <div style="display:flex;align-items:center;gap:1rem">
    <div class="logo"><div class="logo-icon">🏢</div>ResumeIQ-X</div>
    <span class="recruiter-tag">Recruiter Portal</span>
  </div>
  <div class="header-right">
    <div class="recruiter-pill">👤 <?php echo htmlspecialchars($recruiterName); ?></div>
    <a href="../backend_php/logout.php" class="logout-btn">Sign out</a>
  </div>
</header>

<main>
  <div class="greeting">
    <h1>Talent Intelligence Dashboard</h1>
    <p>Access candidate resumes, analysis results, and send professional job recommendations</p>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="stat-label">Total Candidates</div><div class="stat-value" id="statTotal">—</div></div>
    <div class="stat-card"><div class="stat-label">Analyzed</div><div class="stat-value" id="statAnalyzed">—</div></div>
    <div class="stat-card"><div class="stat-label">Pending Analysis</div><div class="stat-value" id="statPending">—</div></div>
  </div>

  <div class="queue-card">
    <div class="queue-header">
      <div class="queue-title"><i class="fas fa-users" style="color:#0ea5e9"></i> Candidate Database</div>
      <div class="live-badge"><div class="live-dot"></div>LIVE DATA</div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th><i class="fas fa-user-tie"></i> Candidate</th>
            <th><i class="fas fa-envelope"></i> Email</th>
            <th><i class="fas fa-file-pdf"></i> Resume</th>
            <th><i class="fas fa-circle-nodes"></i> Status</th>
            <th><i class="fas fa-star"></i> Score</th>
            <th><i class="fas fa-tools"></i> Actions</th>
          </tr>
        </thead>
        <tbody id="candidateTable">
          <tr class="loading-row"><td colspan="6"><div class="spinner"></div><span>Loading candidates...</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- EMAIL MODAL -->
<div class="modal" id="emailModal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">📧 Send Job Recommendation</div>
      <button class="modal-close" onclick="closeEmailModal()">✕ Close</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Candidate Name</label>
        <input type="text" class="form-input" id="candidateName" readonly>
      </div>
      <div class="form-group">
        <label class="form-label">Candidate Email</label>
        <input type="email" class="form-input" id="candidateEmail" readonly>
      </div>
      <div class="form-group">
        <label class="form-label">Email Subject</label>
        <input type="text" class="form-input" id="emailSubject" value="Congratulations! Job Opportunities Matching Your Profile">
      </div>
      <div class="form-group">
        <label class="form-label">Job Recommendations</label>
        <textarea class="form-textarea" id="jobRecommendations" placeholder="Enter job titles, companies, and details..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Personal Message</label>
        <textarea class="form-textarea" id="personalMessage" placeholder="Add a personal message to the candidate..."></textarea>
      </div>
      <button class="btn-send" id="sendBtn" onclick="sendEmail()">
        <i class="fas fa-paper-plane"></i> Send Email
      </button>
      <div id="modalMsg" class="modal-msg"></div>
    </div>
  </div>
</div>

<script>
// BG
const canvas=document.getElementById('bgCanvas');
const ctx=canvas.getContext('2d');
let W,H;
function resize(){W=canvas.width=window.innerWidth;H=canvas.height=window.innerHeight}
resize();window.addEventListener('resize',resize);
function draw(){
  ctx.clearRect(0,0,W,H);ctx.fillStyle='#030712';ctx.fillRect(0,0,W,H);
  [[.2,.2,500,'14,165,233',.1],[.8,.8,400,'6,182,212',.07],[.5,.1,300,'99,102,241',.08]].forEach(([x,y,r,c,a])=>{
    const g=ctx.createRadialGradient(x*W,y*H,0,x*W,y*H,r);
    g.addColorStop(0,`rgba(${c},${a})`);g.addColorStop(1,'transparent');
    ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
  });
  requestAnimationFrame(draw);
}
draw();

function getBadge(status){
  const s=status.toLowerCase();
  if(s.includes("processing"))return`<span class="badge badge-processing">⚙️ ${status}</span>`;
  if(s.includes("completed"))return`<span class="badge badge-completed">✓ ${status}</span>`;
  if(s.includes("failed"))return`<span class="badge badge-failed">✕ ${status}</span>`;
  return`<span class="badge badge-pending">⏳ ${status}</span>`;
}

async function loadCandidates(){
  try {
    const res=await fetch("../backend_php/get_admin_dashboard_resumes.php",{credentials:"include"});
    const result=await res.json();
    const tbody=document.getElementById("candidateTable");
    const data=result.data||[];

    // Update stats
    document.getElementById('statTotal').textContent = data.length;
    document.getElementById('statAnalyzed').textContent = data.filter(r=>r.status.includes('completed')).length;
    document.getElementById('statPending').textContent = data.filter(r=>r.status==='pending').length;

    if(!data.length){
      tbody.innerHTML='<tr class="empty-row"><td colspan="6">📭 No candidates in database yet</td></tr>';
      return;
    }

    tbody.innerHTML='';
    data.forEach((r,idx)=>{
      const tr=document.createElement('tr');
      tr.setAttribute('data-id',r.id);
      tr.style.animationDelay=idx*.03+'s';
      
      // Get score from analysis if available
      const score = r.score ? Math.round(r.score) : '—';
      
      tr.innerHTML=`
        <td><i class="fas fa-user-circle" style="color:#0ea5e9;margin-right:.5rem"></i>${r.name}</td>
        <td style="color:rgba(255,255,255,.6)">${r.email}</td>
        <td><i class="far fa-file-pdf" style="color:#f87171;margin-right:.4rem"></i>${r.file_name}</td>
        <td>${getBadge(r.status)}</td>
        <td><span style="font-weight:700;color:#34d399">${score}</span></td>
        <td>
          <div class="actions">
            <button class="btn-act btn-view" onclick="viewAnalysis(${r.id})"><i class="fas fa-eye"></i> View</button>
            <button class="btn-act btn-email" onclick="openEmailModal(${r.id},'${r.name.replace(/'/g,"\\'")}','${r.email}')"><i class="fas fa-envelope"></i> Email</button>
            <button class="btn-act btn-download" onclick="downloadResume(${r.id})"><i class="fas fa-download"></i></button>
          </div>
        </td>`;
      tbody.appendChild(tr);
    });

  } catch(e) { console.error('loadCandidates error:', e); }
}

function viewAnalysis(id){
  window.location="analysis_result_viewer.php?resume_id="+id;
}

function downloadResume(id){
  window.open("../backend_php/download_resume.php?resume_id="+id);
}

let currentCandidateId = null;

function openEmailModal(id, name, email){
  currentCandidateId = id;
  document.getElementById('candidateName').value = name;
  document.getElementById('candidateEmail').value = email;
  document.getElementById('emailSubject').value = 'Congratulations! Job Opportunities Matching Your Profile';
  document.getElementById('jobRecommendations').value = '';
  document.getElementById('personalMessage').value = '';
  document.getElementById('modalMsg').innerHTML = '';
  document.getElementById('emailModal').classList.add('show');
}

function closeEmailModal(){
  document.getElementById('emailModal').classList.remove('show');
  currentCandidateId = null;
}

async function sendEmail(){
  const subject = document.getElementById('emailSubject').value.trim();
  const jobs = document.getElementById('jobRecommendations').value.trim();
  const message = document.getElementById('personalMessage').value.trim();
  const candidateName = document.getElementById('candidateName').value;
  const candidateEmail = document.getElementById('candidateEmail').value;
  const msg = document.getElementById('modalMsg');
  const btn = document.getElementById('sendBtn');
  
  msg.innerHTML = '';
  
  if(!subject || !jobs){
    msg.innerHTML = "<span class='modal-error'>Subject and job recommendations are required</span>";
    return;
  }
  
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Sending...';
  
  try {
    const res = await fetch("../backend_php/send_candidate_email.php", {
      method: "POST",
      credentials: "include",
      headers: {"Content-Type": "application/x-www-form-urlencoded"},
      body: `candidate_id=${currentCandidateId}&candidate_name=${encodeURIComponent(candidateName)}&candidate_email=${encodeURIComponent(candidateEmail)}&subject=${encodeURIComponent(subject)}&job_recommendations=${encodeURIComponent(jobs)}&personal_message=${encodeURIComponent(message)}`
    });
    
    const result = await res.json();
    
    if(result.status){
      msg.innerHTML = "<span class='modal-success'>✓ Email sent successfully!</span>";
      setTimeout(() => {
        closeEmailModal();
      }, 2000);
    } else {
      msg.innerHTML = "<span class='modal-error'>" + (result.message || 'Failed to send email') + "</span>";
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
    }
  } catch(e) {
    msg.innerHTML = "<span class='modal-error'>Server error. Please try again.</span>";
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
  }
}

// Close modal on outside click
document.getElementById('emailModal').addEventListener('click', function(e){
  if(e.target === this) closeEmailModal();
});

setInterval(loadCandidates, 10000);
loadCandidates();
</script>

<?php include 'components/ai_chat_widget.php'; ?>

</body>
</html>
