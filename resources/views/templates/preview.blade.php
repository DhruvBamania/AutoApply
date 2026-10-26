@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1000px; margin: 0 auto; padding-bottom: 50px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin: 0; color: #1e293b;">Resume Preview</h1>
            <p class="text-muted" style="margin-top: 0.5rem;">Review your tailored resume layout before downloading the DOCX file.</p>
        </div>
        
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
            <a href="{{ route('templates.index') }}" class="btn-secondary" style="text-decoration: none; flex: 1; text-align: center;">← Back</a>
            <a href="{{ route('templates.download', $id) }}" class="btn-primary" style="text-decoration: none; flex: 1; text-align: center;">↓ Download DOCX</a>
        </div>
    </div>

    <!-- PREVIEW BOX -->
    <div id="preview-wrapper" class="card" style="padding: 0; overflow-x: hidden; overflow-y: auto; background: #e2e8f0; border: 1px solid var(--surface-border); display: flex; justify-content: center; padding-top: 40px; padding-bottom: 40px;">
        
        <!-- The actual A4 page container -->
        <div id="preview-container" style="background: white; width: 210mm; min-height: 297mm; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); padding: 40px; overflow: visible; transform-origin: top center; height: auto;">
            {!! $htmlContent !!}
        </div>
        
    </div>

</div>

<!-- Override some HTML Writer defaults that mess up the preview -->
<style>
    /* The PHPWord HTML writer outputs a 'body' and a 'div' container representing the page. */
    /* Since we inject it into our DOM, we need to strip/override its body styles. */
    div[style*="page: page1"] {
        width: 100%;
        font-family: inherit;
    }
    div[style*="page: page1"] table {
        width: 100% !important;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    div[style*="page: page1"] p {
        margin-top: 5px;
        margin-bottom: 5px;
    }
    
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
@endsection
