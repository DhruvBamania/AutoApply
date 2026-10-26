@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin-top: 0.5rem;">AI Social Campaign</h1>
            <p class="subtitle" style="margin-bottom: 0;">Preview and approve your cross-platform "Open to Work" posts.</p>
        </div>
        
        <form action="{{ route('social_posts.generate') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">⚡ Generate Fresh Posts</button>
        </form>
    </div>

    @if($posts->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🚀</div>
            <h3 style="margin-bottom: 1rem; color: var(--text-main);">No Posts Generated Yet</h3>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Click the button above to let the AI write your LinkedIn, Naukri, and Indeed posts based on your resume.</p>
        </div>
    @else
        <div class="grid-2">
            @foreach(['linkedin', 'naukri', 'indeed'] as $platform)
                @php 
                    $post = $posts->get($platform); 
                    $colors = [
                        'linkedin' => '#0a66c2',
                        'naukri' => '#1ea2b9',
                        'indeed' => '#2557a7'
                    ];
                    $names = [
                        'linkedin' => 'LinkedIn Post',
                        'naukri' => 'Naukri Profile Summary',
                        'indeed' => 'Indeed About Me'
                    ];
                @endphp
                
                @if($post)
                    <div class="card" style="border-top: 4px solid {{ $colors[$platform] }};">
                        <div class="card-header">
                            <div class="card-title" style="color: {{ $colors[$platform] }};">{{ $names[$platform] }}</div>
                            <span class="badge badge-{{ $post->status === 'drafted' ? 'pending' : 'running' }}">{{ ucfirst($post->status) }}</span>
                        </div>
                        
                        <form action="{{ route('social_posts.update', $post->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <textarea name="content" class="form-control" style="height: 200px; resize: vertical; font-family: inherit;">{{ $post->content }}</textarea>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
                                <button type="submit" class="btn btn-secondary btn-sm">Save Edits</button>
                                </form>
                                <form action="{{ route('social_posts.approve', $post->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" style="background: {{ $colors[$platform] }}; color: white;" {{ $post->status === 'drafted' ? '' : 'disabled' }}>
                                        {{ $post->status === 'drafted' ? 'Approve & Post' : 'Already Queued' }}
                                    </button>
                                </form>
                            </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
@endsection
