@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Top Hero Banner -->
    <div class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); color: white; border: none; padding: 2.2rem 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(49, 46, 129, 0.25); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -20px; top: -30px; font-size: 9rem; opacity: 0.08; pointer-events: none; user-select: none;">
            🌐
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.5rem; position: relative; z-index: 1;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.8rem; border: 1px solid rgba(255,255,255,0.2);">
                    <span>🌍</span> International Career Mobility
                </div>
                <h1 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 0.6rem; color: #ffffff;">
                    AI Multilingual Resume Studio
                </h1>
                <p style="color: #c7d2fe; font-size: 1.05rem; max-width: 680px; line-height: 1.5; margin: 0;">
                    Translate and culturally adapt your resume into German, Spanish, French, Japanese, Mandarin, Hindi, or any language in the world — fully localized while safeguarding technical terminology and ATS formatting.
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('resume_editor.index') }}" class="btn-secondary" style="background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(8px);">
                    📝 Dynamic Editor
                </a>
                <a href="{{ route('templates.index') }}" class="btn-secondary" style="background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(8px);">
                    🎨 ATS Templates
                </a>
            </div>
        </div>
    </div>

    @if(!$hasBaseResume)
        <div class="card" style="border-left: 5px solid #f59e0b; background: #fffbeb; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size: 2rem;">⚠️</span>
                <div>
                    <h4 style="margin: 0; color: #92400e; font-weight: 700;">No Base Resume Found</h4>
                    <p style="margin: 4px 0 0 0; color: #b45309; font-size: 0.92rem;">
                        Upload your master resume PDF on the Dashboard first so the AI has your experience and achievements to translate.
                    </p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-primary" style="background: #d97706; padding: 0.6rem 1.4rem;">
                Upload Resume in Dashboard →
            </a>
        </div>
    @endif

    <!-- Translation Configuration Form -->
    <div class="card" style="padding: 2rem; border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <div>
                <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0 0 4px 0;">
                    1. Select Target Language(s)
                </h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                    Choose single or multiple languages to translate your resume into. Each language generates a tailored ATS resume version.
                </p>
            </div>
            <!-- Quick Selection Filters -->
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn-secondary btn-sm" onclick="selectTopLanguages(['de', 'es', 'fr', 'ja', 'zh'])" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                    ⭐ Top 5 Global
                </button>
                <button type="button" class="btn-secondary btn-sm" onclick="selectTopLanguages(['de', 'fr', 'es', 'it', 'nl', 'pt'])" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                    🇪🇺 European Bundle
                </button>
                <button type="button" class="btn-secondary btn-sm" onclick="clearAllLanguages()" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; color: #ef4444;">
                    Clear Selection
                </button>
            </div>
        </div>

        <form action="{{ route('multilingual_resumes.generate') }}" method="POST" id="translateForm" onsubmit="handleTranslateSubmit(event)">
            @csrf

            <!-- Preset Language Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 0.9rem; margin-bottom: 1.5rem;">
                @foreach($presets as $preset)
                    <label class="lang-pill" style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1.5px solid #e2e8f0; border-radius: 14px; cursor: pointer; transition: all 0.2s; background: #ffffff;">
                        <input type="checkbox" name="languages[]" value="{{ $preset['name'] }}" data-code="{{ $preset['code'] }}" class="lang-checkbox" style="width: 18px; height: 18px; accent-color: #4f46e5; cursor: pointer;">
                        <span style="font-size: 1.6rem; line-height: 1;">{{ $preset['flag'] }}</span>
                        <div style="min-width: 0;">
                            <div style="font-weight: 700; font-size: 0.92rem; color: #1e1b4b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $preset['name'] }}
                            </div>
                            <div style="font-size: 0.76rem; color: #64748b;">
                                {{ $preset['native'] }}
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <!-- Custom Any Language Input & Options Grid -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem; margin-bottom: 1.75rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <!-- Custom Language -->
                <div>
                    <label style="font-size: 0.88rem; font-weight: 700; color: #1e1b4b; display: block; margin-bottom: 6px;">
                        🌐 Other Language(s) Not Listed Above:
                    </label>
                    <input type="text" name="custom_language" id="custom_language" class="form-control" placeholder="e.g. Norwegian, Greek, Hebrew, Bengali..." style="background: white; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 0.9rem;">
                    <span style="font-size: 0.76rem; color: #64748b; margin-top: 4px; display: block;">
                        Type any world language. Separate multiple with commas.
                    </span>
                </div>

                <!-- Template Selector -->
                <div>
                    <label style="font-size: 0.88rem; font-weight: 700; color: #1e1b4b; display: block; margin-bottom: 6px;">
                        🎨 ATS Resume Template:
                    </label>
                    <select name="template_id" class="form-control" style="background: white; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 0.9rem;">
                        @foreach($templates as $tid => $tname)
                            <option value="{{ $tid }}" {{ $tid === 1 ? 'selected' : '' }}>Template {{ $tid }} - {{ $tname }}</option>
                        @endforeach
                    </select>
                    <span style="font-size: 0.76rem; color: #64748b; margin-top: 4px; display: block;">
                        Formats heading styles, margins, and typography in DOCX export.
                    </span>
                </div>

                <!-- Target Role (Optional) -->
                <div>
                    <label style="font-size: 0.88rem; font-weight: 700; color: #1e1b4b; display: block; margin-bottom: 6px;">
                        💼 Target Role / Focus (Optional):
                    </label>
                    <input type="text" name="target_role" class="form-control" placeholder="e.g. Senior Full Stack Engineer" style="background: white; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 0.9rem;">
                    <span style="font-size: 0.76rem; color: #64748b; margin-top: 4px; display: block;">
                        Guides AI on role-specific terminology for that target country.
                    </span>
                </div>
            </div>

            <!-- Submit Action Bar -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span id="selectedCountLabel" style="font-size: 0.9rem; font-weight: 600; color: #4338ca;">
                    0 languages selected
                </span>
                <button type="submit" id="translateSubmitBtn" class="btn-primary" style="padding: 0.85rem 2.2rem; font-size: 1rem; font-weight: 700; border-radius: 14px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35);">
                    <span id="btnText">✨ Generate Multilingual Resume(s)</span>
                    <span id="btnSpinner" style="display: none; align-items: center; gap: 8px;">
                        <svg class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.3"></circle>
                            <path d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" fill="currentColor"></path>
                        </svg>
                        Translating & Formatting...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Saved Multilingual Resumes Vault -->
    <div class="card" style="padding: 2rem; border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <div>
                <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0 0 4px 0;">
                    2. Your Multilingual Resume Vault ({{ $resumes->count() }})
                </h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                    All your saved language versions ready for instant preview, ATS DOCX download, or international applications.
                </p>
            </div>
        </div>

        @if($resumes->isEmpty())
            <div style="text-align: center; padding: 3.5rem 1rem; color: #64748b;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">🗺️</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e1b4b; margin-bottom: 0.4rem;">
                    No Multilingual Resumes Generated Yet
                </h3>
                <p style="max-width: 460px; margin: 0 auto; font-size: 0.9rem; line-height: 1.5;">
                    Select your desired destination languages above (like German, Spanish, or French) and click generate to create your first international resume.
                </p>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
                @foreach($resumes as $res)
                    @php
                        $resumeData = $res->resume_data ?? [];
                        $skillsCount = count($resumeData['skills'] ?? []);
                        $expCount = count($resumeData['experience'] ?? []);
                        $templateName = $templates[$res->template_id] ?? 'Template ' . $res->template_id;
                    @endphp
                    <div class="card" style="border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 1.4rem; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.25s; box-shadow: 0 4px 12px rgba(0,0,0,0.03);" onmouseover="this.style.borderColor='#818cf8'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'">
                        <div>
                            <!-- Header row -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 2.2rem; line-height: 1;">{{ $res->language_flag }}</span>
                                    <div>
                                        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #1e1b4b;">
                                            {{ $res->language_name }}
                                        </h3>
                                        <span style="font-size: 0.78rem; color: #64748b;">
                                            {{ $res->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.74rem; font-weight: 700; padding: 4px 8px; border-radius: 8px;">
                                    {{ $templateName }}
                                </span>
                            </div>

                            @if($res->target_role)
                                <div style="margin-bottom: 0.75rem;">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: #4f46e5; background: #eef2ff; padding: 3px 8px; border-radius: 6px;">
                                        💼 {{ $res->target_role }}
                                    </span>
                                </div>
                            @endif

                            <!-- Translated Summary Snippet -->
                            @if(!empty($resumeData['summary']))
                                <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $resumeData['summary'] }}
                                </p>
                            @endif

                            <!-- Metadata tags -->
                            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
                                <span style="font-size: 0.78rem; background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 6px;">
                                    🛠️ {{ $skillsCount }} Skills
                                </span>
                                <span style="font-size: 0.78rem; background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 6px;">
                                    💼 {{ $expCount }} Roles
                                </span>
                                @if(!empty($resumeData['job_title']))
                                    <span style="font-size: 0.78rem; background: #ecfdf5; color: #065f46; padding: 3px 8px; border-radius: 6px;">
                                        {{ $resumeData['job_title'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions row -->
                        <div style="display: flex; gap: 0.6rem; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem; flex-wrap: wrap;">
                            <button type="button" class="btn-secondary btn-sm" onclick="openPreviewModal({{ $res->id }}, '{{ addslashes($res->language_flag . ' ' . $res->language_name) }}')" style="flex: 1; padding: 0.5rem 0.8rem; font-size: 0.84rem; font-weight: 600; text-align: center;">
                                👁️ Preview
                            </button>
                            <a href="{{ route('multilingual_resumes.download', $res->id) }}" class="btn-primary btn-sm" style="flex: 1.2; padding: 0.5rem 0.8rem; font-size: 0.84rem; font-weight: 700; text-align: center; text-decoration: none;">
                                📥 DOCX
                            </a>
                            <form action="{{ route('multilingual_resumes.destroy', $res->id) }}" method="POST" onsubmit="return confirm('Delete this {{ $res->language_name }} resume version?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary btn-sm" style="color: #ef4444; border-color: #fee2e2; padding: 0.5rem 0.75rem;" title="Delete">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<!-- Interactive Modal for Live Preview -->
<div id="multilingualPreviewModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 900px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <!-- Modal Header -->
        <div style="padding: 1.2rem 1.6rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span id="modalLangFlag" style="font-size: 1.8rem;">🌐</span>
                <h3 id="modalTitle" style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e1b4b;">
                    Resume Preview
                </h3>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a id="modalDownloadBtn" href="#" class="btn-primary btn-sm" style="text-decoration: none; font-size: 0.85rem; padding: 0.45rem 1.1rem;">
                    📥 Download .DOCX
                </a>
                <button type="button" class="btn-secondary btn-sm" onclick="closePreviewModal()" style="font-size: 1.1rem; padding: 0.3rem 0.7rem; border-radius: 8px;">
                    ✕
                </button>
            </div>
        </div>
        <!-- Modal Body / Resume Preview Document -->
        <div id="modalBody" style="flex: 1; overflow-y: auto; padding: 2rem; background: #e2e8f0; display: flex; justify-content: center;">
            <div id="modalDocumentContainer" style="background: white; width: 100%; max-width: 210mm; min-height: 297mm; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 4px;">
                <div style="text-align: center; padding: 50px; color: #64748b;">
                    Loading resume preview...
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .lang-pill:has(.lang-checkbox:checked) {
        border-color: #4f46e5 !important;
        background: #eef2ff !important;
        box-shadow: 0 0 0 1px #4f46e5;
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

<script>
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.lang-checkbox:checked').length;
        const custom = document.getElementById('custom_language')?.value.trim();
        const customCount = custom ? custom.split(',').filter(s => s.trim().length > 0).length : 0;
        const total = checked + customCount;

        const label = document.getElementById('selectedCountLabel');
        if (label) {
            label.textContent = total === 1 ? '1 language selected' : total + ' languages selected';
            label.style.color = total > 0 ? '#4338ca' : '#64748b';
        }
    }

    document.querySelectorAll('.lang-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    document.getElementById('custom_language')?.addEventListener('input', updateSelectedCount);

    function selectTopLanguages(codes) {
        document.querySelectorAll('.lang-checkbox').forEach(cb => {
            const code = cb.getAttribute('data-code');
            cb.checked = codes.includes(code);
        });
        updateSelectedCount();
    }

    function clearAllLanguages() {
        document.querySelectorAll('.lang-checkbox').forEach(cb => cb.checked = false);
        const custom = document.getElementById('custom_language');
        if (custom) custom.value = '';
        updateSelectedCount();
    }

    function handleTranslateSubmit(e) {
        const checked = document.querySelectorAll('.lang-checkbox:checked').length;
        const custom = document.getElementById('custom_language')?.value.trim();

        if (checked === 0 && !custom) {
            e.preventDefault();
            alert('Please select at least one language preset or enter a custom language.');
            return false;
        }

        const btn = document.getElementById('translateSubmitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        btn.disabled = true;
        btnText.style.display = 'none';
        btnSpinner.style.display = 'inline-flex';
        return true;
    }

    function openPreviewModal(id, title) {
        const modal = document.getElementById('multilingualPreviewModal');
        const modalTitle = document.getElementById('modalTitle');
        const downloadBtn = document.getElementById('modalDownloadBtn');
        const docContainer = document.getElementById('modalDocumentContainer');

        modalTitle.textContent = title + ' Resume Preview';
        downloadBtn.href = `/multilingual-resumes/${id}/download`;
        docContainer.innerHTML = '<div style="text-align: center; padding: 60px; color: #64748b;">Loading formatted ATS preview...</div>';

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        fetch(`/multilingual-resumes/${id}/preview`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.html) {
                docContainer.innerHTML = data.html;
            } else {
                docContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;">Unable to load preview. Please download the DOCX directly.</div>';
            }
        })
        .catch(err => {
            console.error('Preview error:', err);
            docContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;">Connection error loading preview.</div>';
        });
    }

    function closePreviewModal() {
        const modal = document.getElementById('multilingualPreviewModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close on click outside modal container
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('multilingualPreviewModal');
        if (e.target === modal) {
            closePreviewModal();
        }
    });

    // Close on Escape key
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreviewModal();
        }
    });

    // Initialize selection count
    updateSelectedCount();
</script>
@endsection

