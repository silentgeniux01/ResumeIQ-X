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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shortlisted Candidates — ResumeIQ-X</title>
<link rel="stylesheet" href="<?= $appUrl ?>/frontend/assets/css/recruiter.css">
<style>
  .tab-bar { display:flex; gap:8px; padding:16px 20px; border-bottom:1px solid var(--border); }
  .tab-btn { padding:7px 18px; border-radius:8px; border:1px solid var(--border); background:transparent; color:var(--text-muted); font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; }
  .tab-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
  .tab-btn:hover:not(.active) { background:var(--bg-hover); color:var(--text-primary); }
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
    <a href="shortlist.php"      class="nav-item active"><span class="icon">✅</span><span>Shortlisted</span></a>
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
      <h1 class="page-title">✅ Shortlisted Candidates</h1>
      <p class="page-subtitle"><?= $company ?> · Review accepted and rejected candidates</p>
    </div>
  </div>

  <div class="table-card">
    <!-- Tabs -->
    <div class="tab-bar">
      <button class="tab-btn active" data-tab="all"      onclick="switchTab('all',this)">All</button>
      <button class="tab-btn"        data-tab="accepted" onclick="switchTab('accepted',this)">✅ Accepted</button>
      <button class="tab-btn"        data-tab="rejected" onclick="switchTab('rejected',this)">❌ Rejected</button>
    </div>

    <!-- Filter by Job -->
    <div class="filters-bar">
      <div class="filter-group">
        <label class="filter-label">Filter by Job</label>
        <select id="fJob" class="filter-input" style="width:220px;" onchange="loadShortlist()">
          <option value="">All Jobs</option>
        </select>
      </div>
      <span id="resultCount" style="font-size:13px;color:var(--text-muted);margin-left:auto;"></span>
    </div>

    <!-- Table -->
    <div id="tableBody">
      <div class="loading-overlay"><div class="loading-spinner"></div> Loading…</div>
    </div>
  </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
const APP_URL = '<?= $appUrl ?>';
let activeTab = 'all';

function showToast(msg, type = 'info') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${type==='success'?'✅':type==='error'?'❌':'ℹ️'}</span><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

function switchTab(tab, btn) {
  activeTab = tab;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadShortlist();
}

function actionBadge(action) {
  if (action === 'accepted') return '<span class="status-badge status-accepted">✅ Accepted</span>';
  if (action === 'rejected') return '<span class="status-badge status-rejected">❌ Rejected</span>';
  return `<span class="status-badge status-pending">${action || '—'}</span>`;
}

function scoreBadge(score) {
  let cls;
  if (score >= 86)      cls = 'score-high';
  else if (score >= 71) cls = 'score-medium';
  else if (score >= 51) cls = 'score-low';
  else                  cls = 'score-poor';
  return `<span class="score-badge ${cls}">${score ?? '—'}%</span>`;
}

async function loadShortlist() {
  const body  = document.getElementById('tableBody');
  body.innerHTML = '<div class="loading-overlay"><div class="loading-spinner"></div> Loading…</div>';
  const jobId = document.getElementById('fJob').value;
  const params = new URLSearchParams();
  if (activeTab !== 'all') params.set('action_type', activeTab);
  if (jobId) params.set('job_id', jobId);

  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_shortlisted_candidates.php?${params}`);
    const data = await res.json();
    if (!data.status) { showToast(data.message || 'Failed to load', 'error'); body.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Failed to load.</p></div>'; return; }
    const rows = data.data || [];
    document.getElementById('resultCount').textContent = `${rows.length} result${rows.length !== 1 ? 's' : ''}`;

    if (rows.length === 0) {
      body.innerHTML = `<div class="empty-state"><div class="empty-icon">✅</div><h3>No shortlisted candidates</h3><p>Accept or reject candidates from the Candidates page.</p></div>`;
      return;
    }

    body.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Score</th>
            <th>Job Title</th>
            <th>Action</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => `
            <tr>
              <td><strong>${r.name || '—'}</strong></td>
              <td style="color:var(--text-muted)">${r.email || '—'}</td>
              <td>${scoreBadge(r.overall_score)}</td>
              <td>${r.job_title || '—'}</td>
              <td>${actionBadge(r.action_type)}</td>
              <td style="color:var(--text-muted)">${r.action_timestamp ? new Date(r.action_timestamp).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'}) : '—'}</td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="candidate_details.php?id=${r.candidate_id}" class="btn btn-outline btn-sm">👁 View</a>
                  <a href="communications.php?candidate_id=${r.candidate_id}" class="btn btn-outline btn-sm">✉️ Email</a>
                </div>
              </td>
            </tr>`).join('')}
        </tbody>
      </table>`;
  } catch(err) {
    showToast('Network error.', 'error');
    body.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Network error.</p></div>';
  }
}

async function loadJobOptions() {
  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_job_postings.php`);
    const data = await res.json();
    if (!data.status) return;
    const sel = document.getElementById('fJob');
    (data.data || []).forEach(j => {
      const opt = document.createElement('option');
      opt.value = j.id;
      opt.textContent = j.title;
      sel.appendChild(opt);
    });
  } catch(e) {}
}

loadJobOptions();
loadShortlist();
</script>
</body>
</html>
