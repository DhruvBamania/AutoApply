// ==========================================================================
// AutoApply AI Companion - Multi-Platform Content Script (Manifest V3)
// ==========================================================================

(function () {
  'use strict';

  // Prevent multiple injections
  if (window.__AUTOAPPLY_COMPANION_INITIALIZED__) return;
  window.__AUTOAPPLY_COMPANION_INITIALIZED__ = true;

  let currentScrapedJob = null;
  let cachedAnalysis = null;
  let companionPanelOpen = false;

  // --------------------------------------------------------------------------
  // 1. Multi-Platform Job Scrapers
  // --------------------------------------------------------------------------

  function cleanText(text) {
    if (!text) return '';
    return text.replace(/\s+/g, ' ').trim();
  }

  function detectPlatformAndScrape() {
    const url = window.location.href;
    const hostname = window.location.hostname.toLowerCase();

    let jobData = {
      title: '',
      company: '',
      location: '',
      salary: '',
      description: '',
      platform: 'universal',
      job_url: url
    };

    // 1. LinkedIn
    if (hostname.includes('linkedin.com')) {
      jobData.platform = 'linkedin';
      const titleEl = document.querySelector(
        '.job-details-jobs-unified-top-card__job-title, .jobs-unified-top-card__job-title, h1.t-24, .jobs-details__main-content h1'
      );
      const companyEl = document.querySelector(
        '.job-details-jobs-unified-top-card__company-name, .jobs-unified-top-card__company-name, .jobs-unified-top-card__subtitle-primary-grouping a'
      );
      const locationEl = document.querySelector(
        '.job-details-jobs-unified-top-card__bullet, .jobs-unified-top-card__bullet'
      );
      const descEl = document.querySelector(
        '#job-details, .jobs-description__content, .jobs-box__html-content'
      );

      if (titleEl) jobData.title = cleanText(titleEl.textContent);
      if (companyEl) jobData.company = cleanText(companyEl.textContent);
      if (locationEl) jobData.location = cleanText(locationEl.textContent);
      if (descEl) jobData.description = cleanText(descEl.innerText || descEl.textContent);
    }
    // 2. Indeed
    else if (hostname.includes('indeed.com')) {
      jobData.platform = 'indeed';
      const titleEl = document.querySelector('h1.jobsearch-JobInfoHeader-title, [data-testid="jobsearch-JobInfoHeader-title"], h1');
      const companyEl = document.querySelector('[data-testid="inlineHeader-companyName"], [data-company-name="true"]');
      const locationEl = document.querySelector('[data-testid="inlineHeader-companyLocation"], [data-testid="jobsearch-JobInfoHeader-companyLocation"]');
      const descEl = document.querySelector('#jobDescriptionText, .jobsearch-jobDescriptionText');

      if (titleEl) jobData.title = cleanText(titleEl.textContent);
      if (companyEl) jobData.company = cleanText(companyEl.textContent);
      if (locationEl) jobData.location = cleanText(locationEl.textContent);
      if (descEl) jobData.description = cleanText(descEl.innerText || descEl.textContent);
    }
    // 3. Glassdoor
    else if (hostname.includes('glassdoor.com')) {
      jobData.platform = 'glassdoor';
      const titleEl = document.querySelector('[data-test="job-title"], h1');
      const companyEl = document.querySelector('[data-test="employer-name"], .EmployerProfile_employerName__');
      const locationEl = document.querySelector('[data-test="location"]');
      const descEl = document.querySelector('.jobDescriptionContent, [data-test="job-description"]');

      if (titleEl) jobData.title = cleanText(titleEl.textContent);
      if (companyEl) jobData.company = cleanText(companyEl.textContent);
      if (locationEl) jobData.location = cleanText(locationEl.textContent);
      if (descEl) jobData.description = cleanText(descEl.innerText || descEl.textContent);
    }
    // 4. Internshala
    else if (hostname.includes('internshala.com')) {
      jobData.platform = 'internshala';
      const titleEl = document.querySelector('.heading_4_5.profile, .profile_on_detail_page');
      const companyEl = document.querySelector('.company_name, .link_display_like_text');
      const locationEl = document.querySelector('#location_names a, .location_link');
      const descEl = document.querySelector('.internship_details, .text-container');

      if (titleEl) jobData.title = cleanText(titleEl.textContent);
      if (companyEl) jobData.company = cleanText(companyEl.textContent);
      if (locationEl) jobData.location = cleanText(locationEl.textContent);
      if (descEl) jobData.description = cleanText(descEl.innerText || descEl.textContent);
    }
    // 5. Lever.co
    else if (hostname.includes('lever.co')) {
      jobData.platform = 'lever';
      const titleEl = document.querySelector('.posting-headline h2');
      const companyEl = document.querySelector('.main-header-logo img') || document.querySelector('.posting-headline');
      const locationEl = document.querySelector('.posting-categories .location');
      const descEl = document.querySelector('.section-page, .content');

      if (titleEl) jobData.title = cleanText(titleEl.textContent);
      if (companyEl) jobData.company = companyEl.alt || cleanText(document.title.split('-')[0]);
      if (locationEl) jobData.location = cleanText(locationEl.textContent);
      if (descEl) jobData.description = cleanText(descEl.innerText || descEl.textContent);
    }
    // 6. Greenhouse.io
    else if (hostname.includes('greenhouse.io')) {
      jobData.platform = 'greenhouse';
      const titleEl = document.querySelector('.app-title, #header h1');
      const companyEl = document.querySelector('.company-name');
      const locationEl = document.querySelector('.location');
      const descEl = document.querySelector('#content, #main');

      if (titleEl) jobData.title = cleanText(titleEl.textContent);
      if (companyEl) jobData.company = cleanText(companyEl.textContent);
      if (locationEl) jobData.location = cleanText(locationEl.textContent);
      if (descEl) jobData.description = cleanText(descEl.innerText || descEl.textContent);
    }
    // 7. Workday
    else if (hostname.includes('workday.com') || hostname.includes('myworkdayjobs.com')) {
      jobData.platform = 'workday';
      const titleEl = document.querySelector('[data-automation-id="jobPostingHeader"], h2');
      const locationEl = document.querySelector('[data-automation-id="locations"]');
      const descEl = document.querySelector('[data-automation-id="jobPostingDescription"]');

      if (titleEl) jobData.title = cleanText(titleEl.textContent);
      jobData.company = cleanText(document.title.split('-')[0]);
      if (locationEl) jobData.location = cleanText(locationEl.textContent);
      if (descEl) jobData.description = cleanText(descEl.innerText || descEl.textContent);
    }
    // 8. Universal JSON-LD Fallback
    else {
      const scripts = document.querySelectorAll('script[type="application/ld+json"]');
      for (const script of scripts) {
        try {
          const json = JSON.parse(script.textContent);
          const item = Array.isArray(json) ? json.find(j => j['@type'] === 'JobPosting') : (json['@type'] === 'JobPosting' ? json : null);
          if (item) {
            jobData.title = cleanText(item.title || '');
            jobData.company = cleanText(item.hiringOrganization?.name || '');
            jobData.location = cleanText(item.jobLocation?.address?.addressLocality || '');
            jobData.description = cleanText(item.description || '');
            break;
          }
        } catch (e) {}
      }

      // DOM fallback if no JSON-LD
      if (!jobData.title) {
        const h1 = document.querySelector('h1');
        if (h1) jobData.title = cleanText(h1.textContent);
        jobData.company = cleanText(document.title.split(/[|\-–]/)[0] || '');
        const main = document.querySelector('main, article, #content, .content, [role="main"]');
        if (main) jobData.description = cleanText(main.innerText || main.textContent).substring(0, 3000);
      }
    }

    // Only return job if title or description found
    if (jobData.title || (jobData.description && jobData.description.length > 80)) {
      currentScrapedJob = jobData;
      return jobData;
    }

    return null;
  }

  // --------------------------------------------------------------------------
  // 2. In-Page Floating Companion UI
  // --------------------------------------------------------------------------

  function injectCompanionUI(jobData) {
    if (document.getElementById('autoapply-floating-companion')) return;

    const container = document.createElement('div');
    container.id = 'autoapply-floating-companion';

    container.innerHTML = `
      <!-- Trigger Badge -->
      <div class="autoapply-badge-trigger" id="autoapply-badge-btn" title="Open AutoApply AI Companion">
        <div class="autoapply-badge-icon">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
        </div>
        <span>AutoApply AI</span>
        <span class="autoapply-badge-pulse"></span>
      </div>

      <!-- Slide-Up Companion Panel -->
      <div class="autoapply-companion-panel" id="autoapply-companion-drawer">
        <div class="autoapply-panel-header">
          <div class="autoapply-panel-brand">
            <span>AutoApply</span> AI Companion
          </div>
          <button type="button" class="autoapply-panel-close" id="autoapply-drawer-close">&times;</button>
        </div>

        <div class="autoapply-job-box">
          <div class="autoapply-job-title" id="autoapply-display-title">
            ${jobData ? jobData.title : 'Application Page Detected'}
          </div>
          <div class="autoapply-job-company">
            <span class="autoapply-portal-tag">${jobData ? jobData.platform : 'portal'}</span>
            <span id="autoapply-display-company">${jobData && jobData.company ? jobData.company : 'Careers Portal'}</span>
          </div>
        </div>

        <div class="autoapply-score-section" id="autoapply-score-box">
          <div class="autoapply-score-header">
            <span>ATS Match Score</span>
            <span class="autoapply-score-badge autoapply-score-mid" id="autoapply-score-text">Analyzing...</span>
          </div>
          <div class="autoapply-progress-track">
            <div class="autoapply-progress-fill" id="autoapply-progress-bar" style="width: 50%;"></div>
          </div>
        </div>

        <div class="autoapply-keywords-box" id="autoapply-keywords-box">
          <div class="autoapply-keywords-label">Missing Keywords to Add</div>
          <div class="autoapply-keywords-chips" id="autoapply-chips-container">
            <span class="autoapply-chip">Loading keywords...</span>
          </div>
        </div>

        <div class="autoapply-actions-row">
          <button type="button" class="autoapply-btn autoapply-btn-primary" id="autoapply-autofill-btn">
            ⚡ 1-Click AutoFill
          </button>
          <button type="button" class="autoapply-btn autoapply-btn-secondary" id="autoapply-save-job-btn">
            💾 Save Application
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(container);

    // Bind Event Listeners
    const triggerBtn = document.getElementById('autoapply-badge-btn');
    const drawer = document.getElementById('autoapply-companion-drawer');
    const closeBtn = document.getElementById('autoapply-drawer-close');
    const autofillBtn = document.getElementById('autoapply-autofill-btn');
    const saveJobBtn = document.getElementById('autoapply-save-job-btn');

    triggerBtn.addEventListener('click', () => {
      companionPanelOpen = !companionPanelOpen;
      drawer.classList.toggle('open', companionPanelOpen);
      if (companionPanelOpen && !cachedAnalysis && jobData) {
        requestJobAnalysis(jobData);
      }
    });

    closeBtn.addEventListener('click', () => {
      companionPanelOpen = false;
      drawer.classList.remove('open');
    });

    autofillBtn.addEventListener('click', async () => {
      autofillBtn.textContent = '⏳ AutoFilling...';
      try {
        const response = await chrome.runtime.sendMessage({ action: 'GET_AUTOFILL_PROFILE' });
        if (response && response.success && response.data) {
          const count = executeAutofill(response.data);
          autofillBtn.textContent = `✓ Filled ${count} Fields!`;
          setTimeout(() => { autofillBtn.textContent = '⚡ 1-Click AutoFill'; }, 2500);
        } else {
          autofillBtn.textContent = '⚠️ Log In in Popup';
          setTimeout(() => { autofillBtn.textContent = '⚡ 1-Click AutoFill'; }, 2500);
        }
      } catch (err) {
        autofillBtn.textContent = '⚠️ Log In in Popup';
        setTimeout(() => { autofillBtn.textContent = '⚡ 1-Click AutoFill'; }, 2500);
      }
    });

    saveJobBtn.addEventListener('click', async () => {
      if (!currentScrapedJob) return;
      saveJobBtn.textContent = '💾 Saving...';
      try {
        const res = await chrome.runtime.sendMessage({
          action: 'SAVE_JOB',
          payload: {
            job_title: currentScrapedJob.title || 'Job Opening',
            company_name: currentScrapedJob.company || 'Unknown Company',
            job_url: currentScrapedJob.job_url,
            platform: currentScrapedJob.platform,
            location: currentScrapedJob.location,
            salary: currentScrapedJob.salary,
            match_score: cachedAnalysis ? cachedAnalysis.match_score : null,
            status: 'bookmarked'
          }
        });
        if (res && res.success) {
          saveJobBtn.textContent = '✓ Saved to Tracker!';
        } else {
          saveJobBtn.textContent = '⚠️ Log In in Popup';
        }
      } catch (e) {
        saveJobBtn.textContent = '⚠️ Log In in Popup';
      }
      setTimeout(() => { saveJobBtn.textContent = '💾 Save Application'; }, 2500);
    });
  }

  // Request backend match analysis for in-page drawer
  async function requestJobAnalysis(job) {
    try {
      const response = await chrome.runtime.sendMessage({
        action: 'ANALYZE_JOB',
        payload: {
          job_title: job.title,
          company: job.company,
          job_description: job.description
        }
      });

      if (response && response.success && response.data) {
        cachedAnalysis = response.data;
        updateDrawerAnalysis(cachedAnalysis);
      }
    } catch (e) {
      // User might not be logged in yet
      const scoreText = document.getElementById('autoapply-score-text');
      if (scoreText) scoreText.textContent = 'Log In in Popup';
    }
  }

  function updateDrawerAnalysis(analysis) {
    const scoreText = document.getElementById('autoapply-score-text');
    const progressBar = document.getElementById('autoapply-progress-bar');
    const chipsContainer = document.getElementById('autoapply-chips-container');

    if (scoreText && progressBar) {
      const score = analysis.match_score || 0;
      scoreText.textContent = `${score}% Match`;
      progressBar.style.width = `${score}%`;

      scoreText.className = 'autoapply-score-badge';
      if (score >= 75) scoreText.classList.add('autoapply-score-high');
      else if (score >= 50) scoreText.classList.add('autoapply-score-mid');
      else scoreText.classList.add('autoapply-score-low');
    }

    if (chipsContainer && analysis.missing_keywords) {
      chipsContainer.innerHTML = '';
      const missing = analysis.missing_keywords.slice(0, 8);
      if (missing.length === 0) {
        chipsContainer.innerHTML = '<span class="autoapply-chip" style="color: #10b981;">✓ Excellent Keyword Alignment!</span>';
      } else {
        missing.forEach(kw => {
          const chip = document.createElement('span');
          chip.className = 'autoapply-chip autoapply-chip-missing';
          chip.textContent = `+ ${kw}`;
          chipsContainer.appendChild(chip);
        });
      }
    }
  }

  // --------------------------------------------------------------------------
  // 3. Robust Form AutoFill Engine
  // --------------------------------------------------------------------------

  function setInputValue(element, value) {
    if (!element || value === undefined || value === null) return false;

    // React / Vue / Angular native prototype value setter
    const prototype = element instanceof HTMLTextAreaElement ? window.HTMLTextAreaElement.prototype : window.HTMLInputElement.prototype;
    const descriptor = Object.getOwnPropertyDescriptor(prototype, 'value');

    if (descriptor && descriptor.set) {
      descriptor.set.call(element, value);
    } else {
      element.value = value;
    }

    // Dispatch synthetic events so reactive frameworks capture the change
    element.dispatchEvent(new Event('input', { bubbles: true }));
    element.dispatchEvent(new Event('change', { bubbles: true }));

    // Soft visual feedback
    element.classList.add('autoapply-field-autofilled');
    setTimeout(() => {
      element.classList.remove('autoapply-field-autofilled');
    }, 3000);

    return true;
  }

  function executeAutofill(profile) {
    let filledCount = 0;
    const inputs = document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea');

    inputs.forEach(input => {
      // Gather identifiers
      const id = (input.id || '').toLowerCase();
      const name = (input.name || '').toLowerCase();
      const placeholder = (input.placeholder || '').toLowerCase();
      const aria = (input.getAttribute('aria-label') || '').toLowerCase();
      const autocomplete = (input.getAttribute('autocomplete') || '').toLowerCase();

      // Nearby label text
      let labelText = '';
      if (input.id) {
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label) labelText = label.textContent.toLowerCase();
      }
      if (!labelText && input.closest('label')) {
        labelText = input.closest('label').textContent.toLowerCase();
      }

      const combined = `${id} ${name} ${placeholder} ${aria} ${autocomplete} ${labelText}`;

      // Matching rules
      if (/first.*name|fname|given.*name/.test(combined)) {
        if (setInputValue(input, profile.first_name || profile.full_name?.split(' ')[0])) filledCount++;
      } else if (/last.*name|lname|family.*name|surname/.test(combined)) {
        if (setInputValue(input, profile.last_name || profile.full_name?.split(' ').slice(1).join(' '))) filledCount++;
      } else if (/full.*name|applicant.*name|candidate.*name|^name$/.test(combined)) {
        if (setInputValue(input, profile.full_name || `${profile.first_name || ''} ${profile.last_name || ''}`.trim())) filledCount++;
      } else if (/e-?mail/.test(combined)) {
        if (setInputValue(input, profile.email)) filledCount++;
      } else if (/phone|mobile|tel|cell|contact.*number/.test(combined)) {
        if (setInputValue(input, profile.phone)) filledCount++;
      } else if (/linkedin/.test(combined)) {
        if (setInputValue(input, profile.linkedin_url)) filledCount++;
      } else if (/github/.test(combined)) {
        if (setInputValue(input, profile.github_url)) filledCount++;
      } else if (/portfolio|website|personal.*site|blog/.test(combined)) {
        if (setInputValue(input, profile.portfolio_url || profile.website)) filledCount++;
      } else if (/city|location|address-level2/.test(combined)) {
        if (setInputValue(input, profile.city || profile.location)) filledCount++;
      } else if (/state|province|region/.test(combined)) {
        if (setInputValue(input, profile.state)) filledCount++;
      } else if (/current.*company|current.*employer|organization/.test(combined)) {
        if (setInputValue(input, profile.current_company)) filledCount++;
      } else if (/current.*title|job.*title|designation|headline/.test(combined)) {
        if (setInputValue(input, profile.current_title)) filledCount++;
      } else if (/salary|compensation|desired.*salary|expected.*salary/.test(combined)) {
        if (setInputValue(input, profile.salary_expectation)) filledCount++;
      } else if (/experience.*years|years.*of.*experience|yoe/.test(combined)) {
        if (setInputValue(input, profile.years_of_experience)) filledCount++;
      }
    });

    return filledCount;
  }

  // --------------------------------------------------------------------------
  // 4. Inline Screening Question Helpers
  // --------------------------------------------------------------------------

  function injectScreeningQuestionHelpers() {
    const textareas = document.querySelectorAll('textarea');

    textareas.forEach(textarea => {
      if (textarea.dataset.autoapplyEnhanced) return;
      textarea.dataset.autoapplyEnhanced = 'true';

      // Detect question label
      let questionText = '';
      if (textarea.id) {
        const label = document.querySelector(`label[for="${textarea.id}"]`);
        if (label) questionText = label.textContent.trim();
      }
      if (!questionText && textarea.closest('label')) {
        questionText = textarea.closest('label').textContent.trim();
      }
      if (!questionText && textarea.placeholder) {
        questionText = textarea.placeholder.trim();
      }

      // Check if it sounds like a screening question
      const questionKeywords = /why|describe|experience|tell us|explain|project|strength|challenge|salary|relocate|cover.*letter/i;
      if (questionKeywords.test(questionText) || questionText.length > 20) {
        const assistantBtn = document.createElement('button');
        assistantBtn.type = 'button';
        assistantBtn.className = 'autoapply-inline-assistant-btn';
        assistantBtn.innerHTML = '✨ AutoApply AI Answer';
        assistantBtn.title = 'Generate a tailored screening answer using your resume and this job';

        assistantBtn.addEventListener('click', async (e) => {
          e.preventDefault();
          e.stopPropagation();
          assistantBtn.textContent = '⏳ Thinking...';

          try {
            const res = await chrome.runtime.sendMessage({
              action: 'GENERATE_ANSWER',
              payload: {
                question: questionText,
                job_title: currentScrapedJob ? currentScrapedJob.title : '',
                job_description: currentScrapedJob ? currentScrapedJob.description : ''
              }
            });

            if (res && res.success && res.data && res.data.answer) {
              setInputValue(textarea, res.data.answer);
              assistantBtn.textContent = '✓ Answer Inserted!';
              setTimeout(() => { assistantBtn.textContent = '✨ AutoApply AI Answer'; }, 3000);
            } else {
              assistantBtn.textContent = '⚠️ Log In in Popup';
              setTimeout(() => { assistantBtn.textContent = '✨ AutoApply AI Answer'; }, 3000);
            }
          } catch (err) {
            assistantBtn.textContent = '⚠️ Log In in Popup';
            setTimeout(() => { assistantBtn.textContent = '✨ AutoApply AI Answer'; }, 3000);
          }
        });

        textarea.parentNode.insertBefore(assistantBtn, textarea);
      }
    });
  }

  // --------------------------------------------------------------------------
  // 5. Message Listeners & Initialization
  // --------------------------------------------------------------------------

  chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    switch (request.action) {
      case 'GET_PAGE_JOB': {
        const job = detectPlatformAndScrape();
        sendResponse({ success: true, job });
        break;
      }

      case 'TRIGGER_AUTOFILL': {
        const profile = request.payload?.profile || {};
        const count = executeAutofill(profile);
        sendResponse({ success: true, filledCount: count });
        break;
      }

      case 'TOGGLE_COMPANION': {
        const drawer = document.getElementById('autoapply-companion-drawer');
        if (drawer) {
          companionPanelOpen = !companionPanelOpen;
          drawer.classList.toggle('open', companionPanelOpen);
        }
        sendResponse({ success: true, open: companionPanelOpen });
        break;
      }
    }
    return true;
  });

  // Run on page load and observe DOM changes (for SPAs like LinkedIn / Indeed)
  function init() {
    const job = detectPlatformAndScrape();
    if (job) {
      injectCompanionUI(job);
      injectScreeningQuestionHelpers();
    }
  }

  // Run immediately and check periodically on SPA route changes
  init();
  setInterval(() => {
    const job = detectPlatformAndScrape();
    if (job && !document.getElementById('autoapply-floating-companion')) {
      injectCompanionUI(job);
    }
    injectScreeningQuestionHelpers();
  }, 2500);

})();

