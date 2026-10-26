@extends('layouts.app')

@section('content')
<div style="max-width: 420px; width: 100%; margin: clamp(1rem, 5vw, 4rem) auto;">
    <div class="card">
        <div class="card-header" style="justify-content: center;">
            <div class="card-title">Welcome Back</div>
        </div>
        
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
            <span style="color: var(--text-muted);">Don't have an account?</span>
            <a href="{{ route('register') }}" style="color: var(--primary-color); text-decoration: none;">Sign Up</a>
        </div>
    </div>
</div>
@endsection
