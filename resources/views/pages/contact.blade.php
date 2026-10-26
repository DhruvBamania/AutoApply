@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Hero Header -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(238,242,255,0.98) 100%); border-left: 6px solid #4f46e5; text-align: center; padding: 2.8rem 1.5rem; position: relative; overflow: hidden;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 68px; height: 68px; border-radius: 20px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; font-size: 2rem; margin-bottom: 1rem; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.35);">
            💬
        </div>
        <h1 style="font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 800; color: #1e1b4b; margin: 0 0 0.5rem 0; letter-spacing: -0.5px;">
            Get in Touch with AutoApply & AI Looping
        </h1>
        <p style="color: #4b5563; font-size: 1.05rem; max-width: 680px; margin: 0 auto 1.5rem auto; line-height: 1.6;">
            Have questions about our autonomous AI Headhunter agent, multi-platform job syndication, or candidate matching? Our dedicated enterprise and support teams are here to help.
        </p>

        <!-- Guarantee Badges -->
        <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
            <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.82rem; padding: 0.45rem 0.9rem; border-radius: 20px; font-weight: 600;">
                ⚡ Average Response: &lt; 24 Hours
            </span>
            <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.82rem; padding: 0.45rem 0.9rem; border-radius: 20px; font-weight: 600;">
                🔒 TLS 1.3 Encrypted &amp; Confidential
            </span>
            <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.82rem; padding: 0.45rem 0.9rem; border-radius: 20px; font-weight: 600;">
                🎯 Dedicated Enterprise Recruiting Support
            </span>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 2rem; align-items: start;">
        
        <!-- Left Column: Inbound Contact Form -->
        <div class="card" style="padding: 2.2rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.9); box-shadow: 0 12px 35px rgba(31, 38, 135, 0.06); border-radius: 20px;">
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
                <span style="font-size: 1.5rem;">📝</span>
                <h2 style="font-size: 1.35rem; font-weight: 700; color: #1e293b; margin: 0;">Send Us a Message</h2>
            </div>

            <form action="{{ route('contact.submit') }}" method="POST" id="contact-form">
                @csrf
                
                <!-- Anti-spam Honeypot (hidden from real users) -->
                <div style="display: none !important; opacity: 0; position: absolute; left: -9999px;">
                    <label for="website_hp">Leave this field blank</label>
                    <input type="text" name="website_hp" id="website_hp" tabindex="-1" autocomplete="off">
                </div>

                <!-- Name & Email Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label for="name" style="display: block; font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem;">
                            Your Full Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user?->name) }}" 
                               required 
                               placeholder="e.g. Sarah Jenkins"
                               style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; transition: border-color 0.2s; outline: none; background: white;"
                               onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.15)';"
                               onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                        @error('name')
                            <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="email" style="display: block; font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem;">
                            Email Address <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user?->email) }}" 
                               required 
                               placeholder="sarah.jenkins@company.com"
                               style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; transition: border-color 0.2s; outline: none; background: white;"
                               onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.15)';"
                               onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                        @error('email')
                            <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Phone & Category Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label for="phone" style="display: block; font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem;">
                            Phone Number <span style="color: #94a3b8; font-weight: normal;">(Optional)</span>
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone') }}" 
                               placeholder="+1 (555) 000-0000"
                               style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; transition: border-color 0.2s; outline: none; background: white;"
                               onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.15)';"
                               onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                        @error('phone')
                            <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="category" style="display: block; font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem;">
                            Inquiry Type <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="category" 
                                name="category" 
                                required
                                style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; transition: border-color 0.2s; outline: none; background: white; cursor: pointer;"
                                onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.15)';"
                                onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                            <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>💬 General Inquiry</option>
                            <option value="candidate_support" {{ old('category') == 'candidate_support' ? 'selected' : '' }}>👤 Candidate &amp; Resume Support</option>
                            <option value="company_inquiry" {{ old('category') == 'company_inquiry' ? 'selected' : '' }}>🏢 Employer &amp; Job Syndication</option>
                            <option value="enterprise_sales" {{ old('category') == 'enterprise_sales' ? 'selected' : '' }}>🚀 Enterprise Sales &amp; AI Headhunter</option>
                            <option value="bug_report" {{ old('category') == 'bug_report' ? 'selected' : '' }}>🐛 Technical Support &amp; Bug Report</option>
                            <option value="legal" {{ old('category') == 'legal' ? 'selected' : '' }}>⚖️ Legal, GDPR &amp; Privacy Compliance</option>
                        </select>
                        @error('category')
                            <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Subject -->
                <div style="margin-bottom: 1.25rem;">
                    <label for="subject" style="display: block; font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem;">
                        Subject Line <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           value="{{ old('subject') }}" 
                           required 
                           placeholder="Brief summary of your inquiry"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; transition: border-color 0.2s; outline: none; background: white;"
                           onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.15)';"
                           onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                    @error('subject')
                        <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Message -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="message" style="display: block; font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem;">
                        Detailed Message <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="message" 
                              name="message" 
                              rows="6" 
                              required 
                              placeholder="Please describe how our team can help you in detail..."
                              style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; transition: border-color 0.2s; outline: none; background: white; resize: vertical; line-height: 1.5;"
                              onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.15)';"
                              onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">{{ old('message') }}</textarea>
                    @error('message')
                        <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="btn-primary" 
                        id="submit-btn"
                        style="width: 100%; padding: 0.95rem; font-size: 1.05rem; font-weight: 700; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.35); transition: transform 0.2s, box-shadow 0.2s;">
                    <span>🚀 Send Inquiry</span>
                </button>

                <p style="font-size: 0.78rem; color: #64748b; text-align: center; margin: 1rem 0 0 0; line-height: 1.4;">
                    By submitting, you agree to our <a href="{{ route('terms') }}" style="color: #4f46e5; text-decoration: underline;">Terms of Service</a> and <a href="{{ route('privacy') }}" style="color: #4f46e5; text-decoration: underline;">Privacy Policy</a>. We never share or sell your information.
                </p>
            </form>
        </div>

        <!-- Right Column: Support Channels & Interactive FAQ Accordion -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Quick Support Channels Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                
                <div class="card" style="padding: 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #0ea5e9; border-radius: 14px;">
                    <div style="font-size: 1.4rem; margin-bottom: 0.35rem;">📧</div>
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0;">Direct Email Support</h3>
                    <p style="font-size: 0.82rem; color: #475569; margin: 0 0 0.5rem 0;">Response within 24 business hours.</p>
                    <a href="mailto:support@ailooping.com" style="color: #0284c7; font-weight: 600; font-size: 0.85rem; text-decoration: none;">support@ailooping.com &rarr;</a>
                </div>

                <div class="card" style="padding: 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #10b981; border-radius: 14px;">
                    <div style="font-size: 1.4rem; margin-bottom: 0.35rem;">💼</div>
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0;">Enterprise Recruiting</h3>
                    <p style="font-size: 0.82rem; color: #475569; margin: 0 0 0.5rem 0;">Volume hiring &amp; custom ATS connectors.</p>
                    <a href="mailto:enterprise@ailooping.com" style="color: #059669; font-weight: 600; font-size: 0.85rem; text-decoration: none;">enterprise@ailooping.com &rarr;</a>
                </div>

            </div>

            <!-- Interactive FAQ Accordion -->
            <div class="card" style="padding: 1.8rem; background: rgba(255,255,255,0.95); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.4rem;">💡</span>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">Frequently Asked Questions</h2>
                    </div>
                    <span style="font-size: 0.8rem; color: #64748b; background: #f1f5f9; padding: 0.25rem 0.6rem; border-radius: 12px;">Click to expand</span>
                </div>

                <!-- FAQ Search Input -->
                <div style="margin-bottom: 1.25rem;">
                    <input type="text" 
                           id="faq-search" 
                           placeholder="🔍 Search questions (e.g. Headhunter, ATS, Privacy)..." 
                           style="width: 100%; padding: 0.65rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.88rem; outline: none; background: #f8fafc;"
                           onkeyup="filterFaqs()">
                </div>

                <!-- FAQ Groups -->
                <div id="faq-container" style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($faqs as $groupKey => $group)
                        <div class="faq-group">
                            <div style="font-size: 0.82rem; font-weight: 700; color: #4338ca; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.35rem;">
                                <span>{{ $group['icon'] }}</span> {{ $group['title'] }}
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                @foreach($group['items'] as $index => $item)
                                    <div class="faq-item" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #ffffff; transition: all 0.2s;">
                                        <button type="button" 
                                                class="faq-question-btn" 
                                                onclick="toggleFaq(this)"
                                                style="width: 100%; padding: 0.9rem 1rem; display: flex; justify-content: space-between; align-items: center; background: none; border: none; text-align: left; cursor: pointer; font-size: 0.92rem; font-weight: 600; color: #1e293b; gap: 0.75rem;">
                                            <span>{{ $item['q'] }}</span>
                                            <span class="faq-icon" style="font-size: 1.1rem; color: #6366f1; transition: transform 0.25s ease; flex-shrink: 0;">+</span>
                                        </button>
                                        <div class="faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; background: #f8fafc; border-top: 1px solid transparent;">
                                            <p style="padding: 0.9rem 1rem; margin: 0; font-size: 0.88rem; color: #475569; line-height: 1.55;">
                                                {{ $item['a'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Corporate & Compliance Summary -->
            <div class="card" style="padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 14px; font-size: 0.85rem; color: #0369a1;">
                <strong style="display: block; margin-bottom: 0.25rem; color: #0c4a6e;">🏢 Enterprise &amp; Compliance Office</strong>
                <p style="margin: 0; line-height: 1.45; color: #075985;">
                    AutoApply Intelligence Systems &bull; Thaltej, &bull; Ahmedabad, 380063 &bull; India<br>
                    Data Protection Officer: <a href="mailto:privacy@ailooping.com" style="color: #0369a1; font-weight: 600;">denbamania2005@gmail.com</a>
                </p>
            </div>

        </div>

    </div>

</div>

<!-- FAQ Interactivity Script -->
<script>
    function toggleFaq(btn) {
        const item = btn.closest('.faq-item');
        const answer = item.querySelector('.faq-answer');
        const icon = btn.querySelector('.faq-icon');
        const isOpen = answer.style.maxHeight && answer.style.maxHeight !== '0px';

        // Close all answers in this container for accordion behavior
        document.querySelectorAll('.faq-answer').forEach(ans => {
            ans.style.maxHeight = '0px';
            ans.style.borderTopColor = 'transparent';
        });
        document.querySelectorAll('.faq-icon').forEach(ic => {
            ic.textContent = '+';
            ic.style.transform = 'rotate(0deg)';
        });

        if (!isOpen) {
            answer.style.maxHeight = answer.scrollHeight + 'px';
            answer.style.borderTopColor = '#e2e8f0';
            icon.textContent = '−';
            icon.style.transform = 'rotate(180deg)';
        }
    }

    function filterFaqs() {
        const query = document.getElementById('faq-search').value.toLowerCase().trim();
        const items = document.querySelectorAll('.faq-item');

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endsection
