@extends('layouts.app')

@section('content')
<div class="editor-layout" style="display: flex; gap: 2rem; width: 100%; max-width: 100%; margin: 0 auto; height: calc(100vh - 120px); box-sizing: border-box;">
    
    <!-- LEFT SIDE: EDITOR -->
    <div class="editor-sidebar" style="flex: 1; min-width: 0; max-width: 100%; overflow-y: auto; padding-right: 1rem; border-right: 1px solid var(--surface-border);">
        <h2 style="margin-top: 0;">Dynamic Resume Builder</h2>
        <p class="text-muted">Edit your details below. The preview on the right will update automatically.</p>
        
        <form id="resume-form" action="{{ route('resume_editor.download') }}" method="POST">
            @csrf
            
            <!-- TEMPLATE SELECTION & DOWNLOAD -->
            <div class="card" style="margin-bottom: 2rem; position: sticky; top: 0; z-index: 10; background: var(--surface-card);">
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1 1 100%;">
                        <label>Select Template</label>
                        <select name="template_id" id="template_id" class="form-control" style="margin-bottom: 0;">
                            @foreach($templates as $id => $name)
                                <option value="{{ $id }}" {{ (isset($selectedTemplate) && $selectedTemplate == $id) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Download DOCX</button>
                </div>
            </div>

            <!-- BASIC INFO -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <h3>Basic Information</h3>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $resumeData['name'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="job_title" class="form-control" value="{{ $resumeData['job_title'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Contact Info (Email | Phone | Location | Links)</label>
                    <input type="text" name="contact" class="form-control" value="{{ $resumeData['contact'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Professional Summary</label>
                    <textarea name="summary" class="form-control" rows="4">{{ $resumeData['summary'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- SKILLS & CERTS -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <h3>Skills & Certifications (Comma Separated)</h3>
                <div class="form-group">
                    <label>Skills</label>
                    @php $skillsStr = isset($resumeData['skills']) && is_array($resumeData['skills']) ? implode(', ', $resumeData['skills']) : ($resumeData['skills'] ?? ''); @endphp
                    <textarea name="skills" class="form-control" rows="2">{{ $skillsStr }}</textarea>
                </div>
                <div class="form-group">
                    <label>Certifications</label>
                    @php $certsStr = isset($resumeData['certifications']) && is_array($resumeData['certifications']) ? implode(', ', $resumeData['certifications']) : ($resumeData['certifications'] ?? ''); @endphp
                    <textarea name="certifications" class="form-control" rows="2">{{ $certsStr }}</textarea>
                </div>
            </div>

            <!-- EXPERIENCE -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Work Experience</h3>
                    <button type="button" class="btn-secondary" onclick="addArrayItem('experience')">+ Add</button>
                </div>
                <div id="experience-container">
                    @if(isset($resumeData['experience']) && is_array($resumeData['experience']))
                        @foreach($resumeData['experience'] as $index => $exp)
                            @include('partials.resume_experience', ['index' => $index, 'exp' => $exp])
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- PROJECTS -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Projects</h3>
                    <button type="button" class="btn-secondary" onclick="addArrayItem('projects')">+ Add</button>
                </div>
                <div id="projects-container">
                    @if(isset($resumeData['projects']) && is_array($resumeData['projects']))
                        @foreach($resumeData['projects'] as $index => $proj)
                            @include('partials.resume_project', ['index' => $index, 'proj' => $proj])
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- EDUCATION -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Education</h3>
                    <button type="button" class="btn-secondary" onclick="addArrayItem('education')">+ Add</button>
                </div>
                <div id="education-container">
                    @if(isset($resumeData['education']) && is_array($resumeData['education']))
                        @foreach($resumeData['education'] as $index => $edu)
                            @include('partials.resume_education', ['index' => $index, 'edu' => $edu])
                        @endforeach
                    @endif
                </div>
            </div>

        </form>
    </div>

    <!-- RIGHT SIDE: PREVIEW -->
    <div id="preview-wrapper" class="editor-preview" style="flex: 1.2; background: #e2e8f0; border-radius: 8px; overflow-x: hidden; overflow-y: auto; display: flex; justify-content: center; padding: 2rem;">
        
        <div id="preview-container" style="background: white; width: 210mm; min-height: 297mm; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); padding: 40px; overflow: visible; transform-origin: top center; height: auto;">
            <div style="text-align: center; margin-top: 100px; color: #94a3b8;">
                Loading preview...
            </div>
        </div>
        
    </div>

</div>

<!-- Override PHPWord body wrapper -->
<style>
    /* The PHPWord HTML writer outputs a 'body' and a 'div' container representing the page. */
    /* Since we inject it into our DOM, we need to strip/override its body styles. */
    div[style*="page: page1"] { width: 100%; font-family: inherit; }
    div[style*="page: page1"] table { width: 100% !important; border-collapse: collapse; margin-bottom: 10px; }
    div[style*="page: page1"] p { margin-top: 5px; margin-bottom: 5px; }
    
    @media (max-width: 900px) {
        #preview-wrapper { 
            padding: 1rem !important; 
            overflow-x: hidden !important;
            overflow-y: visible !important;
            height: auto !important;
            display: flex; 
            align-items: flex-start; 
            justify-content: center; 
        }
        @supports (zoom: 1) {
            #preview-container { zoom: 0.7; transform: none !important; margin: 0 !important; }
        }
        @supports not (zoom: 1) {
            #preview-container { transform: scale(0.7); transform-origin: top center; margin: 0 !important; margin-bottom: -30% !important; }
        }
    }
    @media (max-width: 600px) {
        #preview-wrapper { 
            padding: 0.5rem !important; 
            height: auto !important;
        }
        @supports (zoom: 1) {
            #preview-container { zoom: 0.45; transform: none !important; margin: 0 !important; }
        }
        @supports not (zoom: 1) {
            #preview-container { transform: scale(0.45); transform-origin: top center; margin: 0 !important; margin-bottom: -50% !important; }
        }
    }
</style>

<!-- Hidden Templates for JS dynamic addition -->
<template id="tpl-experience">
    @include('partials.resume_experience', ['index' => '__INDEX__', 'exp' => []])
</template>
<template id="tpl-projects">
    @include('partials.resume_project', ['index' => '__INDEX__', 'proj' => []])
</template>
<template id="tpl-education">
    @include('partials.resume_education', ['index' => '__INDEX__', 'edu' => []])
</template>

<script>
    let updateTimeout;

    // Trigger update on form input
    document.getElementById('resume-form').addEventListener('input', function() {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(fetchPreview, 800);
    });

    // Trigger update on template change immediately
    document.getElementById('template_id').addEventListener('change', fetchPreview);

    function fetchPreview() {
        const form = document.getElementById('resume-form');
        const formData = new FormData(form);
        
        fetch('{{ route('resume_editor.preview') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.html) {
                document.getElementById('preview-container').innerHTML = data.html;
            }
        })
        .catch(err => console.error('Error fetching preview', err));
    }

    // Add array item
    function addArrayItem(type) {
        const container = document.getElementById(type + '-container');
        const index = container.children.length; // use length as index
        const tpl = document.getElementById('tpl-' + type).innerHTML;
        const html = tpl.replace(/__INDEX__/g, index);
        
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        container.appendChild(wrapper.firstElementChild);
        
        fetchPreview();
    }

    // Remove array item
    function removeArrayItem(button) {
        button.closest('.array-item').remove();
        fetchPreview();
    }

    // Initial load
    fetchPreview();
</script>

@endsection
