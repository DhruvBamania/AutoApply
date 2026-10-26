@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Resume Template Gallery</h1>
        <p class="subtitle" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
            Choose a beautiful, ATS-optimized layout. We'll automatically inject your parsed resume data and generate a flawless .docx file for you to download.
        </p>
    </div>

    @if(!auth()->user()->resume_json)
        <div class="card" style="max-width: 600px; margin: 0 auto 3rem auto; text-align: center; border-color: #fca5a5;">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #ef4444; margin-bottom: 0.5rem;">Resume Required</h3>
                <p class="text-muted">Upload your PDF resume below so our AI can extract your information and inject it into these templates.</p>
            </div>
            <form action="{{ route('resume.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" name="resume" class="form-control" accept=".pdf" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Upload & Parse Resume</button>
            </form>
        </div>
    @else
        <div class="card" style="max-width: 600px; margin: 0 auto 3rem auto; text-align: center;">
            <div style="margin-bottom: 1rem;">
                <h3 style="margin-bottom: 0.5rem;">Want to update your data?</h3>
                <p class="text-muted" style="font-size: 0.9rem;">You can re-upload a newer version of your resume at any time. Our AI will extract the latest details.</p>
            </div>
            <form action="{{ route('resume.upload') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                @csrf
                <input type="file" name="resume" class="form-control" style="flex: 1; min-width: 200px; margin: 0;" accept=".pdf" required>
                <button type="submit" class="btn-secondary" style="white-space: nowrap; flex: 1; min-width: 150px;">Re-upload Resume</button>
            </form>
        </div>
    @endif

    <div class="grid-2">
        @foreach($templates as $template)
            <div class="card" style="display: flex; flex-direction: column; position: relative; overflow: hidden; border-top: 6px solid {{ $template['color'] }}; padding: 0;">
                
                <div style="width: 100%; height: 250px; overflow: hidden; border-bottom: 1px solid var(--surface-border);">
                    <img src="{{ asset('images/templates/' . $template['preview']) }}" alt="{{ $template['name'] }} Preview" style="width: 100%; height: 100%; object-fit: cover; object-position: top;">
                </div>
                
                <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <h2 style="margin: 0;">{{ $template['name'] }}</h2>
                        <span class="badge" style="background: {{ $template['color'] }}20; color: {{ $template['color'] }};">ATS Ready</span>
                    </div>
                    <p class="text-muted" style="margin-bottom: 2rem; flex: 1;">{{ $template['description'] }}</p>
                    
                    @if(auth()->user()->resume_json)
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%;">
                            <a href="{{ route('templates.preview', $template['id']) }}" class="btn-primary" style="text-align: center; width: 100%;">
                                👁 Preview Template
                            </a>
                            <a href="{{ route('resume_editor.index', ['template' => $template['id']]) }}" class="btn-secondary" style="text-align: center; width: 100%; font-size: 0.9rem;">
                                ✏️ Open in Dynamic Editor
                            </a>
                        </div>
                    @else
                        <button class="btn-secondary" style="width: 100%; cursor: not-allowed; opacity: 0.7;" disabled>
                            Upload Resume First
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
