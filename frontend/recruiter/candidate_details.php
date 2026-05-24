<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../backend_php/session_guard.php';
$session = verifySession();
if (!$session || $session['role'] !== 'recruiter') {
    header("Location: ../recruiter_login.html"); exit;
}
$name    = htmlspecialchars($session['name']);
$company = htmlspecialchars($session['company_name'] ?? '');
$initial = strtoupper(substr($name, 0, 1));
$appUrl  = rtrim(getenv('APP_URL') ?: 'http://localhost/ResumeIQ-X', '/');
$candidateId = intval($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Candidate Details — ResumeIQ-X</title>
<link rel="stylesheet" href="<?= $appUrl ?>/frontend/assets/css/recruiter.css">
<style>
  .detail-section { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:20px; margin-bottom:20px; }
  .detail-section h3 { font-size:13px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:14px; }
  .score-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:20px; }
  .score-card  { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:20px; text-align:center; }
  .score-card .val { font-size:36px; font-weight:700; color:var(--primary); }
  .score-card .lbl { font-size:12px; color:var(--text-muted); margin-top:4px; text-transform:uppercase; letter-spacing:.5px; }
  .bullet-list { list-style:none; padding:0; }
  .bullet-list li { padding:6px 0; font-size:13px; display:flex; align-items:flex-start; gap:8px; border-bottom:1px solid rgba(51,65,85,.4); }
  .bullet-list li:last-child { border-bottom:none; }
  .bullet-green::before { content:'✅'; }
  .bullet-red::before   { content:'❌'; }
  .bullet-blue::before  { content:'💡'; }
  .candidate-header { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
  .candidate-avatar { width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:700; color:#fff; flex-shrink:0; }
  .action-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
  .job-selector { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>⚡ ResumeIQ-X</h2>
    <p>Recruiter Portal</p>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php"      class="nav-item"><span class="icon">📊</span><span>Dashboard</span></a>
    <a href="job_postings.php"   class="nav-item"><span class="icon">💼</span><span>Job Postings</span></a>
    <a href="candidates.php"     class="nav-item active"><span class="icon">👥</span><span>Candidates</span></a>
    <a href="shortlist.php"      class="nav-item"><span class="icon">✅</span><span>Shortlisted</span></a>
    <a href="communications.php" class="nav-item"><span class="icon">✉️</span><span>Communications</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= $initial ?></div>
      <div>
        <div class="user-name"><?= $name ?></div>
        <div class="user-role"><?= $company ?></div>
      </div>
    </div>
    <a href="<?= $appUrl ?>/backend_php/logout.php" class="btn btn-outline btn-sm" style="margin-top:10px;width:100%;justify-content:center;">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main class="main-content">
  <div class="page-header">
    <div>
      <a href="candidates.php" style="color:var(--text-muted);font-size:13px;text-decoration:none;">← Back to Candidates</a>
      <h1 class="page-title" style="margin-top:4px;">Candidate Details</h1>
    </div>
  </div>

  <div id="pageContent">
    <div class="loading-overlay"><div class="loading-spinner"></div> Loading candidate data…</div>
  </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
const APP_URL      = '<?= $appUrl ?>';
const CANDIDATE_ID = <?= $candidateId ?>;
let candidateData  = null;

function showToast(msg, type = 'info') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${type==='success'?'✅':type==='error'?'❌':'ℹ️'}</span><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

function parseArr(val) {
  if (!val) return [];
  if (Array.isArray(val)) return val;
  if (typeof val === 'string') { try { return JSON.parse(val); } catch(e) { return val.split(',').map(s=>s.trim()).filter(Boolean); } }
  return [];
}

function scoreBadgeClass(score) {
  if (score >= 86) return 'score-high';
  if (score >= 71) return 'score-medium';
  if (score >= 51) return 'score-low';
  return 'score-poor';
}

async function shortlist(action) {
  const jobId = document.getElementById('jobSelector')?.value;
  if (!jobId) { showToast('Please select a job first.', 'error'); return; }
  try {
    const res  = await fetch(`${APP_URL}/backend_php/shortlist_candidate.php`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ candidate_id: CANDIDATE_ID, job_id: jobId, action_type: action }) });
    const data = await res.json();
    if (data.status) showToast(`Candidate ${action}.`, 'success');
    else showToast(data.message || 'Action failed', 'error');
  } catch(err) { showToast('Network error.', 'error'); }
}

function renderPage(d) {
  const c   = d.candidate || d;
  const ar  = d.analysis  || d;
  const edu = parseArr(ar.education);
  const skills = parseArr(ar.skills || ar.extracted_skills);
  const titles = parseArr(ar.suitable_job_titles);
  const strengths = parseArr(ar.strengths);
  const weaknesses = parseArr(ar.weaknesses);
  const recommendations = parseArr(ar.recommendations);
  const appliedJobs = d.applied_jobs || [];
  const initial = (c.name || '?').charAt(0).toUpperCase();

  document.getElementById('pageContent').innerHTML = `
    <!-- Action Bar -->
    <div class="action-bar">
      <div class="job-selector">
        <label style="font-size:12px;color:var(--text-muted);">Select Job:</label>
        <select id="jobSelector" class="form-control" style="width:220px;padding:6px 10px;">
          <option value="">— Choose a job —</option>
          ${appliedJobs.map(j => `<option value="${j.job_id}">${j.title || 'Job #'+j.job_id}</option>`).join('')}
        </select>
      </div>
      <button class="btn btn-success" onclick="shortlist('accepted')">✅ Accept</button>
      <button class="btn btn-danger"  onclick="shortlist('rejected')">❌ Reject</button>
      <a href="communications.php?candidate_id=${CANDIDATE_ID}" class="btn btn-outline">✉️ Send Email</a>
      <a href="${APP_URL}/backend_php/generate_candidate_pdf.php?candidate_id=${CANDIDATE_ID}" class="btn btn-outline" target="_blank">📄 Download Report</a>
    </div>

    <!-- Header Card -->
    <div class="detail-section">
      <div class="candidate-header">
        <div class="candidate-avatar">${initial}</div>
        <div>
          <h2 style="font-size:20px;font-weight:700;">${c.name || '—'}</h2>
          <div style="color:var(--text-muted);font-size:13px;margin-top:4px;">
            ${c.email ? `📧 ${c.email}` : ''} ${c.phone ? `&nbsp;·&nbsp; 📞 ${c.phone}` : ''} ${ar.sector ? `&nbsp;·&nbsp; 🏢 ${ar.sector}` : ''}
          </div>
        </div>
      </div>
    </div>

    <!-- Score Cards -->
    <div class="score-cards">
      <div class="score-card">
        <div class="val ${scoreBadgeClass(ar.overall_score ?? 0)}" style="color:inherit">${ar.overall_score ?? '—'}%</div>
        <div class="lbl">Overall Score</div>
      </div>
      <div class="score-card">
        <div class="val" style="color:var(--info)">${ar.match_percentage ?? '—'}%</div>
        <div class="lbl">Match %</div>
      </div>
      <div class="score-card">
        <div class="val" style="color:var(--success)">${ar.experience_years ?? '—'}</div>
        <div class="lbl">Years Experience</div>
      </div>
    </div>

    <!-- Summary -->
    ${ar.summary ? `<div class="detail-section"><h3>📝 Summary</h3><p style="font-size:14px;line-height:1.7;color:var(--text-primary)">${ar.summary}</p></div>` : ''}

    <!-- Education -->
    ${edu.length ? `<div class="detail-section"><h3>🎓 Education</h3><ul class="bullet-list">${edu.map(e => `<li>🎓 <span>${typeof e === 'object' ? (e.degree||'') + (e.institution ? ' — ' + e.institution : '') + (e.year ? ' ('+e.year+')' : '') : e}</span></li>`).join('')}</ul></div>` : ''}

    <!-- Skills -->
    ${skills.length ? `<div class="detail-section"><h3>🛠 Skills</h3><div style="display:flex;flex-wrap:wrap;gap:6px;">${skills.map(s => `<span class="skill-tag">${s}</span>`).join('')}</div></div>` : ''}

    <!-- Suitable Job Titles -->
    ${titles.length ? `<div class="detail-section"><h3>🎯 Suitable Job Titles</h3><div style="display:flex;flex-wrap:wrap;gap:6px;">${titles.map(t => `<span class="status-badge status-active">${t}</span>`).join('')}</div></div>` : ''}

    <!-- Strengths / Weaknesses / Recommendations -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:20px;">
      ${strengths.length ? `<div class="detail-section" style="margin-bottom:0"><h3>💪 Strengths</h3><ul class="bullet-list">${strengths.map(s=>`<li class="bullet-green"><span>${s}</span></li>`).join('')}</ul></div>` : ''}
      ${weaknesses.length ? `<div class="detail-section" style="margin-bottom:0"><h3>⚠️ Weaknesses</h3><ul class="bullet-list">${weaknesses.map(w=>`<li class="bullet-red"><span>${w}</span></li>`).join('')}</ul></div>` : ''}
      ${recommendations.length ? `<div class="detail-section" style="margin-bottom:0"><h3>💡 Recommendations</h3><ul class="bullet-list">${recommendations.map(r=>`<li class="bullet-blue"><span>${r}</span></li>`).join('')}</ul></div>` : ''}
    </div>

    <!-- Applied Jobs -->
    ${appliedJobs.length ? `
    <div class="detail-section">
      <h3>📋 Applied Jobs</h3>
      <table>
        <thead><tr><th>Job Title</th><th>Applied At</th><th>Status</th></tr></thead>
        <tbody>
          ${appliedJobs.map(j => `<tr>
            <td>${j.title || 'Job #'+j.job_id}</td>
            <td style="color:var(--text-muted)">${j.applied_at ? new Date(j.applied_at).toLocaleDateString() : '—'}</td>
            <td><span class="status-badge ${j.shortlist_status==='accepted'?'status-accepted':j.shortlist_status==='rejected'?'status-rejected':'status-pending'}">${j.shortlist_status||'pending'}</span></td>
          </tr>`).join('')}
        </tbody>
      </table>
    </div>` : ''}

    <!-- Meta -->
    <div class="detail-section" style="font-size:12px;color:var(--text-dim);">
      ${ar.analyzed_at ? `🕐 Analyzed: ${new Date(ar.analyzed_at).toLocaleString()}` : ''}
      ${ar.llm_provider ? `&nbsp;·&nbsp; 🤖 Provider: ${ar.llm_provider}` : ''}
    </div>
  `;
}

async function loadCandidate() {
  if (!CANDIDATE_ID) {
    document.getElementById('pageContent').innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><h3>No candidate ID provided</h3><p><a href="candidates.php" style="color:var(--primary)">← Back to Candidates</a></p></div>';
    return;
  }
  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_candidate_details.php?candidate_id=${CANDIDATE_ID}`);
    const data = await res.json();
    if (!data.status) {
      document.getElementById('pageContent').innerHTML = `<div class="empty-state"><div class="empty-icon">⚠️</div><h3>${data.message || 'Candidate not found'}</h3><p><a href="candidates.php" style="color:var(--primary)">← Back</a></p></div>`;
      return;
    }
    candidateData = data.data;
    renderPage(data.data);
  } catch(err) {
    document.getElementById('pageContent').innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><h3>Network error</h3><p><a href="candidates.php" style="color:var(--primary)">← Back</a></p></div>';
  }
}

loadCandidate();
</script>
</body>
</html>
