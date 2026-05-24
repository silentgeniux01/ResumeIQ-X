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
<title>Job Postings — ResumeIQ-X</title>
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
    <a href="dashboard.php"     class="nav-item"><span class="icon">📊</span><span>Dashboard</span></a>
    <a href="job_postings.php"  class="nav-item active"><span class="icon">💼</span><span>Job Postings</span></a>
    <a href="candidates.php"    class="nav-item"><span class="icon">👥</span><span>Candidates</span></a>
    <a href="shortlist.php"     class="nav-item"><span class="icon">✅</span><span>Shortlisted</span></a>
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
      <h1 class="page-title">💼 Job Postings</h1>
      <p class="page-subtitle"><?= $company ?> · Manage your job listings</p>
    </div>
    <button class="btn btn-primary" onclick="openCreateModal()">+ Create New Job</button>
  </div>

  <div class="table-card">
    <div class="table-header">
      <h3>All Job Postings</h3>
      <span id="jobCount" style="font-size:13px;color:var(--text-muted);">Loading…</span>
    </div>
    <div id="tableBody">
      <div class="loading-overlay"><div class="loading-spinner"></div> Loading jobs…</div>
    </div>
  </div>
</main>

<!-- Job Modal -->
<div class="modal-overlay" id="jobModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="modalTitle">Create New Job</h2>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="jobForm" onsubmit="submitJob(event)">
      <input type="hidden" id="jobId" value="">
      <div class="form-group">
        <label class="form-label">Job Title *</label>
        <input type="text" id="fTitle" class="form-control" placeholder="e.g. Senior Software Engineer" required>
      </div>
      <div class="form-group">
        <label class="form-label">Description *</label>
        <textarea id="fDesc" class="form-control" rows="4" placeholder="Describe the role, responsibilities…" required></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Required Skills (comma-separated)</label>
        <input type="text" id="fSkills" class="form-control" placeholder="e.g. Python, React, SQL">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Experience Required (years)</label>
          <input type="number" id="fExp" class="form-control" min="0" max="30" placeholder="3">
        </div>
        <div class="form-group">
          <label class="form-label">Salary Range</label>
          <input type="text" id="fSalary" class="form-control" placeholder="e.g. $80k–$120k">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Location</label>
          <input type="text" id="fLocation" class="form-control" placeholder="e.g. Remote / New York">
        </div>
        <div class="form-group">
          <label class="form-label">Employment Type</label>
          <select id="fType" class="form-control">
            <option value="full-time">Full-Time</option>
            <option value="part-time">Part-Time</option>
            <option value="contract">Contract</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="submitBtn">Create Job</button>
      </div>
    </form>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
const APP_URL = '<?= $appUrl ?>';

function showToast(msg, type = 'info') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${type==='success'?'✅':type==='error'?'❌':'ℹ️'}</span><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

function openCreateModal() {
  document.getElementById('modalTitle').textContent = 'Create New Job';
  document.getElementById('submitBtn').textContent  = 'Create Job';
  document.getElementById('jobForm').reset();
  document.getElementById('jobId').value = '';
  document.getElementById('jobModal').classList.add('active');
}

function openEditModal(job) {
  document.getElementById('modalTitle').textContent = 'Edit Job Posting';
  document.getElementById('submitBtn').textContent  = 'Save Changes';
  document.getElementById('jobId').value    = job.id;
  document.getElementById('fTitle').value   = job.title || '';
  document.getElementById('fDesc').value    = job.description || '';
  document.getElementById('fExp').value     = job.experience_required || '';
  document.getElementById('fSalary').value  = job.salary_range || '';
  document.getElementById('fLocation').value = job.location || '';
  document.getElementById('fType').value    = job.employment_type || 'full-time';
  // skills: stored as JSON array, display as comma-separated
  let skills = job.required_skills || [];
  if (typeof skills === 'string') { try { skills = JSON.parse(skills); } catch(e) { skills = [skills]; } }
  document.getElementById('fSkills').value = Array.isArray(skills) ? skills.join(', ') : skills;
  document.getElementById('jobModal').classList.add('active');
}

function closeModal() {
  document.getElementById('jobModal').classList.remove('active');
}

async function submitJob(e) {
  e.preventDefault();
  const id = document.getElementById('jobId').value;
  const skillsRaw = document.getElementById('fSkills').value;
  const skillsArr = skillsRaw.split(',').map(s => s.trim()).filter(Boolean);

  const payload = {
    title:               document.getElementById('fTitle').value.trim(),
    description:         document.getElementById('fDesc').value.trim(),
    required_skills:     skillsArr,
    experience_required: parseInt(document.getElementById('fExp').value) || 0,
    salary_range:        document.getElementById('fSalary').value.trim(),
    location:            document.getElementById('fLocation').value.trim(),
    employment_type:     document.getElementById('fType').value,
  };

  const endpoint = id
    ? `${APP_URL}/backend_php/update_job_posting.php`
    : `${APP_URL}/backend_php/create_job_posting.php`;

  if (id) payload.job_id = id;

  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.textContent = 'Saving…';

  try {
    const res  = await fetch(endpoint, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const data = await res.json();
    if (data.status) {
      showToast(id ? 'Job updated successfully!' : 'Job created successfully!', 'success');
      closeModal();
      loadJobs();
    } else {
      showToast(data.message || 'Operation failed', 'error');
    }
  } catch(err) {
    showToast('Network error. Please try again.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = id ? 'Save Changes' : 'Create Job';
  }
}

async function deleteJob(id, title) {
  if (!confirm(`Delete job posting "${title}"?\n\nThis action cannot be undone.`)) return;
  try {
    const res  = await fetch(`${APP_URL}/backend_php/delete_job_posting.php`, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ job_id: id }) });
    const data = await res.json();
    if (data.status) {
      showToast('Job deleted.', 'success');
      loadJobs();
    } else {
      showToast(data.message || 'Delete failed', 'error');
    }
  } catch(err) {
    showToast('Network error.', 'error');
  }
}

function formatDate(str) {
  if (!str) return '—';
  return new Date(str).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });
}

function statusBadge(status) {
  const map = { active:'status-active', closed:'status-rejected', draft:'status-pending' };
  const cls = map[status] || 'status-pending';
  return `<span class="status-badge ${cls}">${status || 'draft'}</span>`;
}

function typeBadge(type) {
  const map = { 'full-time':'status-active', 'part-time':'status-pending', 'contract':'score-medium' };
  const cls = map[type] || 'status-pending';
  return `<span class="status-badge ${cls}">${type || '—'}</span>`;
}

async function loadJobs() {
  const body = document.getElementById('tableBody');
  body.innerHTML = '<div class="loading-overlay"><div class="loading-spinner"></div> Loading jobs…</div>';
  try {
    const res  = await fetch(`${APP_URL}/backend_php/get_job_postings.php`);
    const data = await res.json();
    if (!data.status) { showToast(data.message || 'Failed to load jobs', 'error'); body.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Failed to load jobs.</p></div>'; return; }
    const jobs = data.data || [];
    document.getElementById('jobCount').textContent = `${jobs.length} posting${jobs.length !== 1 ? 's' : ''}`;
    if (jobs.length === 0) {
      body.innerHTML = `<div class="empty-state"><div class="empty-icon">💼</div><h3>No job postings yet</h3><p>Click "Create New Job" to get started.</p></div>`;
      return;
    }
    body.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Location</th>
            <th>Applications</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${jobs.map(j => `
            <tr>
              <td><strong>${j.title || '—'}</strong></td>
              <td>${typeBadge(j.employment_type)}</td>
              <td>${j.location || '—'}</td>
              <td><span class="score-badge score-medium">${j.application_count ?? 0}</span></td>
              <td>${statusBadge(j.status)}</td>
              <td style="color:var(--text-muted)">${formatDate(j.created_at)}</td>
              <td>
                <div style="display:flex;gap:6px;">
                  <button class="btn btn-outline btn-sm" onclick='openEditModal(${JSON.stringify(j)})'>✏️ Edit</button>
                  <button class="btn btn-danger btn-sm"  onclick="deleteJob(${j.id}, '${(j.title||'').replace(/'/g,"\\'")}')">🗑️ Delete</button>
                </div>
              </td>
            </tr>`).join('')}
        </tbody>
      </table>`;
  } catch(err) {
    showToast('Network error loading jobs.', 'error');
    body.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Network error.</p></div>';
  }
}

// Close modal on overlay click
document.getElementById('jobModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

loadJobs();
</script>
</body>
</html>
