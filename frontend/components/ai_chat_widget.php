<!-- AI Chat Widget Component -->
<!-- Include this file in any dashboard to add AI chat assistant -->

<style>
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
  background:#0f172a;
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
  background:#1e293b;
  border-top:1px solid rgba(255,255,255,.08);
  display:flex;gap:.6rem;
}
.chat-input{
  flex:1;
  background:#0f172a;
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

/* ── RESPONSIVE ── */
@media(max-width:768px){
  .ai-chat-window{
    width:calc(100vw - 2rem);
    height:calc(100vh - 8rem);
    right:1rem;bottom:5rem;
  }
  .ai-chat-btn{right:1rem;bottom:1rem}
}
</style>

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
        <br>• Navigating the platform
        <br>• Resume tips and career advice
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
(function() {
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
      // Call AI backend - construct proper URL
      const protocol = window.location.protocol;
      const host = window.location.host;
      const pathname = window.location.pathname;
      
      // Get current directory
      let currentDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);
      
      // Go up one level to project root (since dashboards are in /frontend/)
      let projectRoot = currentDir.substring(0, currentDir.lastIndexOf('/', currentDir.length - 2) + 1);
      
      // Construct full API URL
      const apiUrl = `${protocol}//${host}${projectRoot}backend_php/ai_chat.php`;
      
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
})();
</script>
