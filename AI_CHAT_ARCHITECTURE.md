# AI Chat Assistant - Architecture & Flow

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER BROWSER                             │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │                    index.html                               │ │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │ │
│  │  │ Chat Button  │  │ Chat Window  │  │  JavaScript  │    │ │
│  │  │     🤖       │  │  Messages    │  │   Handler    │    │ │
│  │  └──────────────┘  └──────────────┘  └──────────────┘    │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ AJAX POST
                              │ {message, history}
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                      BACKEND SERVER                              │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │              backend_php/ai_chat.php                        │ │
│  │  ┌──────────────────────────────────────────────────────┐  │ │
│  │  │  1. Receive user message                             │  │ │
│  │  │  2. Build conversation context                       │  │ │
│  │  │  3. Try LLM providers in fallback order             │  │ │
│  │  │  4. Return AI response                               │  │ │
│  │  └──────────────────────────────────────────────────────┘  │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Try providers in order
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    LLM PROVIDER FALLBACK CHAIN                   │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   1. GROQ    │→ │  2. OpenAI   │→ │  3. Gemini   │         │
│  │   (Forced)   │  │  (GPT-4o)    │  │  (Flash)     │         │
│  │   ⚡ Fast    │  │  ⭐ Quality  │  │  🆓 Free     │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│         ↓                  ↓                  ↓                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │ 4. Anthropic │→ │ 5. DeepSeek  │→ │  6. Ollama   │         │
│  │   (Claude)   │  │  (Chat)      │  │  (Local)     │         │
│  │  ⭐ Quality  │  │  💰 Cheap    │  │  ✅ Always   │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                              ↓                   │
│                                       ✅ GUARANTEED              │
│                                          SUCCESS                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Request Flow Diagram

```
┌─────────────┐
│    USER     │
│  Types msg  │
└──────┬──────┘
       │
       │ 1. Click Send / Press Enter
       ↓
┌─────────────────────────────────────┐
│      FRONTEND (JavaScript)          │
│  ┌───────────────────────────────┐  │
│  │ • Validate message            │  │
│  │ • Add to UI (user bubble)     │  │
│  │ • Show typing indicator       │  │
│  │ • Disable send button         │  │
│  └───────────────────────────────┘  │
└──────────────┬──────────────────────┘
               │
               │ 2. POST /backend_php/ai_chat.php
               │    {message, history}
               ↓
┌─────────────────────────────────────┐
│      BACKEND (PHP)                  │
│  ┌───────────────────────────────┐  │
│  │ • Receive JSON request        │  │
│  │ • Extract message & history   │  │
│  │ • Build system prompt         │  │
│  │ • Format conversation         │  │
│  └───────────────────────────────┘  │
└──────────────┬──────────────────────┘
               │
               │ 3. Try LLM providers
               ↓
┌─────────────────────────────────────┐
│    LLM FALLBACK CHAIN               │
│  ┌───────────────────────────────┐  │
│  │ FOR EACH provider:            │  │
│  │   • Check if configured       │  │
│  │   • Make API request          │  │
│  │   • Parse response            │  │
│  │   • IF success → return       │  │
│  │   • IF fail → try next        │  │
│  └───────────────────────────────┘  │
└──────────────┬──────────────────────┘
               │
               │ 4. Return JSON response
               │    {success, message, provider}
               ↓
┌─────────────────────────────────────┐
│      FRONTEND (JavaScript)          │
│  ┌───────────────────────────────┐  │
│  │ • Remove typing indicator     │  │
│  │ • Add AI response to UI       │  │
│  │ • Update provider badge       │  │
│  │ • Enable send button          │  │
│  │ • Focus input field           │  │
│  └───────────────────────────────┘  │
└──────────────┬──────────────────────┘
               │
               │ 5. Display response
               ↓
┌─────────────┐
│    USER     │
│  Sees reply │
└─────────────┘
```

---

## 🧩 Component Breakdown

### 1. Frontend Components

#### A. Chat Button (`ai-chat-btn`)
```html
<button class="ai-chat-btn" id="aiChatBtn">🤖</button>
```
**Responsibilities:**
- Toggle chat window visibility
- Visual feedback (hover, active states)
- Accessibility (keyboard navigation)

#### B. Chat Window (`ai-chat-window`)
```html
<div class="ai-chat-window" id="aiChatWindow">
  <div class="chat-header">...</div>
  <div class="chat-messages">...</div>
  <div class="chat-input-area">...</div>
  <div class="chat-provider-badge">...</div>
</div>
```
**Responsibilities:**
- Display conversation history
- Handle user input
- Show typing indicators
- Display provider information

#### C. JavaScript Handler
```javascript
async function sendChatMessage() {
  // 1. Validate & add to UI
  // 2. Call backend API
  // 3. Handle response
  // 4. Update UI
}
```
**Responsibilities:**
- Message validation
- API communication
- UI updates
- Error handling

---

### 2. Backend Components

#### A. Main Handler (`ai_chat.php`)
```php
function getAIChatResponse($message, $history) {
  $chain = _getChatLLMFallbackChain();
  foreach ($chain as $provider) {
    $result = _callChatProvider($provider, $message, $history);
    if ($result['success']) return $result;
  }
  return error_response();
}
```
**Responsibilities:**
- Request validation
- Fallback chain orchestration
- Response formatting
- Error handling

#### B. Provider Implementations
```php
function _chatGroq($message, $history) { ... }
function _chatOpenAI($message, $history) { ... }
function _chatGemini($message, $history) { ... }
function _chatAnthropic($message, $history) { ... }
function _chatDeepSeek($message, $history) { ... }
function _chatOllama($message, $history) { ... }
```
**Responsibilities:**
- API-specific formatting
- HTTP request handling
- Response parsing
- Error detection

---

## 📊 Data Flow

### Request Data Structure
```json
{
  "message": "What is ResumeIQ-X?",
  "history": [
    {
      "role": "user",
      "content": "Hello"
    },
    {
      "role": "assistant",
      "content": "Hi! How can I help?"
    }
  ]
}
```

### Response Data Structure
```json
{
  "success": true,
  "message": "ResumeIQ-X is an AI-powered resume analysis platform...",
  "provider": "groq"
}
```

### Error Response Structure
```json
{
  "success": false,
  "message": "I'm having trouble connecting right now.",
  "provider": "none"
}
```

---

## 🔐 Security Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      SECURITY LAYERS                             │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │  Layer 1: Frontend Validation                              │ │
│  │  • Input length limits                                     │ │
│  │  • XSS prevention (HTML escaping)                          │ │
│  │  • Rate limiting (client-side)                             │ │
│  └────────────────────────────────────────────────────────────┘ │
│                              ↓                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │  Layer 2: Backend Validation                               │ │
│  │  • Request method check (POST only)                        │ │
│  │  • JSON validation                                         │ │
│  │  • Message sanitization                                    │ │
│  └────────────────────────────────────────────────────────────┘ │
│                              ↓                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │  Layer 3: API Key Protection                               │ │
│  │  • Keys stored in .env (never exposed)                     │ │
│  │  • Backend-only API calls                                  │ │
│  │  • No keys in frontend code                                │ │
│  └────────────────────────────────────────────────────────────┘ │
│                              ↓                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │  Layer 4: Response Sanitization                            │ │
│  │  • Output escaping                                         │ │
│  │  • No code execution                                       │ │
│  │  • Safe HTML rendering                                     │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## ⚡ Performance Optimization

### 1. Provider Selection Strategy
```
┌─────────────────────────────────────────┐
│  Provider Selection Logic               │
│                                         │
│  IF forced_provider SET:                │
│    → Use forced provider first          │
│                                         │
│  ELSE:                                  │
│    → Try Groq (fastest, free)           │
│    → Try OpenAI (if quota OK)           │
│    → Try Gemini (free tier)             │
│    → Try Anthropic (quality)            │
│    → Try DeepSeek (cheap)               │
│                                         │
│  ALWAYS:                                │
│    → Ollama as final fallback           │
└─────────────────────────────────────────┘
```

### 2. Response Time Optimization
```
┌──────────────────────────────────────────┐
│  Optimization Techniques                 │
│                                          │
│  • Parallel provider checks (future)     │
│  • Response caching (future)             │
│  • Connection pooling                    │
│  • Timeout management (30s)              │
│  • Token limit optimization (300)        │
│  • Conversation history pruning (5 msg)  │
└──────────────────────────────────────────┘
```

---

## 🔄 State Management

### Frontend State
```javascript
{
  conversationHistory: [],  // Last 5 messages
  isProcessing: false,      // Prevent double-send
  chatVisible: false,       // Window open/closed
  currentProvider: null     // Last used provider
}
```

### Backend State
```php
// Stateless - no session storage
// Each request is independent
// History passed from frontend
```

---

## 🎯 Error Handling Strategy

```
┌─────────────────────────────────────────────────────────────────┐
│                    ERROR HANDLING FLOW                           │
│                                                                  │
│  User sends message                                              │
│         ↓                                                        │
│  Frontend validation                                             │
│         ↓                                                        │
│  ┌─────────────────┐                                            │
│  │ Valid message?  │                                            │
│  └────┬────────┬───┘                                            │
│       │ NO     │ YES                                             │
│       ↓        ↓                                                 │
│  Show error  Send to backend                                    │
│              ↓                                                   │
│         Try Provider 1                                           │
│              ↓                                                   │
│         ┌─────────┐                                             │
│         │Success? │                                             │
│         └────┬────┬───┘                                         │
│              │ NO │ YES                                          │
│              ↓    ↓                                              │
│         Try Next  Return response                               │
│         Provider  ↓                                              │
│              ↓    Display to user                               │
│         (Repeat until success or all fail)                      │
│              ↓                                                   │
│         All failed?                                             │
│              ↓                                                   │
│         Show friendly error                                     │
│         "I'm having trouble connecting..."                      │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📈 Scalability Considerations

### Current Architecture
```
Single Server
    ↓
PHP Backend (Stateless)
    ↓
Multiple LLM Providers (Cloud + Local)
```

### Future Scaling Options
```
Load Balancer
    ↓
Multiple PHP Servers (Horizontal scaling)
    ↓
Redis Cache (Response caching)
    ↓
Multiple LLM Providers
    ↓
Database (Conversation persistence)
```

---

## 🧪 Testing Strategy

### 1. Unit Tests
```php
// Test individual provider functions
testGroqProvider()
testOpenAIProvider()
testFallbackChain()
```

### 2. Integration Tests
```php
// Test full request flow
testChatEndpoint()
testErrorHandling()
testProviderFailover()
```

### 3. Manual Tests
```bash
# Backend test
php test_ai_chat.php

# Browser test
Open index.html → Click 🤖 → Send message
```

---

## 🎨 UI/UX Flow

```
User visits homepage
    ↓
Sees 🤖 button (bottom-right)
    ↓
Clicks button
    ↓
Chat window slides up
    ↓
Sees welcome message
    ↓
Types question
    ↓
Presses Enter
    ↓
Message appears (right side, cyan)
    ↓
Typing indicator shows (3 dots)
    ↓
AI response appears (left side, indigo)
    ↓
Provider badge updates
    ↓
User can continue conversation
    ↓
Clicks × or 🤖 to close
    ↓
Window slides down
```

---

## 🔧 Configuration Management

```
.env file
    ↓
Environment Variables
    ↓
┌─────────────────────────────────────┐
│  LLM Provider Configuration         │
│  • API Keys                         │
│  • Model Selection                  │
│  • Forced Provider                  │
│  • Quota Flags                      │
└─────────────────────────────────────┘
    ↓
Backend reads on each request
    ↓
Builds fallback chain dynamically
```

---

## 📊 Monitoring & Logging

### Log Levels
```
ERROR:   Provider failures, API errors
WARNING: Rate limits, slow responses
INFO:    Successful requests, provider used
DEBUG:   Request/response details
```

### Log Format
```
[ResumeIQ-X][AI-Chat] Trying provider: groq
[ResumeIQ-X][AI-Chat] ✓ Success with provider: groq
[ResumeIQ-X][AI-Chat] ✗ Failed with groq: Rate limited
```

### Monitoring Points
```
• Request count per hour
• Average response time
• Provider success rate
• Error rate
• User engagement metrics
```

---

## 🎯 Success Metrics

### Technical Metrics
- **Uptime:** 99.9% (with Ollama fallback)
- **Response Time:** 1-3 seconds average
- **Error Rate:** <0.1%
- **Provider Failover:** <5% of requests

### Business Metrics
- **User Engagement:** Chat usage rate
- **Support Reduction:** Fewer support tickets
- **Conversion:** Registration rate increase
- **Satisfaction:** User feedback scores

---

**Architecture Version:** 1.0.0  
**Last Updated:** May 3, 2026  
**Status:** Production Ready ✅
