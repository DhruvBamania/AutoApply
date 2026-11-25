// AutoApply AI Companion - Background Service Worker (Manifest V3)

const DEFAULT_API_URL = 'https://auto-apply-two-phi.vercel.app';

// Helper: Get configured API URL and Bearer Token
async function getConfig() {
  const result = await chrome.storage.local.get(['apiUrl', 'authToken']);
  const apiUrl = (result.apiUrl || DEFAULT_API_URL).replace(/\/+$/, '');
  const authToken = result.authToken || '';
  return { apiUrl, authToken };
}

// Helper: Make authenticated JSON request to AutoApply API
async function apiRequest(endpoint, method = 'GET', body = null) {
  const { apiUrl, authToken } = await getConfig();

  if (!authToken) {
    throw new Error('NOT_AUTHENTICATED: Please log in via the AutoApply extension popup.');
  }

  // Ensure /v1 prefix (bypasses Vercel's /api Serverless function mount collision)
  const cleanBase = apiUrl.replace(/\/api\/v1$|\/api$|\/v1$/, '');
  const base = `${cleanBase}/v1`;
  const url = `${base}${endpoint}`;

  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${authToken}`
  };

  const options = {
    method,
    headers
  };

  if (body && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(url, options);

  if (response.status === 401) {
    await chrome.storage.local.remove(['authToken', 'userProfile']);
    throw new Error('SESSION_EXPIRED: Your session has expired. Please log in again.');
  }

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || data.error || `HTTP error ${response.status}`);
  }

  return data;
}

// Runtime message listener
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  const handleMessage = async () => {
    switch (message.action) {
      case 'CHECK_STATUS': {
        const { apiUrl, authToken } = await getConfig();
        if (!authToken) {
          return { success: false, authenticated: false, apiUrl };
        }
        try {
          const res = await apiRequest('/candidate/extension/status');
          return { success: true, authenticated: true, apiUrl, data: res };
        } catch (err) {
          return { success: false, authenticated: false, error: err.message, apiUrl };
        }
      }

      case 'LOGIN': {
        const { email, password, apiUrl: customUrl } = message.payload;
        const targetApiUrl = (customUrl || DEFAULT_API_URL).replace(/\/+$/, '');
        const cleanBase = targetApiUrl.replace(/\/api\/v1$|\/api$|\/v1$/, '');
        const base = `${cleanBase}/v1`;

        let res;
        try {
          res = await fetch(`${base}/auth/login`, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password, device_name: 'Chrome Extension Companion' })
          });
        } catch (netErr) {
          throw new Error(`Cannot connect to server at ${cleanBase}. If running locally, check if your local server is running or set URL to http://127.0.0.1:8000.`);
        }

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
          const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : null) || `Login failed (HTTP ${res.status})`;
          throw new Error(errMsg);
        }

        const token = data.token || data.access_token;
        const user = data.user;

        await chrome.storage.local.set({
          authToken: token,
          userProfile: user,
          apiUrl: targetApiUrl
        });

        return { success: true, user, token };
      }

      case 'ANALYZE_JOB': {
        return await apiRequest('/candidate/extension/analyze', 'POST', message.payload);
      }

      case 'GENERATE_ANSWER': {
        return await apiRequest('/candidate/extension/generate-answer', 'POST', message.payload);
      }

      case 'GET_AUTOFILL_PROFILE': {
        return await apiRequest('/candidate/extension/autofill-profile', 'GET');
      }

      case 'SAVE_JOB': {
        return await apiRequest('/candidate/extension/save-job', 'POST', message.payload);
      }

      case 'LOGOUT': {
        try {
          await apiRequest('/auth/logout', 'POST');
        } catch (_) {}
        await chrome.storage.local.remove(['authToken', 'userProfile']);
        return { success: true };
      }

      default:
        throw new Error(`Unknown action: ${message.action}`);
    }
  };

  handleMessage()
    .then(res => sendResponse(res))
    .catch(err => sendResponse({ success: false, error: err.message }));

  return true; // Keep message channel open for async response
});

// Update badge when on job portal
chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
  if (changeInfo.status === 'complete' && tab.url) {
    const jobSites = ['linkedin.com', 'indeed.com', 'glassdoor.com', 'internshala.com', 'lever.co', 'greenhouse.io', 'myworkdayjobs.com'];
    const isJobPortal = jobSites.some(site => tab.url.includes(site));
    if (isJobPortal) {
      chrome.action.setBadgeText({ tabId, text: 'AI' });
      chrome.action.setBadgeBackgroundColor({ tabId, color: '#4f46e5' });
    } else {
      chrome.action.setBadgeText({ tabId, text: '' });
    }
  }
});

