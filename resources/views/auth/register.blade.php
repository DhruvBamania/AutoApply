@extends('layouts.app')

@section('content')
<div id="register-container" style="max-width: {{ old('account_type') === 'company' ? '560px' : '440px' }}; margin: clamp(1rem, 4vw, 3rem) auto; width: 100%; transition: max-width 0.3s ease;">
    <div class="card">
        <div class="card-header" style="justify-content: center; text-align: center; flex-direction: column; gap: 0.25rem;">
            <div class="card-title" style="font-size: 1.5rem; font-weight: 700; color: #1e1b4b;">Create an Account</div>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">Choose your account type to get started</p>
        </div>
        
        <form action="{{ route('register') }}" method="POST" id="registration-form">
            @csrf
            
            <!-- Account Type Selector -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" style="font-weight: 600; margin-bottom: 0.6rem; display: block;">I am registering as:</label>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem;">
                    <!-- Option 1: Normal User -->
                    <label id="role-card-user" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.9rem 0.5rem; border: 2px solid {{ old('account_type', 'user') === 'user' ? '#4c51bf' : 'rgba(209, 213, 219, 0.8)' }}; background: {{ old('account_type', 'user') === 'user' ? 'rgba(238, 242, 255, 0.8)' : 'rgba(255,255,255,0.7)' }}; border-radius: 14px; cursor: pointer; transition: all 0.25s ease; text-align: center;">
                        <input type="radio" name="account_type" value="user" {{ old('account_type', 'user') === 'user' ? 'checked' : '' }} style="display: none;" onchange="toggleAccountType('user')">
                        <span style="font-size: 1.6rem;">👤</span>
                        <span style="font-weight: 700; font-size: 0.92rem; color: #1f2937;">Individual</span>
                        <span style="font-size: 0.75rem; color: #6b7280;">Job Seeker / Candidate</span>
                    </label>

                    <!-- Option 2: Company -->
                    <label id="role-card-company" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.9rem 0.5rem; border: 2px solid {{ old('account_type') === 'company' ? '#4c51bf' : 'rgba(209, 213, 219, 0.8)' }}; background: {{ old('account_type') === 'company' ? 'rgba(238, 242, 255, 0.8)' : 'rgba(255,255,255,0.7)' }}; border-radius: 14px; cursor: pointer; transition: all 0.25s ease; text-align: center;">
                        <input type="radio" name="account_type" value="company" {{ old('account_type') === 'company' ? 'checked' : '' }} style="display: none;" onchange="toggleAccountType('company')">
                        <span style="font-size: 1.6rem;">🏢</span>
                        <span style="font-weight: 700; font-size: 0.92rem; color: #1f2937;">Company</span>
                        <span style="font-size: 0.75rem; color: #6b7280;">Employer / Recruiter</span>
                    </label>
                </div>
                @error('account_type')
                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Standard User Credentials -->
            <div class="form-group">
                <label class="form-label" id="name-label">
                    {{ old('account_type') === 'company' ? 'Representative Full Name' : 'Full Name' }}
                </label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus placeholder="e.g. John Doe">
                @error('name')
                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" id="email-label">
                    {{ old('account_type') === 'company' ? 'Company Work Email' : 'Email Address' }}
                </label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="name@domain.com">
                @error('email')
                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Extra Company Fields (Visible only when Company is selected) -->
            <div id="company-fields" style="display: {{ old('account_type') === 'company' ? 'block' : 'none' }}; margin: 1.5rem 0; padding: 1.25rem; background: rgba(238, 242, 255, 0.45); border: 1px dashed #818cf8; border-radius: 16px;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(99, 102, 241, 0.2); padding-bottom: 0.5rem;">
                    <span style="font-size: 1.1rem;">🏢</span>
                    <strong style="color: #3730a3; font-size: 0.95rem;">Company Profile Information</strong>
                </div>

                <div class="form-group">
                    <label class="form-label">Company Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="company_name_input" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="e.g. Acme Technologies Inc.">
                    @error('company_name')
                        <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Official Website</label>
                    <input type="text" name="website" class="form-control" value="{{ old('website') }}" placeholder="e.g. https://acme.com">
                    @error('website')
                        <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Location / City</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. San Francisco, CA or Remote">
                    @error('location')
                        <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Company Description / Overview</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Briefly describe your company, products, or industry...">{{ old('description') }}</textarea>
                    @error('description')
                        <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
                @error('password')
                    <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="Re-type your password">
            </div>
            
            <button type="submit" id="submit-btn" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 0.75rem; font-size: 1rem;">
                {{ old('account_type') === 'company' ? 'Register Company Account' : 'Create Account' }}
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
            <span style="color: var(--text-muted);">Already have an account?</span>
            <a href="{{ route('login') }}" style="color: #4c51bf; font-weight: 600; text-decoration: none;">Login</a>
        </div>
    </div>
</div>

<script>
    function toggleAccountType(type) {
        const companyFields = document.getElementById('company-fields');
        const companyNameInput = document.getElementById('company_name_input');
        const userCard = document.getElementById('role-card-user');
        const companyCard = document.getElementById('role-card-company');
        const container = document.getElementById('register-container');
        const nameLabel = document.getElementById('name-label');
        const emailLabel = document.getElementById('email-label');
        const submitBtn = document.getElementById('submit-btn');

        if (type === 'company') {
            companyFields.style.display = 'block';
            companyNameInput.setAttribute('required', 'required');
            container.style.maxWidth = '560px';
            nameLabel.innerText = 'Representative Full Name';
            emailLabel.innerText = 'Company Work Email';
            submitBtn.innerText = 'Register Company Account';

            companyCard.style.borderColor = '#4c51bf';
            companyCard.style.background = 'rgba(238, 242, 255, 0.85)';
            userCard.style.borderColor = 'rgba(209, 213, 219, 0.8)';
            userCard.style.background = 'rgba(255,255,255,0.7)';
        } else {
            companyFields.style.display = 'none';
            companyNameInput.removeAttribute('required');
            container.style.maxWidth = '440px';
            nameLabel.innerText = 'Full Name';
            emailLabel.innerText = 'Email Address';
            submitBtn.innerText = 'Create Account';

            userCard.style.borderColor = '#4c51bf';
            userCard.style.background = 'rgba(238, 242, 255, 0.85)';
            companyCard.style.borderColor = 'rgba(209, 213, 219, 0.8)';
            companyCard.style.background = 'rgba(255,255,255,0.7)';
        }
    }

    // Set initial required attribute on load if company is selected
    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="account_type"]:checked');
        if (checkedRadio && checkedRadio.value === 'company') {
            document.getElementById('company_name_input').setAttribute('required', 'required');
        }
    });
</script>
@endsection
