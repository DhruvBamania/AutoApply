@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin-top: 0.5rem;">Resume for {{ $resume->company_name ?: 'Target Job' }}</h1>
            <p class="subtitle" style="margin-bottom: 0;">Tailored on {{ $resume->created_at->format('M j, Y') }}</p>
        </div>
        <a href="{{ route('resumes.tailor') }}" class="btn btn-secondary">← Back to Builder</a>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
            <div class="card-title">Tailored Markdown Content</div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="{{ route('resumes.download', $resume->id) }}" class="btn btn-secondary btn-sm" style="background: white; color: #2563eb; border: 1px solid #2563eb;">Download .docx</a>
                <button class="btn btn-primary btn-sm" onclick="copyContent()">Copy to Clipboard</button>
            </div>
        </div>
        
        <!-- Editable text area so they can copy and paste into Word/Notion or edit -->
        <textarea id="resumeContent" class="form-control draft-box" style="height: 600px; resize: vertical; font-family: 'Courier New', Courier, monospace;" readonly>{{ $resume->tailored_content }}</textarea>
    </div>

    <script>
        function copyContent() {
            var copyText = document.getElementById("resumeContent");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            navigator.clipboard.writeText(copyText.value);
            alert("Copied to clipboard!");
        }
    </script>
@endsection
