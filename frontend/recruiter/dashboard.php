<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../backend_php/session_guard.php';
$session = verifySession();
if (!$session || $session['role'] !== 'recruiter') {
    header("Location: ../recruiter_login.html"); exit;
}
$name = htmlspecialchars($session['name']);
$company = htmlspecialchars($session['company_name'] ?? '');
$initial = strtoupper(substr($name, 0, 1));
$appUrl = rtrim(getenv('APP_URL') ?: 'http://localhost/ResumeIQ-X', '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recruiter Dashboard — ResumeIQ-X</title>
<link rel="stylesheet" href="<?= $appUrl ?>/frontend/assets/css/recruiter.css">
<script src="<?= $appUrl ?>/frontend/assets/js/chart.min.js"></script>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>⚡ ResumeIQ-X</h2>
    <p>Recruiter Portal</p>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item active"><span class="icon">📊</span><span>Dashboard</span></a>
    <a href="job_postings.php" class="nav-item"><span class="icon">💼</span><span>Job Postings</span></a>
    <a href="candidates.php" class="nav-item"><span class="icon">👥</span><span>Candidates</span></a>
    <a href="shortlist.php" class="nav-item"><span class="icon">✅</span><span>Shortlisted</span></a>
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
      <h1 class="page-title">Welcome back, <?= $name ?>! 👋</h1>
      <p class="page-subtitle"><?= $company ?> · Recruiter Dashboard</p>
    </div>
    <a href="job_postings.php" class="btn btn-primary">+ Post New Job</a>
  </div>

  <!-- Metrics -->
  <div class="metrics-grid" id="metricsGrid">
    <div class="metric-card"><div class="metric-icon">💼</div><div class="metric-value" id="m-jobs">—</div><div class="metric-label">Job Postings</div></div>
    <div class="metric-card"><div class="metric-icon">📄</div><div class="metric-value" id="m-apps">—</div><div class="metric-label">Total Applications</div></div>
    <div class="metric-card"><div class="metric-icon">⭐</div><div class="metric-value" id="m-qualified">—</div><div class="metric-label">Qualified (≥80%)</div></div>
    <div class="metric-card"><div class="metric-icon">⏳</div><div class="metric-value" id="m-pending">—</div><div class="metric-label">Pending Review</div></div>
    <div class="metric-card"><div class="metric-icon">✅</div><div class="metric-value" id="m-accepted">—</div><div class="metric-label">Accepted</div></div>
    <div class="metric-card"><div class="metric-icon">❌</div><div class="metric-value" id="m-rejected">—</div><div class="metric-label">Rejected</div></div>
    <div class="metric-card"><div class="metric-icon">📈</div><div class="metric-value" id="m-avgscore">—</div><div class="metric-label">Avg Score</div></div>
    <div class="metric-card"><div class="metric-icon">🎯</div><div class="metric-value" id="m-avgmatch">—</div><div class="metric-label">Avg Match</div></div>
  </div>

  <!-- Charts -->
  <div class="charts-grid">
    <div class="chart-card"><h3>📊 Applications per Job</h3><canvas id="barChart"></canvas></div>
    <div class="chart-card"><h3>🎯 Candidate Quality</h3><canvas id="pieChart"></canvas></div>
    <div class="chart-card" style="grid-column:1/-1"><h3>🔽 Hiring Funnel</h3><canvas id="funnelChart" style="max-height:160px;"></canvas></div>
  </div>

  <!-- Activity Feed -->
  <div class="activity-feed">
    <h3>🕐 Recent Activity</h3>
    <div id="activityFeed"><div class="loading-overlay"><div class="loading-spinner"></div> Loading...</div></div>
  </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
const APP_URL = '<?= $appUrl ?>';

function showToast(msg, type='info') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${type==='success'?'✅':type==='error'?'❌':'ℹ️'}</span><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

async function loadDashboard() {
  try {
    const res = await fetch(`${APP_URL}/backend_php/get_recruiter_dashboard.php`);
    const data = await res.json();
    if (!data.status) { showToast(data.message, 'error'); return; }
    const m = data.data.metrics;
    document.getElementById('m-jobs').textContent      = m.total_jobs;
    document.getElementById('m-apps').textContent      = m.total_applications;
    document.getElementById('m-qualified').textContent = m.qualified_count;
    document.getElementById('m-pending').textContent   = m.pending_count;
    document.getElementById('m-accepted').textContent  = m.accepted_count;
    document.getElementById('m-rejected').textContent  = m.rejected_count;
    document.getElementById('m-avgscore').textContent  = m.avg_score + '%';
    document.getElementById('m-avgmatch').textContent  = m.avg_match + '%';

    // Activity feed
    const feed = document.getElementById('activityFeed');
    if (data.data.recent_activity.length === 0) {
      feed.innerHTML = '<div class="empty-state"><div class="empty-icon">📭</div><p>No activity yet</p></div>';
    } else {
      feed.innerHTML = data.data.recent_activity.map(a => `
        <div class="activity-item">
          <div class="activity-dot"></div>
          <div><div class="activity-text">${a.action_description}</div>
          <div class="activity-time">${new Date(a.created_at).toLocaleString()}</div></div>
        </div>`).join('');
    }
  } catch(e) { showToast('Failed to load dashboard', 'error'); }
}

async function loadCharts() {
  try {
    const res = await fetch(`${APP_URL}/backend_php/get_dashboard_charts.php`);
    const data = await res.json();
    if (!data.status) return;
    const d = data.data;

    new Chart(document.getElementById('barChart'), { type:'bar', data: d.bar_chart, options:{ responsive:true, plugins:{legend:{display:false}}, scales:{x:{ticks:{color:'#94a3b8'}},y:{ticks:{color:'#94a3b8'},grid:{color:'#334155'}}}}});
    new Chart(document.getElementById('pieChart'), { type:'doughnut', data: d.pie_chart, options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{color:'#94a3b8'}}}}});
    new Chart(document.getElementById('funnelChart'), { type:'bar', data: d.funnel_chart, options:{ indexAxis:'y', responsive:true, plugins:{legend:{display:false}}, scales:{x:{ticks:{color:'#94a3b8'},grid:{color:'#334155'}},y:{ticks:{color:'#94a3b8'}}}}});
  } catch(e) { console.error('Charts error:', e); }
}

loadDashboard();
loadCharts();
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
        <br>• Navigating the recruiter dashboard
        <br>• Candidate insights and hiring tips
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
    const apiUrl = `${APP_URL}/backend_php/ai_chat.php`;
    
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
