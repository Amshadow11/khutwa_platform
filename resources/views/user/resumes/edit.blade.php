@extends('layouts.app')
@section('title', 'Resume Editor')

@push('styles')
<style>
    .resume-editor-page { padding: 1.25rem 0 2rem; }
    .editor-shell { display: grid; grid-template-columns: minmax(0, 520px) minmax(420px, 1fr); gap: 1rem; align-items: start; }
    .editor-panel { background:#fff; border-radius:12px; padding:1rem; box-shadow:0 2px 12px rgba(0,0,0,.06); }
    .preview-panel { position: sticky; top: 86px; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.06); }
    .preview-frame { width:100%; height: calc(100vh - 130px); border:0; background:#fff; }
    .status-pill { border-radius:999px; padding:.25rem .65rem; font-size:.78rem; background:#eef2ff; color:#2C5AA0; display:inline-flex; }
    .section-row { display:grid; grid-template-columns: 28px minmax(0, 1fr); gap:.65rem; border:1px solid #edf0f4; border-radius:10px; padding:.75rem; margin-top:.65rem; background:#fff; }
    .drag-handle { cursor:grab; color:#9ca3af; display:flex; align-items:center; justify-content:center; border-radius:8px; background:#f8fafc; }
    .section-row.dragging { opacity:.55; }
    .section-key { font-size:.72rem; color:#6b7280; text-transform:uppercase; }
    .compact-input { max-width: 92px; }
    @media (max-width: 1100px) { .editor-shell { grid-template-columns: 1fr; } .preview-panel { position: static; } .preview-frame { height: 720px; } }
</style>
@endpush

@section('content')
<div class="resume-editor-page">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="fw-bold mb-1">Resume Editor</h4>
                <div class="text-muted small">
                    Snapshot v{{ $resume->snapshot_version }} · PDF:
                    <span class="status-pill">{{ $resume->pdf_status ?? 'not_generated' }}</span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('user.resumes.show', $resume) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Full preview</a>
                @if($resume->shareUrl())
                    <a href="{{ $resume->shareUrl() }}" class="btn btn-outline-success btn-sm rounded-pill px-3">Share URL</a>
                @endif
            </div>
        </div>

        @if($resume->pdf_error)
            <div class="alert alert-warning">
                <div class="fw-bold">آخر خطأ في توليد PDF</div>
                <div class="small">{{ $resume->pdf_error }}</div>
            </div>
        @endif

        <div class="editor-shell">
            <div class="editor-panel">
                @if($diff['has_changes'])
                    <div class="alert alert-info">
                        <div class="fw-bold mb-1">بيانات البروفايل تغيّرت بعد إنشاء هذه النسخة</div>
                        <div class="small mb-2">
                            @foreach($diff['changes'] as $key => $change)
                                @if($change['changed'])
                                    <span class="me-2">{{ $key }}: {{ $change['saved_count'] }} → {{ $change['current_count'] }}</span>
                                @endif
                            @endforeach
                        </div>
                        <form action="{{ route('user.resumes.refresh-snapshot', $resume) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-primary rounded-pill px-3">Update from Profile</button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-light border">
                        <span class="small text-muted">Snapshot مطابق لبيانات البروفايل الحالية.</span>
                    </div>
                @endif

                <form action="{{ route('user.resumes.update', $resume) }}" method="POST" id="resumeEditorForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">عنوان السيرة</label>
                            <input name="title" class="form-control" value="{{ old('title', $resume->title) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">القالب</label>
                            <select name="template_id" class="form-select" required>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" @selected((int) old('template_id', $resume->template_id) === $template->id)>
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الظهور</label>
                            <select name="visibility" class="form-select">
                                @foreach(['private' => 'خاص', 'public' => 'عام', 'unlisted' => 'برابط فقط', 'signed' => 'رابط موقّع'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('visibility', $resume->visibility) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">اللغة</label>
                            <input name="locale" class="form-control" value="{{ old('locale', $resume->locale) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الاتجاه</label>
                            <select name="direction" class="form-select">
                                <option value="rtl" @selected(old('direction', $resume->direction) === 'rtl')>RTL</option>
                                <option value="ltr" @selected(old('direction', $resume->direction) === 'ltr')>LTR</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">ملخص مخصص لهذه النسخة</label>
                            <textarea name="tailored_summary" rows="4" class="form-control">{{ old('tailored_summary', $resume->tailored_summary) }}</textarea>
                            <div class="form-text">تغيير الملخص ينشئ Snapshot جديدًا لهذه السيرة فقط.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <div class="fw-bold">الأقسام</div>
                            <div class="text-muted small">اسحب الأقسام لتغيير الترتيب، واضبط عدد العناصر أو إظهار العناصر المميزة فقط.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="refreshPreview">Refresh preview</button>
                    </div>

                    <div id="sectionList">
                        @foreach($resume->sections as $section)
                            <div class="section-row" draggable="true" data-section-row>
                                <div class="drag-handle" title="Drag">☰</div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="section-key">{{ $section->section_key }}</div>
                                            <input name="sections[{{ $section->id }}][title]" class="form-control form-control-sm mt-1" value="{{ old("sections.{$section->id}.title", $section->title) }}">
                                        </div>
                                        <input type="number" name="sections[{{ $section->id }}][sort_order]" class="form-control form-control-sm compact-input" value="{{ old("sections.{$section->id}.sort_order", $section->sort_order) }}" data-sort-input>
                                    </div>
                                    <div class="d-flex gap-3 flex-wrap mt-2">
                                        <label class="form-check small">
                                            <input type="checkbox" name="sections[{{ $section->id }}][is_visible]" value="1" class="form-check-input" @checked(old("sections.{$section->id}.is_visible", $section->is_visible))>
                                            <span class="form-check-label">ظاهر</span>
                                        </label>
                                        <label class="form-check small">
                                            <input type="checkbox" name="sections[{{ $section->id }}][featured_only]" value="1" class="form-check-input" @checked(old("sections.{$section->id}.featured_only", $section->settings['featured_only'] ?? false))>
                                            <span class="form-check-label">Featured only</span>
                                        </label>
                                        <label class="small d-flex align-items-center gap-2">
                                            <span class="text-muted">Limit</span>
                                            <input type="number" name="sections[{{ $section->id }}][limit]" min="0" max="50" class="form-control form-control-sm compact-input" value="{{ old("sections.{$section->id}.limit", $section->settings['limit'] ?? 0) }}">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary rounded-pill px-4">حفظ الإعدادات</button>
                        <a href="{{ route('user.resumes.show', $resume) }}" class="btn btn-outline-secondary rounded-pill px-4">إلغاء</a>
                    </div>
                </form>
            </div>

            <aside class="preview-panel">
                <iframe class="preview-frame" id="resumePreviewFrame" src="{{ route('user.resumes.preview', $resume) }}"></iframe>
            </aside>
        </div>
    </div>
</div>

@push('scripts')
<script>
const list = document.getElementById('sectionList');
const refreshPreview = document.getElementById('refreshPreview');
const previewFrame = document.getElementById('resumePreviewFrame');

function updateSortInputs() {
    [...list.querySelectorAll('[data-section-row]')].forEach((row, index) => {
        row.querySelector('[data-sort-input]').value = index + 1;
    });
}

list.addEventListener('dragstart', event => {
    const row = event.target.closest('[data-section-row]');
    if (!row) return;
    row.classList.add('dragging');
});

list.addEventListener('dragend', event => {
    const row = event.target.closest('[data-section-row]');
    if (row) row.classList.remove('dragging');
    updateSortInputs();
});

list.addEventListener('dragover', event => {
    event.preventDefault();
    const dragging = list.querySelector('.dragging');
    const after = [...list.querySelectorAll('[data-section-row]:not(.dragging)')]
        .find(row => event.clientY <= row.getBoundingClientRect().top + row.offsetHeight / 2);
    if (!dragging) return;
    if (after) list.insertBefore(dragging, after);
    else list.appendChild(dragging);
});

refreshPreview?.addEventListener('click', () => {
    previewFrame.src = previewFrame.src.split('?')[0] + '?t=' + Date.now();
});
</script>
@endpush
@endsection
