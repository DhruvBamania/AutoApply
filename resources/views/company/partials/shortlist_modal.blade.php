<!-- Shortlist & Interview Schedule Modal -->
<div id="shortlistModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 580px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); padding: 1.25rem 1.5rem; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    ⭐
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #ffffff;">Shortlist & Schedule Interview</h3>
                    <p style="margin: 0.15rem 0 0; font-size: 0.8rem; color: #e0e7ff;">Enter interview details to notify the candidate automatically</p>
                </div>
            </div>
            <button type="button" onclick="closeShortlistModal()" style="background: transparent; border: none; color: #ffffff; font-size: 1.5rem; cursor: pointer; padding: 0.25rem 0.5rem; line-height: 1; border-radius: 6px; opacity: 0.85; transition: opacity 0.15s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.85'">
                &times;
            </button>
        </div>

        <!-- Candidate Context Bar -->
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">Candidate</span>
                <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;" id="modalCandidateName">-</div>
            </div>
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">Position</span>
                <div style="font-weight: 600; color: #4338ca; font-size: 0.88rem;" id="modalJobTitle">-</div>
            </div>
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">Email</span>
                <div style="font-weight: 600; color: #334155; font-size: 0.88rem;" id="modalCandidateEmail">-</div>
            </div>
        </div>

        <!-- Form -->
        <form id="shortlistForm" method="POST" style="margin: 0; padding: 1.5rem;">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="shortlisted">

            <!-- Date & Time Input (Required) -->
            <div style="margin-bottom: 1.15rem;">
                <label style="display: block; font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 0.35rem;">
                    📅 Interview Date & Time <span style="color: #ef4444;">*</span>
                </label>
                <input type="datetime-local" name="interview_scheduled_at" id="modalInterviewScheduledAt" required style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; font-family: inherit; color: #0f172a; transition: border-color 0.15s;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.3rem;">
                    This date & time will be highlighted in the candidate's email.
                </div>
            </div>

            <!-- Meeting Mode -->
            <div style="margin-bottom: 1.15rem;">
                <label style="display: block; font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 0.35rem;">
                    🎯 Meeting Format / Platform
                </label>
                <select name="meeting_type" id="modalMeetingType" style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; font-family: inherit; color: #0f172a; background: #ffffff;">
                    <option value="google_meet">💻 Google Meet (Online Video)</option>
                    <option value="zoom">🎥 Zoom Meeting</option>
                    <option value="in_person">🏢 In-Person (Office Visit)</option>
                    <option value="phone">📞 Phone Call</option>
                </select>
            </div>

            <!-- Meeting Link / Address -->
            <div style="margin-bottom: 1.15rem;">
                <label style="display: block; font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 0.35rem;">
                    📍 Meeting Link or Office Address
                </label>
                <input type="text" name="meeting_link_or_location" id="modalMeetingLink" placeholder="e.g. https://meet.google.com/abc-defg-hij or 123 Tech Park, 4th Floor" style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; font-family: inherit; color: #0f172a; transition: border-color 0.15s;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
            </div>

            <!-- Special Instructions / Notes -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 0.35rem;">
                    📝 Recruiter Notes / Instructions for Candidate <span style="font-weight: 400; color: #64748b;">(Optional)</span>
                </label>
                <textarea name="interview_notes" id="modalInterviewNotes" rows="3" placeholder="e.g. Please be prepared to walk through your recent projects. Ensure good audio/video setup." style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; font-family: inherit; color: #0f172a; resize: vertical; transition: border-color 0.15s;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'"></textarea>
            </div>

            <!-- Automated Email Checkbox & Notice -->
            <div id="emailNoticeBox" style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem;">
                <input type="checkbox" name="send_email" id="modalSendEmail" value="1" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: #4f46e5; cursor: pointer;">
                <div>
                    <label for="modalSendEmail" style="font-size: 0.88rem; font-weight: 700; color: #3730a3; cursor: pointer; display: block;">
                        ✉️ Automatically send interview invitation email to candidate
                    </label>
                    <div style="font-size: 0.78rem; color: #4338ca; margin-top: 0.2rem;" id="emailTargetText">
                        Will be delivered directly to the candidate's registered email address.
                    </div>
                </div>
            </div>

            <div id="noEmailWarningBox" style="display: none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; color: #92400e; font-size: 0.85rem;">
                ⚠️ <strong>Note:</strong> This candidate has no email address on file. The interview schedule will be saved to your dashboard, but no email can be sent.
            </div>

            <!-- Modal Actions -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem;">
                <button type="button" onclick="closeShortlistModal()" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" id="shortlistSubmitBtn" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; border: none; padding: 0.65rem 1.5rem; border-radius: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); display: inline-flex; align-items: center; gap: 0.5rem;">
                    <span>⭐ Confirm Shortlist & Send Email</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalPop {
    from {
        transform: scale(0.95);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}
</style>

<script>
let currentTriggerSelect = null;

function openShortlistModal(inquiryId, candidateName, candidateEmail, jobTitle, scheduledAt = '', meetingType = 'google_meet', meetingLink = '', notes = '') {
    const modal = document.getElementById('shortlistModal');
    const form = document.getElementById('shortlistForm');

    // Set form action
    form.action = `/company/inquiries/${inquiryId}/status`;

    // Populate context
    document.getElementById('modalCandidateName').textContent = candidateName || 'Candidate';
    document.getElementById('modalJobTitle').textContent = jobTitle || 'Position';
    document.getElementById('modalCandidateEmail').textContent = candidateEmail || 'No email provided';

    // Populate fields
    const dateInput = document.getElementById('modalInterviewScheduledAt');
    if (scheduledAt) {
        dateInput.value = scheduledAt;
    } else {
        // Default to tomorrow 10:00 AM
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(10, 0, 0, 0);
        const yyyy = tomorrow.getFullYear();
        const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const dd = String(tomorrow.getDate()).padStart(2, '0');
        const hh = String(tomorrow.getHours()).padStart(2, '0');
        const min = String(tomorrow.getMinutes()).padStart(2, '0');
        dateInput.value = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
    }

    // Set min to current time
    const now = new Date();
    const nowYyyy = now.getFullYear();
    const nowMm = String(now.getMonth() + 1).padStart(2, '0');
    const nowDd = String(now.getDate()).padStart(2, '0');
    const nowHh = String(now.getHours()).padStart(2, '0');
    const nowMin = String(now.getMinutes()).padStart(2, '0');
    dateInput.min = `${nowYyyy}-${nowMm}-${nowDd}T${nowHh}:${nowMin}`;

    document.getElementById('modalMeetingType').value = meetingType || 'google_meet';
    document.getElementById('modalMeetingLink').value = meetingLink || '';
    document.getElementById('modalInterviewNotes').value = notes || '';

    // Handle email visibility
    const emailBox = document.getElementById('emailNoticeBox');
    const noEmailBox = document.getElementById('noEmailWarningBox');
    const emailTargetText = document.getElementById('emailTargetText');

    if (candidateEmail && candidateEmail.trim().length > 0) {
        emailBox.style.display = 'flex';
        noEmailBox.style.display = 'none';
        emailTargetText.textContent = `Will be delivered directly to ${candidateEmail}`;
        document.getElementById('modalSendEmail').checked = true;
    } else {
        emailBox.style.display = 'none';
        noEmailBox.style.display = 'block';
        document.getElementById('modalSendEmail').checked = false;
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeShortlistModal() {
    const modal = document.getElementById('shortlistModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';

    // If triggered by a select dropdown, reset it back to previous value
    if (currentTriggerSelect) {
        const prev = currentTriggerSelect.dataset.prevStatus || 'new';
        currentTriggerSelect.value = prev;
        currentTriggerSelect = null;
    }
}

function handleStatusChange(selectElement, inquiryId, candidateName, candidateEmail, jobTitle, scheduledAt = '', meetingType = 'google_meet', meetingLink = '', notes = '') {
    if (selectElement.value === 'shortlisted') {
        currentTriggerSelect = selectElement;
        openShortlistModal(inquiryId, candidateName, candidateEmail, jobTitle, scheduledAt, meetingType, meetingLink, notes);
    } else {
        selectElement.dataset.prevStatus = selectElement.value;
        selectElement.form.submit();
    }
}

// Close on backdrop click or ESC
window.addEventListener('click', function(e) {
    const modal = document.getElementById('shortlistModal');
    if (e.target === modal) {
        closeShortlistModal();
    }
});

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('shortlistModal');
        if (modal && modal.style.display === 'flex') {
            closeShortlistModal();
        }
    }
});
</script>
