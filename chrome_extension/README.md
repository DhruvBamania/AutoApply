# AutoApply AI Chrome Companion (Manifest V3)

> **Autonomous multi-platform AI assistant for real-time ATS match scoring, screening question generation, and 1-click form autofill across LinkedIn, Indeed, Glassdoor, Internshala, Lever, Greenhouse, Workday & company career portals.**

---

## 🌟 Key Features

1. **Multi-Platform Job Scraping & Detection**
   - Automatically detects active job postings and parsed fields (Title, Company, Location, Salary, JD text) on:
     - LinkedIn (`linkedin.com/jobs/*`)
     - Indeed (`indeed.com/viewjob*`)
     - Glassdoor (`glassdoor.com/job-listing/*`)
     - Internshala (`internshala.com/*`)
     - Lever.co (`jobs.lever.co/*`)
     - Greenhouse (`boards.greenhouse.io/*`)
     - Workday (`*.myworkdayjobs.com/*`)
     - Universal Fallback: Schema.org `JobPosting` JSON-LD & DOM heuristic parser.

2. **Real-Time ATS Match Scoring**
   - Autonomous AI (Gemini 1.5 Pro / Groq LLaMA 3.3) analyzes your latest candidate resume against the live JD.
   - Highlights critical missing keywords, technical skills, and recommendations.

3. **1-Click Portal Form AutoFill Engine**
   - Populates common application fields (First/Last/Full Name, Email, Phone, LinkedIn, GitHub, Portfolio URL, Current Company, Current Title, Years of Experience, Location, Desired Salary).
   - Injects native prototype property setters and dispatches synthetic `input` and `change` events for seamless compatibility with modern reactive frameworks (React, Vue, Angular on Lever/Workday).

4. **AI Screening Question Answer Assistant**
   - Injects inline ✨ **AutoApply AI Answer** buttons next to `<textarea>` screening questions.
   - Generates tailored, metrics-driven responses aligned with your profile and the specific JD.

5. **Centralized Application Tracker Sync**
   - 1-Click bookmarking of external applications into the candidate dashboard (`/applications`).
   - Track recruitment stages from *Bookmarked* to *Applied*, *Interviewing*, *Offered*, and *Rejected*.

---

## 📂 Directory Structure

```
chrome_extension/
├── manifest.json              # Chrome Manifest V3 configuration
├── background/
│   └── background.js          # Service worker: Auth, API requests & storage proxy
├── content/
│   ├── content.js             # In-page portal scrapers, autofill engine & floating companion
│   └── content.css            # Scoped CSS for floating trigger badge & companion drawer
├── popup/
│   ├── popup.html             # Tabbed popup interface (Match, AI Answer, AutoFill, Settings)
│   ├── popup.css              # Glassmorphic AutoApply UI design
│   └── popup.js               # Popup controller, auth flow & active tab inspector
├── icons/
│   ├── icon16.png
│   ├── icon48.png
│   └── icon128.png
└── README.md
```

---

## 🚀 How to Install in Developer Mode

### For Google Chrome, Microsoft Edge, or Brave:

1. **Get the Extension Files:**
   - Download the `.zip` from the web app (`/chrome-extension/download`), or use this folder directly.
   - If downloaded as a `.zip`, unzip `autoapply-ai-companion.zip` to a local folder.
2. **Open Extensions Manager:**
   - Navigate to `chrome://extensions` in your address bar (or `edge://extensions` on Microsoft Edge).
3. **Enable Developer Mode:**
   - Toggle the **Developer mode** switch in the top-right corner to **ON**.
4. **Load Unpacked:**
   - Click the **"Load unpacked"** button in the top-left toolbar.
   - Select the `chrome_extension` folder.
5. **Pin & Sign In:**
   - Click the extensions puzzle icon in your browser toolbar and pin **AutoApply AI Companion**.
   - Click the pinned icon and log in with your AutoApply candidate email & password.

---

## 🔌 Backend REST APIs (`routes/api.php`)

All extension endpoints operate under `/v1/candidate/extension/*` using Laravel Sanctum Bearer authentication:

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/v1/candidate/extension/status` | Verifies Sanctum token & returns candidate profile summary |
| `POST` | `/v1/candidate/extension/analyze` | AI ATS match scoring & missing keyword detection |
| `POST` | `/v1/candidate/extension/generate-answer` | Generates tailored screening question responses |
| `GET` | `/v1/candidate/extension/autofill-profile` | Returns formatted candidate data for form autofilling |
| `POST` | `/v1/candidate/extension/save-job` | Saves external job to candidate application tracker |
| `GET` | `/v1/candidate/extension/applications` | Lists all saved applications with filtering |
| `PATCH` | `/v1/candidate/extension/applications/{id}` | Updates status (`bookmarked`, `applied`, etc.) |
| `DELETE` | `/v1/candidate/extension/applications/{id}` | Removes tracked application |

---

## 🔒 Security & Privacy

- Operates strictly on job portals and application forms you visit.
- Stores authentication credentials locally in `chrome.storage.local`.
- Never stores or shares candidate passwords; requests use secure Sanctum Bearer tokens.
- No third-party ad trackers or analytics scripts are bundled.

