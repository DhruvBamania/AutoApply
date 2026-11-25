// ==========================================================================
// AutoApply AI Companion - Extension Popup Script
// ==========================================================================

document.addEventListener('DOMContentLoaded', async () => {
  const statusDot = document.getElementById('statusDot');
  const statusLabel = document.getElementById('statusLabel');
  const loginView = document.getElementById('loginView');
  const mainView = document.getElementById('mainView');

  let currentJob = null;
  let currentAnalysis = null;
  let cachedProfile = null;
  let configuredApiUrl = 'https://auto-apply-two-phi.vercel.app';

  // --------------------------------------------------------------------------
  // 1. Authentication & Status Check
  // --------------------------------------------------------------------------

  async function checkAuthStatus() {
    statusDot.className = 'status-dot';
    statusLabel.textContent = 'Checking...';

    try {
      const res = await chrome.runtime.sendMessage({ action: 'CHECK_STATUS' });
      if (res && res.apiUrl) {
        configuredApiUrl = res.apiUrl;
      }

      if (res && res.authenticated) {
        showMainView(res.data?.user);
      } else {
        showLoginView();
      }
    } catch (err) {
      showLoginView();
    }
  }

  function showLoginView() {
    statusDot.className = 'status-dot error';
    statusLabel.textContent = 'Disconnected';
    loginView.style.display = 'flex';
    mainView.style.display = 'none';

    const loginApiUrlInput = document.getElementById('loginApiUrl');
    if (loginApiUrlInput) loginApiUrlInput.value = configuredApiUrl;
  }

  function showMainView(user) {
    statusDot.className = 'status-dot online';
    statusLabel.textContent = 'Connected';
    loginView.style.display = 'none';
    mainView.style.display = 'flex';

    if (user) {
      updateUserSettings(user);
    }

    // Inspect active tab for job
    inspectActiveTab();
    // Pre-fetch candidate profile
    loadCandidateProfile();
  }

  function updateUserSettings(user) {
    const avatar = document.getElementById('settingsUserAvatar');
    const name = document.getElementById('settingsUserName');
    const email = document.getElementById('settingsUserEmail');
    const apiUrlInput = document.getElementById('settingsApiUrl');

    if (avatar) avatar.textContent = (user.name || 'U').charAt(0).toUpperCase();
    if (name) name.textContent = user.name || 'Candidate';
    if (email) email.textContent = user.email || '';
    if (apiUrlInput) apiUrlInput.value = configuredApiUrl;
  }

  // --------------------------------------------------------------------------
  // 2. Active Tab Job Detection & ATS Match Analysis
  // --------------------------------------------------------------------------

  async function inspectActiveTab() {
    const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
    if (!tab || !tab.id) return;

    try {
      const response = await chrome.tabs.sendMessage(tab.id, { action: 'GET_PAGE_JOB' });
      if (response && response.success && response.job) {
        currentJob = response.job;
        updateJobBanner(currentJob);
        runMatchAnalysis(currentJob);
      } else {
        setNoJobFoundState();
      }
    } catch (e) {
      // Content script might not be injected or page doesn't allow content scripts
      setNoJobFoundState();
    }
  }

  function updateJobBanner(job) {
    const stripPlatform = document.getElementById('stripPlatform');
    const stripTitle = document.getElementById('stripTitle');
    const stripCompany = document.getElementById('stripCompany');

    stripPlatform.textContent = job.platform || 'Portal';
    stripTitle.textContent = job.title || 'Job Opening';
    stripCompany.textContent = job.company ? `${job.company} • ${job.location || 'Remote'}` : 'Location detected';
  }

  function setNoJobFoundState() {
    const stripPlatform = document.getElementById('stripPlatform');
    const stripTitle = document.getElementById('stripTitle');
    const stripCompany = document.getElementById('stripCompany');

    stripPlatform.textContent = 'Universal';
    stripTitle.textContent = 'Open a job on LinkedIn, Indeed, or Lever';
    stripCompany.textContent = 'Navigate to any job posting to activate real-time ATS match scoring';

    document.getElementById('scoreCircle').textContent = '--';
    document.getElementById('scoreHeadline').textContent = 'No Job Detected on Active Tab';
    document.getElementById('scoreSubtext').textContent = 'Open a job page on LinkedIn, Indeed, Glassdoor, Internshala, or any careers portal.';
    document.getElementById('keywordsList').innerHTML = '<span class="chip-placeholder">No job data on current page.</span>';
    document.getElementById('missingCountBadge').textContent = '0 missing';
  }

  async function runMatchAnalysis(job) {
    const scoreCircle = document.getElementById('scoreCircle');
    const scoreHeadline = document.getElementById('scoreHeadline');
    const scoreSubtext = document.getElementById('scoreSubtext');
    const keywordsList = document.getElementById('keywordsList');
    const missingBadge = document.getElementById('missingCountBadge');

    scoreCircle.textContent = '...';
    scoreHeadline.textContent = 'Analyzing Requirements...';
    scoreSubtext.textContent = 'Comparing job requirements against your latest resume...';

    try {
      const res = await chrome.runtime.sendMessage({
        action: 'ANALYZE_JOB',
        payload: {
          job_title: job.title,
          company: job.company,
          job_description: job.description
        }
      });

      if (res && res.success && res.data) {
        currentAnalysis = res.data;
        const score = currentAnalysis.match_score || 0;

        scoreCircle.textContent = `${score}%`;
        if (score >= 75) {
          scoreCircle.style.background = 'linear-gradient(135deg, #10b981, #059669)';
          scoreHeadline.textContent = 'Strong Match for Your Profile';
        } else if (score >= 50) {
          scoreCircle.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
          scoreHeadline.textContent = 'Moderate Match — Optimization Recommended';
        } else {
          scoreCircle.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
          scoreHeadline.textContent = 'Low Keyword Alignment Detected';
        }

        scoreSubtext.textContent = currentAnalysis.explanation || `ATS match score calculated from your technical skills, experience, and job requirements.`;

        // Render Missing Keywords
        const missing = currentAnalysis.missing_keywords || [];
        missingBadge.textContent = `${missing.length} missing`;
        keywordsList.innerHTML = '';

        if (missing.length === 0) {
          keywordsList.innerHTML = '<span class="chip" style="background:#ecfdf5; color:#059669; border-color:#a7f3d0;">✓ Outstanding keyword coverage!</span>';
        } else {
          missing.forEach(kw => {
            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.textContent = `+ ${kw}`;
            keywordsList.appendChild(chip);
          });
        }

        // Render Recommendation Box if present
        const recBox = document.getElementById('recommendationBox');
        const recText = document.getElementById('recommendationText');
        if (currentAnalysis.recommendations && currentAnalysis.recommendations.length > 0) {
          recBox.style.display = 'flex';
          recText.textContent = currentAnalysis.recommendations.join(' ');
        }
      }
    } catch (err) {
      scoreCircle.textContent = '⚠️';
      scoreHeadline.textContent = 'Analysis Failed';
      scoreSubtext.textContent = err.message || 'Please check your connection and login status.';
    }
  }

  // --------------------------------------------------------------------------
  // 3. Tab Navigation
  // --------------------------------------------------------------------------

  const tabs = document.querySelectorAll('.nav-tab');
  const tabContents = document.querySelectorAll('.tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tabContents.forEach(c => c.classList.remove('active'));

      tab.classList.add('active');
      const targetId = tab.dataset.tab;
      const targetContent = document.getElementById(targetId);
      if (targetContent) targetContent.classList.add('active');
    });
  });

  // --------------------------------------------------------------------------
  // 4. Tab 2: Screening Answer Generator
  // --------------------------------------------------------------------------

  const generateAnswerBtn = document.getElementById('generateAnswerBtn');
  const screeningInput = document.getElementById('screeningQuestionInput');
  const answerToneSelect = document.getElementById('answerToneSelect');
  const answerResultBox = document.getElementById('answerResultBox');
  const answerOutput = document.getElementById('answerOutput');
  const copyAnswerBtn = document.getElementById('copyAnswerBtn');
  const insertAnswerBtn = document.getElementById('insertAnswerBtn');

  generateAnswerBtn.addEventListener('click', async () => {
    const question = screeningInput.value.trim();
    if (!question) {
      screeningInput.focus();
      return;
    }

    generateAnswerBtn.disabled = true;
    generateAnswerBtn.textContent = 'Generating...';

    try {
      const res = await chrome.runtime.sendMessage({
        action: 'GENERATE_ANSWER',
        payload: {
          question,
          job_title: currentJob ? currentJob.title : '',
          job_description: currentJob ? currentJob.description : '',
          tone: answerToneSelect.value
        }
      });

      if (res && res.success && res.data) {
        answerResultBox.style.display = 'flex';
        answerOutput.textContent = res.data.answer;
      } else {
        alert(res?.error || 'Failed to generate answer.');
      }
    } catch (e) {
      alert(e.message || 'Error communicating with AI service.');
    } finally {
      generateAnswerBtn.disabled = false;
      generateAnswerBtn.textContent = 'Generate';
    }
  });

  copyAnswerBtn.addEventListener('click', async () => {
    if (answerOutput.textContent) {
      await navigator.clipboard.writeText(answerOutput.textContent);
      copyAnswerBtn.textContent = '✓ Copied!';
      setTimeout(() => { copyAnswerBtn.textContent = '📋 Copy'; }, 2000);
    }
  });

  insertAnswerBtn.addEventListener('click', async () => {
    const text = answerOutput.textContent;
    if (!text) return;

    const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
    if (!tab || !tab.id) return;

    chrome.scripting.executeScript({
      target: { tabId: tab.id },
      func: (answer) => {
        const active = document.activeElement;
        if (active && (active.tagName === 'TEXTAREA' || active.tagName === 'INPUT')) {
          const proto = active instanceof HTMLTextAreaElement ? window.HTMLTextAreaElement.prototype : window.HTMLInputElement.prototype;
          const setter = Object.getOwnPropertyDescriptor(proto, 'value').set;
          if (setter) setter.call(active, answer);
          else active.value = answer;
          active.dispatchEvent(new Event('input', { bubbles: true }));
          active.dispatchEvent(new Event('change', { bubbles: true }));
          return true;
        }
        return false;
      },
      args: [text]
    }, (results) => {
      if (results && results[0] && results[0].result) {
        insertAnswerBtn.textContent = '✓ Inserted into Field!';
      } else {
        insertAnswerBtn.textContent = '⚠️ Click field on page first';
      }
      setTimeout(() => { insertAnswerBtn.textContent = '⚡ Insert into Active Field'; }, 2500);
    });
  });

  // --------------------------------------------------------------------------
  // 5. Tab 3: Form AutoFill
  // --------------------------------------------------------------------------

  async function loadCandidateProfile() {
    try {
      const res = await chrome.runtime.sendMessage({ action: 'GET_AUTOFILL_PROFILE' });
      if (res && res.success && res.data) {
        cachedProfile = res.data;
        document.getElementById('pvName').textContent = cachedProfile.full_name || '—';
        document.getElementById('pvEmail').textContent = cachedProfile.email || '—';
        document.getElementById('pvPhone').textContent = cachedProfile.phone || '—';
        document.getElementById('pvLinkedin').textContent = cachedProfile.linkedin_url || '—';
        document.getElementById('pvGithub').textContent = cachedProfile.github_url || '—';
        document.getElementById('pvTitle').textContent = cachedProfile.current_title || '—';
      }
    } catch (e) {}
  }

  const runAutofillBtn = document.getElementById('runAutofillBtn');
  runAutofillBtn.addEventListener('click', async () => {
    runAutofillBtn.disabled = true;
    runAutofillBtn.textContent = 'AutoFilling...';

    const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
    if (!tab || !tab.id) {
      runAutofillBtn.disabled = false;
      runAutofillBtn.textContent = 'Fill Application Fields Now';
      return;
    }

    try {
      if (!cachedProfile) {
        await loadCandidateProfile();
      }

      const res = await chrome.tabs.sendMessage(tab.id, {
        action: 'TRIGGER_AUTOFILL',
        payload: { profile: cachedProfile }
      });

      if (res && res.success) {
        runAutofillBtn.textContent = `✓ Filled ${res.filledCount} Fields!`;
      } else {
        runAutofillBtn.textContent = '⚠️ Could not fill fields';
      }
    } catch (err) {
      runAutofillBtn.textContent = '⚠️ Error on page';
    } finally {
      setTimeout(() => {
        runAutofillBtn.disabled = false;
        runAutofillBtn.textContent = 'Fill Application Fields Now';
      }, 3000);
    }
  });

  // --------------------------------------------------------------------------
  // 6. Save Job & Tracker
  // --------------------------------------------------------------------------

  const saveJobBtn = document.getElementById('saveJobBtn');
  saveJobBtn.addEventListener('click', async () => {
    if (!currentJob) {
      alert('No active job found on page.');
      return;
    }

    saveJobBtn.disabled = true;
    saveJobBtn.textContent = 'Saving...';

    try {
      const res = await chrome.runtime.sendMessage({
        action: 'SAVE_JOB',
        payload: {
          job_title: currentJob.title || 'Job Opening',
          company_name: currentJob.company || 'Unknown Company',
          job_url: currentJob.job_url,
          platform: currentJob.platform,
          location: currentJob.location,
          salary: currentJob.salary,
          match_score: currentAnalysis ? currentAnalysis.match_score : null,
          status: 'bookmarked'
        }
      });

      if (res && res.success) {
        saveJobBtn.textContent = '✓ Saved to Tracker!';
      } else {
        saveJobBtn.textContent = '⚠️ Save Failed';
      }
    } catch (e) {
      saveJobBtn.textContent = '⚠️ Save Failed';
    } finally {
      setTimeout(() => {
        saveJobBtn.disabled = false;
        saveJobBtn.textContent = '💾 Save to Tracker';
      }, 2500);
    }
  });

  const refreshAnalysisBtn = document.getElementById('refreshAnalysisBtn');
  refreshAnalysisBtn.addEventListener('click', () => {
    inspectActiveTab();
  });

  // --------------------------------------------------------------------------
  // 7. Login & Settings Handlers
  // --------------------------------------------------------------------------

  const loginForm = document.getElementById('loginForm');
  const loginSubmitBtn = document.getElementById('loginSubmitBtn');
  const loginError = document.getElementById('loginError');

  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    loginError.style.display = 'none';
    loginSubmitBtn.disabled = true;
    loginSubmitBtn.textContent = 'Signing in...';

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const apiUrl = document.getElementById('loginApiUrl').value.trim() || configuredApiUrl;

    try {
      const res = await chrome.runtime.sendMessage({
        action: 'LOGIN',
        payload: { email, password, apiUrl }
      });

      if (res && res.success) {
        configuredApiUrl = apiUrl;
        showMainView(res.user);
      } else {
        loginError.textContent = res?.error || 'Invalid credentials.';
        loginError.style.display = 'block';
      }
    } catch (err) {
      loginError.textContent = err.message || 'Login failed. Check server connection.';
      loginError.style.display = 'block';
    } finally {
      loginSubmitBtn.disabled = false;
      loginSubmitBtn.textContent = 'Sign In & Connect';
    }
  });

  const saveSettingsBtn = document.getElementById('saveSettingsBtn');
  saveSettingsBtn.addEventListener('click', async () => {
    const newUrl = document.getElementById('settingsApiUrl').value.trim();
    if (newUrl) {
      await chrome.storage.local.set({ apiUrl: newUrl });
      configuredApiUrl = newUrl;
      saveSettingsBtn.textContent = '✓ Server URL Saved!';
      setTimeout(() => { saveSettingsBtn.textContent = 'Save Server URL'; }, 2000);
    }
  });

  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', async () => {
    await chrome.runtime.sendMessage({ action: 'LOGOUT' });
    showLoginView();
  });

  // Server Presets
  const presetLiveBtn = document.getElementById('setPresetLive');
  const presetLocalBtn = document.getElementById('setPresetLocal');
  if (presetLiveBtn) {
    presetLiveBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const input = document.getElementById('loginApiUrl');
      if (input) input.value = 'https://auto-apply-two-phi.vercel.app';
    });
  }
  if (presetLocalBtn) {
    presetLocalBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const input = document.getElementById('loginApiUrl');
      if (input) input.value = 'http://127.0.0.1:8000';
    });
  }

  // External Web App Links
  document.getElementById('openWebAppRegister').addEventListener('click', (e) => {
    e.preventDefault();
    chrome.tabs.create({ url: `${configuredApiUrl}/register` });
  });

  document.getElementById('openWebAppTracker').addEventListener('click', (e) => {
    e.preventDefault();
    chrome.tabs.create({ url: `${configuredApiUrl}/applications` });
  });

  document.getElementById('openWebAppDashboard').addEventListener('click', (e) => {
    e.preventDefault();
    chrome.tabs.create({ url: `${configuredApiUrl}/candidate/headhunter` });
  });

  // Initialize
  checkAuthStatus();
});

