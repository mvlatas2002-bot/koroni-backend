@php
    $selectedDepartment = old('department_id', $rule?->department_id);
    $selectedApprover = old('approver_id', $rule?->approver_id);
@endphp

<div class="field">
    <label>Τύπος</label>
    <select name="authority_type" required>
        @foreach ($authorityLabels as $value => $label)
            <option value="{{ $value }}" @selected(old('authority_type', $rule?->authority_type ?? 'functional_approver') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>Ποσοστό από</label>
    <input name="min_percent" type="number" min="0" max="100" step="0.01" value="{{ old('min_percent', $rule?->min_percent ?? 4) }}" required>
    <small><label><input type="checkbox" name="min_inclusive" value="1" @checked(old('min_inclusive', $rule?->min_inclusive ?? false))> περιλαμβάνει το όριο</label></small>
</div>

<div class="field">
    <label>Ποσοστό έως</label>
    <input name="max_percent" type="number" min="0" max="100" step="0.01" value="{{ old('max_percent', $rule?->max_percent) }}">
    <small><label><input type="checkbox" name="max_inclusive" value="1" @checked(old('max_inclusive', $rule?->max_inclusive ?? false))> περιλαμβάνει το όριο</label></small>
</div>

<div class="field">
    <label>Τμήμα</label>
    <select name="department_id">
        <option value="">Όλη η εταιρεία</option>
        @foreach ($departments as $department)
            <option value="{{ $department->id }}" @selected((string) $selectedDepartment === (string) $department->id)>{{ $department->name }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>Εγκριτής</label>
    <select name="approver_id">
        <option value="">Αυτόματα από ρόλο</option>
        @foreach ($approvers as $approver)
            <option value="{{ $approver->id }}" @selected((string) $selectedApprover === (string) $approver->id)>{{ $approver->name }} · {{ $approver->role?->code }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>Ρόλος fallback</label>
    <input name="required_role_code" value="{{ old('required_role_code', $rule?->required_role_code ?? 'COMMERCIAL_DIRECTOR') }}">
</div>

<div class="field">
    <label>Ετικέτα</label>
    <input name="label" value="{{ old('label', $rule?->label) }}" placeholder="π.χ. Εμπορική έγκριση">
</div>

<div class="field">
    <label>Έναρξη</label>
    <input name="effective_from" type="date" value="{{ old('effective_from', optional($rule?->effective_from)->format('Y-m-d') ?? now()->toDateString()) }}">
</div>

<div class="field">
    <label>Λήξη</label>
    <input name="effective_to" type="date" value="{{ old('effective_to', optional($rule?->effective_to)->format('Y-m-d')) }}">
</div>

<div class="field">
    <label>Κατάσταση</label>
    <select name="is_active">
        <option value="1" @selected(old('is_active', $rule?->is_active ?? true))>Ενεργός</option>
        <option value="0" @selected(! old('is_active', $rule?->is_active ?? true))>Ανενεργός</option>
    </select>
</div>
