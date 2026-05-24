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
$preselectedCandidateId = intval($_GET['candidate_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Communications — ResumeIQ-X</title>
<link rel="stylesheet" href="<?= $appUrl ?>/frontend/assets/css/recruiter.css">
<style>
  .comm-layout { display:grid; grid-template-columns:280px 1fr; gap:20px; }
  .candidate-panel { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:16px; height:fit-content; }
  .candidate-panel h3 { font-size:13px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px; }
  .candidate-item { padding:10px; border-radius:8px; cursor:pointer; transition:background .15s; border:1px solid transparent; margin-bottom:6px; }
  .candidate-item:hover { background:var(--bg-hover); }
  .candidate-item.selected { background:rgba(99,102,241,.15); border-color:var(--primary); }
  .candidate-item .c-name { font-size:13px; font-weight:600; }
  .candidate-item .c-email { font-size:11px; color:var(--text-dim); margin-top:2px; }
  .composer-card { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:20px; }
  .composer-card h3 { font-size:13px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; }
  .interview-fields { background:rgba(99,102,241,.07); border:1px solid rgba(99,102,241,.2); border-radius:8px; padding:14px; margin-top:8px; }
  .history-card { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; margin-top:20px; }
  .history-card .table-header { padding:14px 20px; border-bottom:1px solid var(--border); }
  .history-card .table-header h3 { font-size:14px; font-weight:600; }
  @media(max-width:768px){ .comm-layout{ grid-template-columns:1fr; } }
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
    <a href="candidates.php"     class="nav-item"><span class="icon">👥</span><span>Candidates</span></a>
    <a href="shortlist.php"      class="nav-item"><span class="icon">✅</span><span>Shortlisted</span></a>
    <a href="communications.php" class="nav-item active"><span class="icon">✉️</span><span>Communications</span></a>
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
      <h1 class="page-title">✉️ Communications</h1>
      <p class="page-subtitle"><?= $company ?> · Send emails to candidates</p>
    </div>
  </div>

  <div class="comm-layout">
    <!-- Left: Candidate Panel -->
    <div>
      <div class="candidate-panel">
        <h3>👥 Select Candidate</h3>
        <input type="text" id="candidateSearch" class="form-control" placeholder="Search by name or email…" oninput="filterCandidates()" style="margin-bottom:10px;">
        <div id="candidateList">
          <div class="loading-overlay" style="padding:20px;"><div class="loading-spinner"></div></div>
        </div>
      </div>
    </div>

    <!-- Right: Composer + History -->
    <div>
      <div class="composer-card">
        <h3>📝 Compose Email</h3>
        <div id="noSelectionMsg" style="text-align:center;padding:24px;color:var(--text-muted);">
          <div style="font-size:32px;margin-bottom:8px;">👈</div>
          <p>Select a candidate to compose an email</p>
        </div>
        <div id="composerForm" style="display:none;">
          <div class="form-group">
            <label class="form-label">Template</label>
            <select id="templateSelect" class="form-control" onchange="onTemplateChange()">
              <option value="">— No template —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Subject *</label>
            <input type="text" id="emailSubject" class="form-control" placeholder="Email subject…" required>
          </div>
          <div class="form-group">
            <label class="form-label">Message *</label>
            <textarea id="emailBody" class="form-control" rows="7" placeholder="Write your message here…" required></textarea>
          </div>

          <!-- Interview fields (shown for interview templates) -->
          <div id="interviewFields" class="interview-fields" style="display:none;">
            <p style="font-size:12px;color:var(--primary);font-weight:600;margin-bottom:10px;">📅 Interview Details</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Date</label>
                <input type="date" id="interviewDate" class="form-control">
              </div>
              <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Time</label>
                <input type="time" id="interviewTime" class="form-control">
              </div>
            </div>
            <div class="form-group" style="margin-top:12px;margin-bottom:0">
              <label class="form-label">Location / Meeting Link</label>
              <input type="text" id="interviewLocation" class="form-control" placeholder="e.g. Zoom link or office address">
            </div>
          </div>

          <div style="display:flex;justify-content:flex-end;margin-top:16px;">
            <button class="btn btn-primary" onclick="sendEmail()" id="sendBtn">✉️ Send Email</button>
          </div>
        </div>
      </div>

      <!-- Communication History -->
      <div class="history-card" id="historyCard" style="display:none;">
        <div class="table-header"><h3>📬 Communication History</h3></div>
        <div id="historyBody">
          <div class="loading-overlay"><div class="loading-spinner"></div></div>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
const APP_URL             = '<?= $appUrl ?>';
const PRESELECTED_ID      = <?= $preselectedCandidateId ?>;
let allCandidates         = [];
let selectedCandidateId   = null;
let templates             = [];

function showToast(msg, type = 'info') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${type==='success'?'✅':type==='error'?'❌':'ℹ️'}</span><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

function filterCandidates() {
  const q = document.getElementById('candidateSearch').value.toLowerCase();
  renderCandidateList(q ? allCandidates.filter(c => (c.name||'').toLowerCase().includes(q) || (c.email||'').toLowerCase().includes(q)) : allCandidates);
}

function renderCandidateList(list) {
  const el = document.getElementById('candidateList');
  if (!list.length) { el.innerHTML = '<p style="font-size:13px;color:var(--text-dim);text-align:center;padding:12px;">No candidates found</p>'; return; }
  el.innerHTML = list.map(c => `
    <div class="candidate-item ${c.candidate_id == selectedCandidateId ? 'selected' : ''}" onclick="selectCandidate(${c.candidate_id})" id="ci-${c.candidate_id}">
      <div class="c-name">${c.name || '—'}</div>
      <div class="c-email">${c.email || ''}</div>
    </div>`).join('');
}

function selectCandidate(id) {
  selectedCandidateId = id;
  document.querySelectorAll('.candidate-item').forEach(el => el.classList.remove('selected'));
  const el = document.getElementById(`ci-${id}`);
  if (el) el.classList.add('selected');
  document.getElementById('noSelectionMsg').style.display = 'none';
  document.getElementById('composerForm').style.display   = 'block';
  document.getElementById('historyCard').style.display    = 'block';
  loadHistory(id);
}

function onTemplateChange() {
  const sel = document.getElementById('templateSelect');
  const tpl = templates.find(t => t.id == sel.value);
  if (tpl) {
    document.getElementById('emailSubject').value = tpl.subject || '';
    document.getElementById('emailBody').value    = tpl.body    || '';
    const isInterview = (tpl.template_type || tpl.name || '').toLowerCase().includes('interview');
    document.getElementById('interviewFields').style.display = isInterview ? 'block' : 'none';
  } else {
    document.getElementById('interviewFields').style.display = 'none';
  }
}

async function sendEmail() {
  if (!selectedCandidateId) { showToast('Please select a candidate.', 'error'); return; }
  const subject = document.getElementById('emailSubject').value.trim();
  const body    = document.getElementById('emailBody').value.trim();
  if (!subject || !body) { showToast('Subject and message are required.', 'error'); return; }

  const payload = {
    candidate_id: selectedCandidateId,
    subject,
    body,
    template_id: document.getElementById('templateSelect').value || null,
  };

  const interviewVisible = document.getElementById('interviewFields').style.display !== 'none';
  if (interviewVisible) {
    payload.interview_date     = document.getElementById('interviewDate').value     || null;
    payload.interview_time     = document.getElementById('interviewTime').value     || null;
    payload.interview_location = document.getElementById('interviewLocation').value || null;
  }

  const btn = document.getElementById('sendBtn');
  btn.disabled = true; btn.textContent = 'Sending…';

  try {
    const res  = await fetch(`${APP_URL}/backend_php/send_candidate_email.php`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const data = await res.json();
    if (data.status) {
      showToast('Email sent successfully!', 'success');
      document.getElementById('emailSubject').value = '';
      document.getElementById('emailBody').value    = '';
      document.getElementById('templateSelect').value = '';
      document.getElementById('interviewFields').style.display = 'none';
      loadHistory(selectedCandidateId);
    } else {
      showToast(data.message || 'Failed to send email', 'error');
    }
  } catch(err) { showToast('Network error.', 'error'); }
  finally { btn.disabled = false; btn.textContent = '✉️ Send Email'; }
}

async function loadHistory(candidateId) {
  const body = document.getElementById('historyBody');
  body.innerHTML = '<div class="loading-overlay" style="padding:20px;"><div class="loading-spinner"></div></div>';
  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_communication_history.php?candidate_id=${candidateId}`);
    const data = await res.json();
    if (!data.status) { body.innerHTML = '<div class="empty-state" style="padding:20px;"><p>No history available.</p></div>'; return; }
    const rows = data.data || [];
    if (!rows.length) { body.innerHTML = '<div class="empty-state" style="padding:20px;"><div class="empty-icon">📭</div><p>No emails sent yet.</p></div>'; return; }
    body.innerHTML = `
      <table>
        <thead><tr><th>Subject</th><th>Template</th><th>Sent At</th></tr></thead>
        <tbody>
          ${rows.map(r => `<tr>
            <td>${r.subject || '—'}</td>
            <td>${r.template_used || r.template_name || '—'}</td>
            <td style="color:var(--text-muted)">${r.sent_at ? new Date(r.sent_at).toLocaleString() : '—'}</td>
          </tr>`).join('')}
        </tbody>
      </table>`;
  } catch(err) { body.innerHTML = '<div class="empty-state" style="padding:20px;"><p>Failed to load history.</p></div>'; }
}

async function loadTemplates() {
  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_email_templates.php`);
    const data = await res.json();
    if (!data.status) return;
    templates = data.data || [];
    const sel = document.getElementById('templateSelect');
    templates.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.id;
      opt.textContent = t.name || t.template_name || `Template #${t.id}`;
      sel.appendChild(opt);
    });
  } catch(e) {}
}

async function loadCandidates() {
  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_candidates.php?per_page=200`);
    const data = await res.json();
    allCandidates = data.data || [];
    renderCandidateList(allCandidates);
    if (PRESELECTED_ID) {
      // Scroll to and select preselected candidate
      setTimeout(() => selectCandidate(PRESELECTED_ID), 100);
    }
  } catch(err) {
    document.getElementById('candidateList').innerHTML = '<p style="font-size:13px;color:var(--text-dim);text-align:center;padding:12px;">Failed to load candidates</p>';
  }
}

loadTemplates();
loadCandidates();
</script>
</body>
</html>
