<div class="array-item" style="border: 1px solid var(--surface-border); padding: 1rem; border-radius: 6px; margin-bottom: 1rem; background: #f8fafc;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
        <strong>Education Item</strong>
        <button type="button" class="btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="removeArrayItem(this)">Remove</button>
    </div>
    <div class="array-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
            <label>Degree</label>
            <input type="text" name="education[{{ $index }}][degree]" class="form-control" value="{{ $edu['degree'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Institution</label>
            <input type="text" name="education[{{ $index }}][institution]" class="form-control" value="{{ $edu['institution'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="education[{{ $index }}][location]" class="form-control" value="{{ $edu['location'] ?? '' }}">
        </div>
        <div class="form-group">
            <label>GPA</label>
            <input type="text" name="education[{{ $index }}][gpa]" class="form-control" value="{{ $edu['gpa'] ?? '' }}">
        </div>
        <div class="form-group array-grid-span-2" style="grid-column: span 2;">
            <label>Date</label>
            <input type="text" name="education[{{ $index }}][date]" class="form-control" value="{{ $edu['date'] ?? '' }}">
        </div>
    </div>
</div>
