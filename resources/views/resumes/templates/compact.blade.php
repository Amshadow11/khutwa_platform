<!DOCTYPE html>
<html lang="{{ $resume->locale }}" dir="{{ $resume->direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resume->title }}</title>
    @include('resumes.templates.base-styles')
    <style>
        :root { --accent: #0f172a; }
        body { line-height: 1.35; }
        .resume-page { padding: 12mm 14mm; }
        .resume-name { font-size: 24px; font-weight: 800; margin: 0; }
        .resume-section { margin-top: 12px; }
        .resume-section h2 { color:#0f172a; font-size:12px; text-transform:uppercase; border-bottom:1px solid #e5e7eb; padding-bottom:3px; }
        .resume-item { margin-bottom: 7px; }
        .skill-pill { border:0; padding:0; margin-inline-end:8px; }
    </style>
</head>
<body>
<main class="resume-page">
    @php($identity = $snapshot['identity'] ?? [])
    <header>
        <h1 class="resume-name">{{ $identity['name'] ?? '' }}</h1>
        <div class="muted small">
            {{ collect([$identity['headline'] ?? null, $identity['email'] ?? null, $identity['phone'] ?? null])->filter()->implode(' · ') }}
        </div>
    </header>
    @foreach($sections as $section)
        @includeIf("resumes.sections.{$section['key']}", ['section' => $section])
    @endforeach
</main>
</body>
</html>
