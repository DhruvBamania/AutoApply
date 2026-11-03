@extends('layouts.app')

@section('content')
<div style="max-width: 900px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: #1e1b4b; margin: 0;">
                {{ $resume->language_flag }} {{ $resume->language_name }} Resume Preview
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 4px 0 0 0;">
                Formatted with Template {{ $resume->template_id }}
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('multilingual_resumes.index') }}" class="btn-secondary">
                ← Back to Vault
            </a>
            <a href="{{ route('multilingual_resumes.download', $resume->id) }}" class="btn-primary">
                📥 Download .DOCX
            </a>
        </div>
    </div>

    <div style="background: #e2e8f0; padding: 2rem; border-radius: 16px; display: flex; justify-content: center; overflow-x: auto;">
        <div style="background: white; width: 100%; max-width: 210mm; min-height: 297mm; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 4px;">
            {!! $html !!}
        </div>
    </div>
</div>
@endsection

