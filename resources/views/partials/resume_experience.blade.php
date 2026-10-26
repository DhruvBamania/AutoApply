<div class="array-item" style="border: 1px solid var(--surface-border); padding: 1rem; border-radius: 6px; margin-bottom: 1rem; background: #f8fafc;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
        <strong>Experience Item</strong>
        <button type="button" class="btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="removeArrayItem(this)">Remove</button>
    </div>
    <div class="array-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="experience[{{ $index }}][title]" class="form-control" value="{{ $exp['title'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Company</label>
            <input type="text" name="experience[{{ $index }}][company]" class="form-control" value="{{ $exp['company'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Date / Duration</label>
            <input type="text" name="experience[{{ $index }}][date]" class="form-control" value="{{ $exp['date'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="experience[{{ $index }}][location]" class="form-control" value="{{ $exp['location'] ?? '' }}">
        </div>
    </div>
    <div class="form-group" style="margin-top: 1rem;">
        <label>Description (Bullet points)</label>
        <textarea name="experience[{{ $index }}][description]" class="form-control" rows="4">{{ $exp['description'] ?? '' }}</textarea>
    </div>
</div>
