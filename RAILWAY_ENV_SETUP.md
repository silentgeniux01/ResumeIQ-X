# Railway Environment Variables Setup

## Problem
The analysis is failing because LLM API keys are not set in Railway environment variables.

**Error:** "Analysis failed: LLM analysis failed: All LLM providers failed. Last error: Ollama not running at http://localhost:11434"

## Solution
Set the following environment variables in Railway dashboard.

## How to Set Environment Variables in Railway

1. Go to: https://railway.app
2. Select your project: **ResumeIQ-X**
3. Click on your service
4. Go to **Variables** tab
5. Add each variable below

## Required Environment Variables for LLM Analysis

### Primary LLM Provider (Groq - Fast & Free)
```
GROQ_API_KEY=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
MEERA_FORCE_PROVIDER=groq
```

### Backup LLM Providers
```
OPENAI_API_KEY=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
GEMINI_API_KEY=AIzaSyBiKp2NVekn8Lv5ozVvS5QPcxIzWJbK6bg
ANTHROPIC_API_KEY=s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
DEEPSEEK_API_KEY=sk-401fbd42cf00493b8c28db07f3027460
```

### Disable Ollama Fallback (Not Available on Railway)
```
OPENAI_QUOTA_EXCEEDED=0
```

## Quick Copy-Paste for Railway

Copy each line and paste into Railway Variables tab:

```
GROQ_API_KEY=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
MEERA_FORCE_PROVIDER=groq
OPENAI_API_KEY=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
GEMINI_API_KEY=AIzaSyBiKp2NVekn8Lv5ozVvS5QPcxIzWJbK6bg
ANTHROPIC_API_KEY=s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
DEEPSEEK_API_KEY=sk-401fbd42cf00493b8c28db07f3027460
OPENAI_QUOTA_EXCEEDED=0
```

## Verification Steps

After setting the variables:

1. **Redeploy** - Railway will automatically redeploy
2. **Wait 2-3 minutes** for deployment to complete
3. **Test Analysis**:
   - Upload a resume
   - Admin clicks "Analyze"
   - Should work without errors

## Testing Individual Providers

If Groq fails, the system will automatically try:
1. Groq (primary - fastest)
2. OpenAI (if Groq fails)
3. Gemini (if OpenAI fails)
4. Anthropic (if Gemini fails)
5. DeepSeek (if Anthropic fails)

## Troubleshooting

### If analysis still fails:

1. **Check Railway Logs**:
   - Go to Railway dashboard
   - Click "Deployments"
   - View logs for errors

2. **Verify API Keys**:
   - Test Groq API key: https://console.groq.com/keys
   - Check if key is active and has quota

3. **Check Environment Variables**:
   - Go to Railway Variables tab
   - Verify all keys are set correctly
   - No extra spaces or quotes

### Common Issues:

**Issue:** "Ollama not running"
**Solution:** This means all cloud providers failed. Check API keys are valid.

**Issue:** "Rate limit exceeded"
**Solution:** Groq has rate limits. Wait a few minutes or use another provider.

**Issue:** "Invalid API key"
**Solution:** Regenerate API key from provider's dashboard.

## Alternative: Use OpenAI as Primary

If Groq is not working, switch to OpenAI:

```
MEERA_FORCE_PROVIDER=openai
OPENAI_QUOTA_EXCEEDED=0
```

## Alternative: Use Gemini as Primary

If both Groq and OpenAI fail, use Gemini:

```
MEERA_FORCE_PROVIDER=gemini
```

---

**Important:** After setting variables, Railway will automatically redeploy. Wait 2-3 minutes before testing.

**Created by:** MAYUR GOPAL KOVE  
**Platform:** ResumeIQ-X
