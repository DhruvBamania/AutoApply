<!-- AI Headhunter Outreach Modal -->
<div id="headhunterOutreachModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 620px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 50%, #4f46e5 100%); padding: 1.25rem 1.5rem; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    🎯
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #ffffff;">Review & Send AI Headhunter Outreach</h3>
                    <p style="margin: 0.15rem 0 0; font-size: 0.8rem; color: #e0f2fe;">Personalized pitch crafted by AI Headhunter Agent</p>
                </div>
            </div>
            <button type="button" onclick="closeHeadhunterModal()" style="background: transparent; border: none; color: #ffffff; font-size: 1.5rem; cursor: pointer; padding: 0.25rem 0.5rem; line-height: 1; border-radius: 6px; opacity: 0.85; transition: opacity 0.15s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.85'">
                &times;
            </button>
        </div>

        <!-- Candidate Context Bar -->
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">Candidate</span>
                <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;" id="hhModalCandidateName">-</div>
            </div>
            <div>
                <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">Match Score</span>
                <div>
                    <span id="hhModalMatchScore" style="display: inline-block; padding: 2px 10px; border-radius: 20px; font-weight: 800; font-size: 0.82rem; background: #dcfce7; color: #15803d;">92% Match</span>
                </div>
            </div>
            <div>
                <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">Pool Source</span>
                <div style="font-weight: 600; color: #0284c7; font-size: 0.85rem;" id="hhModalPoolSource">Platform Candidate</div>
            </div>
        </div>

        <!-- Form -->
        <form id="headhunterInviteForm" method="POST" style="margin: 0; padding: 1.5rem;">
            @csrf

            <!-- Candidate Email (Display) -->
            <div style="margin-bottom: 1.15rem;">
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.35rem;">
                    📬 Recipient Email
                </label>
                <input type="text" id="hhModalCandidateEmail" readonly style="width: 100%; box-sizing: border-box; padding: 0.6rem 0.85rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; background: #f8fafc; color: #475569;">
            </div>

            <!-- Email Subject -->
            <div style="margin-bottom: 1.15rem;">
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.35rem;">
                    ✉️ Outreach Subject <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="outreach_subject" id="hhModalSubject" required style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; font-family: inherit; color: #0f172a; transition: border-color 0.15s;" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'">
            </div>

            <!-- Personalized Pitch / Message -->
            <div style="margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        ✨ Personalized Pitch / Message <span style="color: #ef4444;">*</span>
                    </label>
                    <span style="font-size: 0.75rem; color: #6366f1; font-weight: 600;">AI Tailored</span>
                </div>
                <textarea name="outreach_message" id="hhModalMessage" rows="7" required style="width: 100%; box-sizing: border-box; padding: 0.75rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; font-family: inherit; color: #0f172a; line-height: 1.5; resize: vertical; transition: border-color 0.15s;" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'"></textarea>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
                    Feel free to personalize this note before dispatching. A secure 1-click accept button will be attached automatically in their email.
                </div>
            </div>

            <!-- Notice Callout -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.75rem 0.9rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.6rem;">
                <span style="font-size: 1.1rem; line-height: 1;">⚡</span>
                <div style="font-size: 0.8rem; color: #166534; line-height: 1.4;">
                    <strong>Seamless Automation:</strong> When the candidate clicks "Accept & Connect" from their email or mobile app, they will instantly appear in your <strong>Job Shortlist</strong> tab ready for interview scheduling.
                </div>
            </div>

            <!-- Modal Actions -->
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <button type="button" onclick="closeHeadhunterModal()" style="padding: 0.6rem 1.1rem; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">
                    Cancel
                </button>
                <button type="submit" id="hhSubmitBtn" style="padding: 0.6rem 1.4rem; border: none; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); color: #ffffff; border-radius: 8px; font-size: 0.9rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); transition: all 0.15s;">
                    ✉️ Dispatch Invitation Email
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openHeadhunterModal(matchId, candidateName, candidateEmail, matchScore, poolSource, defaultSubject, defaultMessage) {
        const modal = document.getElementById('headhunterOutreachModal');
        const form = document.getElementById('headhunterInviteForm');
        
        form.action = "{{ url('company/headhunter') }}/" + matchId + "/invite";
        
        document.getElementById('hhModalCandidateName').innerText = candidateName || 'Candidate';
        document.getElementById('hhModalCandidateEmail').value = candidateEmail || 'Simulated External Contact';
        
        const scoreBadge = document.getElementById('hhModalMatchScore');
        const score = parseInt(matchScore) || 0;
        scoreBadge.innerText = score + '% Match';
        if (score >= 80) {
            scoreBadge.style.background = '#dcfce7';
            scoreBadge.style.color = '#15803d';
        } else if (score >= 60) {
            scoreBadge.style.background = '#e0f2fe';
            scoreBadge.style.color = '#0369a1';
        } else {
            scoreBadge.style.background = '#fef3c7';
            scoreBadge.style.color = '#b45309';
        }

        const sourceLabel = poolSource === 'platform' ? '👤 Registered Platform Candidate' : '🌐 External Tech Talent Pool';
        document.getElementById('hhModalPoolSource').innerText = sourceLabel;

        document.getElementById('hhModalSubject').value = defaultSubject || ('Career Opportunity: ' + "{{ addslashes($job->title) }}");
        document.getElementById('hhModalMessage').value = defaultMessage || '';

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeHeadhunterModal() {
        const modal = document.getElementById('headhunterOutreachModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close when clicking backdrop
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('headhunterOutreachModal');
        if (e.target === modal) {
            closeHeadhunterModal();
        }
    });
</script>
