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
<title>Candidates — ResumeIQ-X</title>
<link rel="stylesheet" href="<?= $appUrl ?>/frontend/assets/css/recruiter.css">
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
      <h1 class="page-title">👥 Candidates</h1>
      <p class="page-subtitle"><?= $company ?> · Browse and manage applicants</p>
    </div>
  </div>

  <!-- Bulk Action Bar (hidden until checkboxes selected) -->
  <div id="bulkBar" style="display:none;background:var(--bg-card);border:1px solid var(--primary);border-radius:var(--radius);padding:12px 20px;margin-bottom:16px;display:none;align-items:center;gap:12px;">
    <span id="bulkCount" style="font-size:13px;color:var(--text-muted);">0 selected</span>
    <button class="btn btn-success btn-sm" onclick="bulkAction('accepted')">✅ Accept Selected</button>
    <button class="btn btn-danger btn-sm"  onclick="bulkAction('rejected')">❌ Reject Selected</button>
    <button class="btn btn-outline btn-sm" onclick="clearSelection()">Clear</button>
  </div>

  <div class="table-card">
    <!-- Filters -->
    <div class="filters-bar">
      <div class="filter-group">
        <label class="filter-label">Min Score</label>
        <input type="number" id="fScore" class="filter-input" min="0" max="100" placeholder="0" style="width:80px;">
      </div>
      <div class="filter-group">
        <label class="filter-label">Skill</label>
        <input type="text" id="fSkill" class="filter-input" placeholder="e.g. Python">
      </div>
      <div class="filter-group">
        <label class="filter-label">Min Exp (yrs)</label>
        <input type="number" id="fExp" class="filter-input" min="0" placeholder="0" style="width:80px;">
      </div>
      <div class="filter-group">
        <label class="filter-label">Sector</label>
        <input type="text" id="fSector" class="filter-input" placeholder="e.g. IT">
      </div>
      <div class="filter-group">
        <label class="filter-label">Job</label>
        <select id="fJob" class="filter-input" style="width:160px;">
          <option value="">All Jobs</option>
        </select>
      </div>
      <button class="btn btn-primary btn-sm" onclick="loadCandidates(1)">🔍 Filter</button>
      <button class="btn btn-outline btn-sm" onclick="resetFilters()">Reset</button>
    </div>

    <!-- Table -->
    <div id="tableBody">
      <div class="loading-overlay"><div class="loading-spinner"></div> Loading candidates…</div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination"></div>
  </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
const APP_URL = '<?= $appUrl ?>';
let currentPage = 1;
let totalPages  = 1;
let selectedIds = new Set();

function showToast(msg, type = 'info') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${type==='success'?'✅':type==='error'?'❌':'ℹ️'}</span><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

function scoreBadge(score) {
  let cls, label;
  if (score >= 86)      { cls = 'score-high';   label = score + '%'; }
  else if (score >= 71) { cls = 'score-medium';  label = score + '%'; }
  else if (score >= 51) { cls = 'score-low';     label = score + '%'; }
  else                  { cls = 'score-poor';    label = score + '%'; }
  return `<span class="score-badge ${cls}">${label}</span>`;
}

function shortlistBadge(status) {
  const map = { accepted:'status-accepted', rejected:'status-rejected', pending:'status-pending' };
  const cls = map[status] || 'status-pending';
  return `<span class="status-badge ${cls}">${status || 'pending'}</span>`;
}

function updateBulkBar() {
  const bar = document.getElementById('bulkBar');
  if (selectedIds.size > 0) {
    bar.style.display = 'flex';
    document.getElementById('bulkCount').textContent = `${selectedIds.size} selected`;
  } else {
    bar.style.display = 'none';
  }
}

function clearSelection() {
  selectedIds.clear();
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
  document.getElementById('selectAll').checked = false;
  updateBulkBar();
}

function getFilters() {
  return {
    min_score:      document.getElementById('fScore').value  || '',
    skill:          document.getElementById('fSkill').value  || '',
    min_experience: document.getElementById('fExp').value    || '',
    sector:         document.getElementById('fSector').value || '',
    job_id:         document.getElementById('fJob').value    || '',
  };
}

function resetFilters() {
  document.getElementById('fScore').value  = '';
  document.getElementById('fSkill').value  = '';
  document.getElementById('fExp').value    = '';
  document.getElementById('fSector').value = '';
  document.getElementById('fJob').value    = '';
  loadCandidates(1);
}

async function loadCandidates(page = 1) {
  currentPage = page;
  const body = document.getElementById('tableBody');
  body.innerHTML = '<div class="loading-overlay"><div class="loading-spinner"></div> Loading candidates…</div>';
  clearSelection();

  const filters = getFilters();
  const params  = new URLSearchParams({ ...filters, page, per_page: 20 });
  // Remove empty params
  for (const [k, v] of [...params.entries()]) { if (!v) params.delete(k); }

  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_candidates.php?${params}`);
    const data = await res.json();
    if (!data.status) { showToast(data.message || 'Failed to load candidates', 'error'); body.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Failed to load candidates.</p></div>'; return; }

    const candidates = data.data || [];
    const meta       = data.meta || {};
    totalPages = meta.total_pages || 1;

    if (candidates.length === 0) {
      body.innerHTML = `<div class="empty-state"><div class="empty-icon">👥</div><h3>No candidates found</h3><p>Try adjusting your filters.</p></div>`;
      renderPagination();
      return;
    }

    body.innerHTML = `
      <table>
        <thead>
          <tr>
            <th class="checkbox-cell"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
            <th>Name</th>
            <th>Email</th>
            <th>Score</th>
            <th>Match %</th>
            <th>Sector</th>
            <th>Experience</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${candidates.map(c => `
            <tr>
              <td class="checkbox-cell"><input type="checkbox" class="row-check" value="${c.candidate_id}" onchange="toggleRow(this)"></td>
              <td><strong>${c.name || '—'}</strong></td>
              <td style="color:var(--text-muted)">${c.email || '—'}</td>
              <td>${scoreBadge(c.overall_score ?? 0)}</td>
              <td>${scoreBadge(c.match_percentage ?? 0)}</td>
              <td>${c.sector || '—'}</td>
              <td>${c.experience_years != null ? c.experience_years + ' yr' + (c.experience_years !== 1 ? 's' : '') : '—'}</td>
              <td>${shortlistBadge(c.shortlist_status)}</td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <a href="candidate_details.php?id=${c.candidate_id}" class="btn btn-outline btn-sm">👁 View</a>
                  <button class="btn btn-success btn-sm" onclick="shortlist(${c.candidate_id}, ${c.job_id || 'null'}, 'accepted')">✅</button>
                  <button class="btn btn-danger btn-sm"  onclick="shortlist(${c.candidate_id}, ${c.job_id || 'null'}, 'rejected')">❌</button>
                </div>
              </td>
            </tr>`).join('')}
        </tbody>
      </table>`;
    renderPagination();
  } catch(err) {
    showToast('Network error.', 'error');
    body.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Network error.</p></div>';
  }
}

function toggleAll(cb) {
  document.querySelectorAll('.row-check').forEach(c => {
    c.checked = cb.checked;
    if (cb.checked) selectedIds.add(c.value);
    else selectedIds.delete(c.value);
  });
  updateBulkBar();
}

function toggleRow(cb) {
  if (cb.checked) selectedIds.add(cb.value);
  else selectedIds.delete(cb.value);
  updateBulkBar();
}

async function shortlist(candidateId, jobId, action) {
  try {
    const payload = { candidate_id: candidateId, action_type: action };
    if (jobId) payload.job_id = jobId;
    const res  = await fetch(`${APP_URL}/backend_php/shortlist_candidate.php`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const data = await res.json();
    if (data.status) { showToast(`Candidate ${action}.`, 'success'); loadCandidates(currentPage); }
    else showToast(data.message || 'Action failed', 'error');
  } catch(err) { showToast('Network error.', 'error'); }
}

async function bulkAction(action) {
  if (selectedIds.size === 0) return;
  try {
    const payload = { candidate_ids: [...selectedIds], action_type: action };
    const res  = await fetch(`${APP_URL}/backend_php/bulk_shortlist.php`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const data = await res.json();
    if (data.status) { showToast(`${selectedIds.size} candidates ${action}.`, 'success'); loadCandidates(currentPage); }
    else showToast(data.message || 'Bulk action failed', 'error');
  } catch(err) { showToast('Network error.', 'error'); }
}

function renderPagination() {
  const pg = document.getElementById('pagination');
  if (totalPages <= 1) { pg.innerHTML = ''; return; }
  let html = `<button class="page-btn" onclick="loadCandidates(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= 2) {
      html += `<button class="page-btn ${i===currentPage?'active':''}" onclick="loadCandidates(${i})">${i}</button>`;
    } else if (Math.abs(i - currentPage) === 3) {
      html += `<span style="color:var(--text-dim);padding:0 4px;">…</span>`;
    }
  }
  html += `<button class="page-btn" onclick="loadCandidates(${currentPage+1})" ${currentPage===totalPages?'disabled':''}>›</button>`;
  pg.innerHTML = html;
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
loadCandidates(1);
</script>
</body>
</html>
