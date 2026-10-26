<div class="array-item" style="border: 1px solid var(--surface-border); padding: 1rem; border-radius: 6px; margin-bottom: 1rem; background: #f8fafc;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
        <strong>Project Item</strong>
        <button type="button" class="btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="removeArrayItem(this)">Remove</button>
    </div>
    <div class="array-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="projects[{{ $index }}][title]" class="form-control" value="{{ $proj['title'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Link / URL</label>
            <input type="text" name="projects[{{ $index }}][link]" class="form-control" value="{{ $proj['link'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Tech Stack</label>
            <input type="text" name="projects[{{ $index }}][tech_stack]" class="form-control" value="{{ $proj['tech_stack'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="text" name="projects[{{ $index }}][date]" class="form-control" value="{{ $proj['date'] ?? '' }}">
        </div>
    </div>
    <div class="form-group" style="margin-top: 1rem;">
        <label>Description (Bullet points)</label>
        <textarea name="projects[{{ $index }}][description]" class="form-control" rows="4">{{ $proj['description'] ?? '' }}</textarea>
    </div>
</div>
