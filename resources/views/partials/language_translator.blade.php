{{-- Universal Google Translate Integration --}}
<div id="google_translate_element" style="position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; opacity: 0; pointer-events: none;" class="notranslate" aria-hidden="true"></div>

<style>
    /* ==========================================================================
       GOOGLE TRANSLATE SEAMLESS INTEGRATION & CLEAN OVERRIDES
       ========================================================================== */
    /* Hide Google Translate top banner bar completely so it never pushes page layout */
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame,
    .goog-te-banner-frame,
    .skiptranslate iframe {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    
    body {
        top: 0px !important;
        position: static !important;
    }

    /* Hide Google default gadgets, badges, and tooltips */
    #goog-gt-tt, 
    .goog-tooltip, 
    .goog-tooltip:hover,
    .goog-te-gadget-simple {
        display: none !important;
        visibility: hidden !important;
    }
    .goog-te-gadget {
        font-size: 0px !important;
        color: transparent !important;
    }
    .goog-text-highlight {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    /* ==========================================================================
       DESKTOP LANGUAGE SELECTOR DROPDOWN STYLES
       ========================================================================== */
    .lang-dropdown-wrapper {
        position: relative;
        display: inline-block;
    }

    .lang-picker-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(203, 213, 225, 0.9);
        border-radius: 12px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        text-decoration: none;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        user-select: none;
    }
    .lang-picker-btn:hover {
        background: #ffffff;
        border-color: #6366f1;
        color: #4f46e5;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.16);
        transform: translateY(-1px);
    }
    .lang-dropdown-wrapper.open .lang-picker-btn {
        background: #ffffff;
        border-color: #4f46e5;
        color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .lang-globe-icon {
        font-size: 1rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .lang-current-label {
        max-width: 95px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .lang-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 320px;
        max-width: 92vw;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.18), 0 8px 18px -4px rgba(79, 70, 229, 0.08);
        padding: 0.85rem;
        z-index: 1060;
        animation: langDropdownFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        flex-direction: column;
        box-sizing: border-box;
    }
    .lang-dropdown-wrapper.open .lang-dropdown-menu,
    .header-dropdown.open .lang-dropdown-menu {
        display: flex !important;
    }
    @keyframes langDropdownFade {
        from { opacity: 0; transform: translateY(-8px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Menu Header */
    .lang-menu-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 0.6rem;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .lang-menu-title {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
    }
    .lang-reset-btn {
        background: none;
        border: none;
        padding: 0.25rem 0.55rem;
        border-radius: 8px;
        font-size: 0.74rem;
        font-weight: 600;
        color: #6366f1;
        cursor: pointer;
        transition: background 0.15s, color 0.15s;
    }
    .lang-reset-btn:hover {
        background: #eef2ff;
        color: #4338ca;
    }

    /* Search Input */
    .lang-search-wrapper {
        position: relative;
        margin-bottom: 0.6rem;
    }
    .lang-search-input {
        width: 100%;
        padding: 0.45rem 0.75rem 0.45rem 2rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.82rem;
        color: #1e293b;
        outline: none;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }
    .lang-search-input:focus {
        background: #ffffff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .lang-search-icon {
        position: absolute;
        left: 0.65rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.85rem;
        color: #94a3b8;
        pointer-events: none;
    }

    /* Quick Chips */
    .lang-quick-section {
        margin-bottom: 0.6rem;
    }
    .lang-quick-title {
        font-size: 0.68rem;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
    }
    .lang-quick-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .lang-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.55rem;
        border-radius: 8px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        font-size: 0.74rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .lang-chip:hover {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4338ca;
        transform: translateY(-1px);
    }
    .lang-chip.active {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
        font-weight: 600;
    }

    /* Scrollable Language List */
    .lang-list-container {
        max-height: 220px;
        overflow-y: auto;
        overscroll-behavior: contain;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        padding-right: 0.25rem;
    }
    .lang-list-container::-webkit-scrollbar {
        width: 5px;
    }
    .lang-list-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .lang-list-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .lang-item-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0.45rem 0.65rem;
        border-radius: 10px;
        background: none;
        border: none;
        cursor: pointer;
        text-align: left;
        color: #1e293b;
        transition: background 0.15s ease, transform 0.15s ease;
        font-family: inherit;
        user-select: none;
    }
    .lang-item-btn:hover {
        background: #f1f5f9;
        transform: translateX(2px);
    }
    .lang-item-btn.active {
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
        font-weight: 600;
    }
    .lang-item-left {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .lang-item-flag {
        font-size: 1.05rem;
        line-height: 1;
    }
    .lang-item-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    .lang-item-native {
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f172a;
    }
    .lang-item-btn.active .lang-item-native {
        color: #4338ca;
    }
    .lang-item-english {
        font-size: 0.7rem;
        color: #64748b;
    }
    .lang-item-check {
        color: #4f46e5;
        font-weight: 700;
        font-size: 0.9rem;
        display: none;
    }
    .lang-item-btn.active .lang-item-check {
        display: inline-block;
    }

    /* Mobile Drawer Language Section */
    .mobile-lang-box {
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.75rem;
        margin: 0.4rem 0.25rem 1rem 0.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .mobile-lang-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .mobile-lang-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1e293b;
    }
    .mobile-lang-select {
        width: 100%;
        padding: 0.55rem 0.75rem;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.86rem;
        font-weight: 500;
        color: #0f172a;
        outline: none;
        cursor: pointer;
    }
    .mobile-lang-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    @media (max-width: 860px) {
        .lang-current-label {
            display: none;
        }
        .lang-picker-btn {
            padding: 0.45rem 0.6rem;
        }
    }
</style>

<script type="text/javascript">
    // Comprehensive Global Language Catalog (ISO 639-1)
    const UNIVERSAL_LANGUAGES = [
        { code: 'en', name: 'English', native: 'English', flag: '🇺🇸', popular: true },
        { code: 'hi', name: 'Hindi', native: 'हिन्दी', flag: '🇮🇳', popular: true },
        { code: 'gu', name: 'Gujarati', native: 'ગુજરાતી', flag: '🇮🇳', popular: true },
        { code: 'mr', name: 'Marathi', native: 'मराठी', flag: '🇮🇳', popular: true },
        { code: 'bn', name: 'Bengali', native: 'বাংলা', flag: '🇮🇳', popular: true },
        { code: 'ta', name: 'Tamil', native: 'தமிழ்', flag: '🇮🇳', popular: true },
        { code: 'te', name: 'Telugu', native: 'తెలుగు', flag: '🇮🇳', popular: true },
        { code: 'kn', name: 'Kannada', native: 'ಕನ್ನಡ', flag: '🇮🇳', popular: false },
        { code: 'ml', name: 'Malayalam', native: 'മലയാളം', flag: '🇮🇳', popular: false },
        { code: 'pa', name: 'Punjabi', native: 'ਪੰਜਾਬੀ', flag: '🇮🇳', popular: false },
        { code: 'ur', name: 'Urdu', native: 'اردو', flag: '🇵🇰', popular: true },
        { code: 'es', name: 'Spanish', native: 'Español', flag: '🇪🇸', popular: true },
        { code: 'fr', name: 'French', native: 'Français', flag: '🇫🇷', popular: true },
        { code: 'de', name: 'German', native: 'Deutsch', flag: '🇩🇪', popular: true },
        { code: 'zh-CN', name: 'Chinese (Simplified)', native: '简体中文', flag: '🇨🇳', popular: true },
        { code: 'zh-TW', name: 'Chinese (Traditional)', native: '繁體中文', flag: '🇹🇼', popular: false },
        { code: 'ja', name: 'Japanese', native: '日本語', flag: '🇯🇵', popular: true },
        { code: 'ko', name: 'Korean', native: '한국어', flag: '🇰🇷', popular: true },
        { code: 'ar', name: 'Arabic', native: 'العربية', flag: '🇸🇦', popular: true },
        { code: 'pt', name: 'Portuguese', native: 'Português', flag: '🇧🇷', popular: true },
        { code: 'ru', name: 'Russian', native: 'Русский', flag: '🇷🇺', popular: true },
        { code: 'it', name: 'Italian', native: 'Italiano', flag: '🇮🇹', popular: false },
        { code: 'nl', name: 'Dutch', native: 'Nederlands', flag: '🇳🇱', popular: false },
        { code: 'tr', name: 'Turkish', native: 'Türkçe', flag: '🇹🇷', popular: false },
        { code: 'id', name: 'Indonesian', native: 'Bahasa Indonesia', flag: '🇮🇩', popular: false },
        { code: 'vi', name: 'Vietnamese', native: 'Tiếng Việt', flag: '🇻🇳', popular: false },
        { code: 'pl', name: 'Polish', native: 'Polski', flag: '🇵🇱', popular: false },
        { code: 'uk', name: 'Ukrainian', native: 'Українська', flag: '🇺🇦', popular: false },
        { code: 'th', name: 'Thai', native: 'ไทย', flag: '🇹🇭', popular: false },
        { code: 'sv', name: 'Swedish', native: 'Svenska', flag: '🇸🇪', popular: false },
        { code: 'el', name: 'Greek', native: 'Ελληνικά', flag: '🇬🇷', popular: false },
        { code: 'he', name: 'Hebrew', native: 'עברית', flag: '🇮🇱', popular: false },
        { code: 'fa', name: 'Persian', native: 'فارسی', flag: '🇮🇷', popular: false },
        { code: 'ro', name: 'Romanian', native: 'Română', flag: '🇷🇴', popular: false },
        { code: 'cs', name: 'Czech', native: 'Čeština', flag: '🇨🇿', popular: false },
        { code: 'hu', name: 'Hungarian', native: 'Magyar', flag: '🇭🇺', popular: false },
        { code: 'da', name: 'Danish', native: 'Dansk', flag: '🇩🇰', popular: false },
        { code: 'fi', name: 'Finnish', native: 'Suomi', flag: '🇫🇮', popular: false },
        { code: 'no', name: 'Norwegian', native: 'Norsk', flag: '🇳🇴', popular: false },
        { code: 'ms', name: 'Malay', native: 'Bahasa Melayu', flag: '🇲🇾', popular: false },
        { code: 'tl', name: 'Filipino', native: 'Tagalog', flag: '🇵🇭', popular: false },
        { code: 'sw', name: 'Swahili', native: 'Kiswahili', flag: '🇰🇪', popular: false },
        { code: 'af', name: 'Afrikaans', native: 'Afrikaans', flag: '🇿🇦', popular: false },
        { code: 'sq', name: 'Albanian', native: 'Shqip', flag: '🇦🇱', popular: false },
        { code: 'am', name: 'Amharic', native: 'አማርኛ', flag: '🇪🇹', popular: false },
        { code: 'hy', name: 'Armenian', native: 'Հայերեն', flag: '🇦🇲', popular: false },
        { code: 'az', name: 'Azerbaijani', native: 'Azərbaycan', flag: '🇦🇿', popular: false },
        { code: 'eu', name: 'Basque', native: 'Euskara', flag: '🇪🇸', popular: false },
        { code: 'be', name: 'Belarusian', native: 'Беларуская', flag: '🇧🇾', popular: false },
        { code: 'bs', name: 'Bosnian', native: 'Bosanski', flag: '🇧🇦', popular: false },
        { code: 'bg', name: 'Bulgarian', native: 'Български', flag: '🇧🇬', popular: false },
        { code: 'ca', name: 'Catalan', native: 'Català', flag: '🇪🇸', popular: false },
        { code: 'hr', name: 'Croatian', native: 'Hrvatski', flag: '🇭🇷', popular: false },
        { code: 'et', name: 'Estonian', native: 'Eesti', flag: '🇪🇪', popular: false },
        { code: 'ka', name: 'Georgian', native: 'ქართული', flag: '🇬🇪', popular: false },
        { code: 'ht', name: 'Haitian Creole', native: 'Kreyòl', flag: '🇭🇹', popular: false },
        { code: 'is', name: 'Icelandic', native: 'Íslenska', flag: '🇮🇸', popular: false },
        { code: 'ga', name: 'Irish', native: 'Gaeilge', flag: '🇮🇪', popular: false },
        { code: 'jw', name: 'Javanese', native: 'Basa Jawa', flag: '🇮🇩', popular: false },
        { code: 'kk', name: 'Kazakh', native: 'Қазақ', flag: '🇰🇿', popular: false },
        { code: 'km', name: 'Khmer', native: 'ខ្មែរ', flag: '🇰🇭', popular: false },
        { code: 'ku', name: 'Kurdish', native: 'Kurdî', flag: '🇮🇶', popular: false },
        { code: 'lo', name: 'Lao', native: 'ລາວ', flag: '🇱🇦', popular: false },
        { code: 'la', name: 'Latin', native: 'Latina', flag: '🏛️', popular: false },
        { code: 'lv', name: 'Latvian', native: 'Latviešu', flag: '🇱🇻', popular: false },
        { code: 'lt', name: 'Lithuanian', native: 'Lietuvių', flag: '🇱🇹', popular: false },
        { code: 'mk', name: 'Macedonian', native: 'Македонски', flag: '🇲🇰', popular: false },
        { code: 'ne', name: 'Nepali', native: 'नेपाली', flag: '🇳🇵', popular: false },
        { code: 'sk', name: 'Slovak', native: 'Slovenčina', flag: '🇸🇰', popular: false },
        { code: 'sl', name: 'Slovenian', native: 'Slovenščina', flag: '🇸🇮', popular: false },
        { code: 'so', name: 'Somali', native: 'Soomaali', flag: '🇸🇴', popular: false },
        { code: 'su', name: 'Sundanese', native: 'Basa Sunda', flag: '🇮🇩', popular: false },
        { code: 'tg', name: 'Tajik', native: 'Тоҷикӣ', flag: '🇹🇯', popular: false },
        { code: 'uz', name: 'Uzbek', native: 'Oʻzbek', flag: '🇺🇿', popular: false },
        { code: 'cy', name: 'Welsh', native: 'Cymraeg', flag: '🏴󠁧󠁢󠁷󠁬󠁳󠁿', popular: false },
        { code: 'yi', name: 'Yiddish', native: 'ייִדיש', flag: '✡️', popular: false },
        { code: 'yo', name: 'Yoruba', native: 'Yorùbá', flag: '🇳🇬', popular: false },
        { code: 'zu', name: 'Zulu', native: 'isiZulu', flag: '🇿🇦', popular: false }
    ];

    // Helper: Read active language from googtrans cookie
    function getStoredTranslateLang() {
        const match = document.cookie.match(/(?:^|;\s*)googtrans=\/en\/([a-zA-Z\-]+)/);
        return match ? match[1] : 'en';
    }

    // Helper: Write googtrans cookie across root and host domain
    function setTranslateCookie(langCode) {
        const host = window.location.hostname;
        const cookieVal = (!langCode || langCode === 'en') ? '' : ('/en/' + langCode);
        const expires = (!langCode || langCode === 'en') ? '; expires=Thu, 01 Jan 1970 00:00:00 UTC' : '; max-age=31536000';

        document.cookie = 'googtrans=' + cookieVal + expires + '; path=/;';
        document.cookie = 'googtrans=' + cookieVal + expires + '; path=/; domain=' + host;
        document.cookie = 'googtrans=' + cookieVal + expires + '; path=/; domain=.' + host;
    }

    // Programmatically trigger Google Translate
    window.applyUniversalLanguage = function(langCode, langName, flag) {
        setTranslateCookie(langCode);
        updateLanguageUI(langCode, langName, flag);

        // Find Google's native select element
        const combo = document.querySelector('.goog-te-combo');
        if (combo) {
            combo.value = (langCode === 'en') ? '' : langCode;
            combo.dispatchEvent(new Event('change', { bubbles: true }));
            if (langCode === 'en') {
                setTimeout(function() {
                    window.location.reload();
                }, 150);
            }
        } else {
            // Check if combo appears shortly or reload
            let attempts = 0;
            const comboPoll = setInterval(function() {
                const lateCombo = document.querySelector('.goog-te-combo');
                if (lateCombo) {
                    clearInterval(comboPoll);
                    lateCombo.value = (langCode === 'en') ? '' : langCode;
                    lateCombo.dispatchEvent(new Event('change', { bubbles: true }));
                } else if (++attempts >= 5) {
                    clearInterval(comboPoll);
                    window.location.reload();
                }
            }, 100);
        }

        // Close dropdown
        const wrapper = document.getElementById('langDropdownWrapper');
        if (wrapper) {
            wrapper.classList.remove('open');
            const btn = wrapper.querySelector('button[aria-expanded]');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    };

    // Update active indicators across Desktop and Mobile selectors
    function updateLanguageUI(langCode, langName, flag) {
        const item = UNIVERSAL_LANGUAGES.find(l => l.code === langCode) || UNIVERSAL_LANGUAGES[0];
        const displayName = langName || item.native;
        const displayFlag = flag || item.flag;

        // Desktop Button Label
        const desktopLabel = document.getElementById('currentLangLabel');
        if (desktopLabel) {
            desktopLabel.textContent = (item.code === 'en') ? 'English' : displayName;
        }
        const desktopGlobe = document.getElementById('currentLangGlobe');
        if (desktopGlobe) {
            desktopGlobe.textContent = (item.code === 'en') ? '🌐' : displayFlag;
        }

        // Active State in Desktop List
        document.querySelectorAll('.lang-item-btn').forEach(btn => {
            const code = btn.getAttribute('data-code');
            btn.classList.toggle('active', code === item.code);
        });

        // Active State in Quick Chips
        document.querySelectorAll('.lang-chip').forEach(chip => {
            const code = chip.getAttribute('data-code');
            chip.classList.toggle('active', code === item.code);
        });

        // Mobile Drawer Elements
        const mobileFlag = document.getElementById('mobileCurrentFlag');
        const mobileName = document.getElementById('mobileCurrentName');
        const mobileSelect = document.getElementById('mobileLangSelect');

        if (mobileFlag) mobileFlag.textContent = (item.code === 'en') ? '🌐' : displayFlag;
        if (mobileName) mobileName.textContent = (item.code === 'en') ? 'English (Original)' : displayName;
        if (mobileSelect && mobileSelect.value !== item.code) {
            mobileSelect.value = item.code;
        }
    }

    // Google Translate Callback function
    window.googleTranslateElementInit = function() {
        if (window.google && window.google.translate) {
            new window.google.translate.TranslateElement({
                pageLanguage: 'en',
                autoDisplay: false
            }, 'google_translate_element');
        }
    };

    // Initialize UI on DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        const langDropdownWrapper = document.getElementById('langDropdownWrapper');
        const langPickerBtn = document.getElementById('langPickerBtn');
        const langSearchInput = document.getElementById('langSearchInput');
        const langList = document.getElementById('langList');
        const langQuickChips = document.getElementById('langQuickChips');
        const mobileLangSelect = document.getElementById('mobileLangSelect');
        const langResetBtn = document.getElementById('langResetBtn');

        const activeLang = getStoredTranslateLang();

        // 1. Populate Quick Chips
        if (langQuickChips) {
            const popularLangs = UNIVERSAL_LANGUAGES.filter(l => l.popular);
            langQuickChips.innerHTML = popularLangs.map(l => `
                <button type="button" class="lang-chip notranslate ${l.code === activeLang ? 'active' : ''}" data-code="${l.code}">
                    <span>${l.flag}</span>
                    <span>${l.native}</span>
                </button>
            `).join('');

            langQuickChips.addEventListener('click', function(e) {
                const btn = e.target.closest('.lang-chip');
                if (btn) {
                    const code = btn.getAttribute('data-code');
                    const item = UNIVERSAL_LANGUAGES.find(l => l.code === code);
                    if (item) window.applyUniversalLanguage(item.code, item.native, item.flag);
                }
            });
        }

        // 2. Populate Full Language List
        function renderLangList(filterQuery = '') {
            if (!langList) return;
            const query = filterQuery.toLowerCase().trim();
            const filtered = UNIVERSAL_LANGUAGES.filter(l => 
                l.name.toLowerCase().includes(query) || 
                l.native.toLowerCase().includes(query) || 
                l.code.toLowerCase().includes(query)
            );

            if (filtered.length === 0) {
                langList.innerHTML = `
                    <div style="padding: 1rem; text-align: center; color: #94a3b8; font-size: 0.8rem;" class="notranslate">
                        No matching languages found
                    </div>
                `;
                return;
            }

            langList.innerHTML = filtered.map(l => `
                <button type="button" class="lang-item-btn notranslate ${l.code === activeLang ? 'active' : ''}" data-code="${l.code}">
                    <div class="lang-item-left">
                        <span class="lang-item-flag">${l.flag}</span>
                        <div class="lang-item-text">
                            <span class="lang-item-native">${l.native}</span>
                            <span class="lang-item-english">${l.name}</span>
                        </div>
                    </div>
                    <span class="lang-item-check">✓</span>
                </button>
            `).join('');
        }

        renderLangList();

        if (langList) {
            langList.addEventListener('click', function(e) {
                const btn = e.target.closest('.lang-item-btn');
                if (btn) {
                    const code = btn.getAttribute('data-code');
                    const item = UNIVERSAL_LANGUAGES.find(l => l.code === code);
                    if (item) window.applyUniversalLanguage(item.code, item.native, item.flag);
                }
            });
        }

        // 3. Search Filter
        if (langSearchInput) {
            langSearchInput.addEventListener('input', function(e) {
                renderLangList(e.target.value);
            });
        }

        // 4. Populate Mobile Select
        if (mobileLangSelect) {
            mobileLangSelect.innerHTML = UNIVERSAL_LANGUAGES.map(l => `
                <option value="${l.code}" ${l.code === activeLang ? 'selected' : ''}>
                    ${l.flag} ${l.native} (${l.name})
                </option>
            `).join('');

            mobileLangSelect.addEventListener('change', function(e) {
                const code = e.target.value;
                const item = UNIVERSAL_LANGUAGES.find(l => l.code === code);
                if (item) window.applyUniversalLanguage(item.code, item.native, item.flag);
            });
        }

        // 5. Reset button
        if (langResetBtn) {
            langResetBtn.addEventListener('click', function() {
                window.applyUniversalLanguage('en', 'English', '🇺🇸');
            });
        }

        // 6. Initial UI Sync
        const currentItem = UNIVERSAL_LANGUAGES.find(l => l.code === activeLang) || UNIVERSAL_LANGUAGES[0];
        updateLanguageUI(currentItem.code, currentItem.native, currentItem.flag);

        // 7. Prevent Google banner top jump on body
        const bodyObserver = new MutationObserver(function() {
            if (document.body.style.top && document.body.style.top !== '0px') {
                document.body.style.top = '0px';
            }
        });
        bodyObserver.observe(document.body, { attributes: true, attributeFilter: ['style'] });
    });
</script>

{{-- Google Translate API script loader --}}
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

