<!DOCTYPE html>
<html lang="{{ $resume->locale }}" dir="{{ $resume->direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resume->title }}</title>
    @include('resumes.templates.base-styles')
    <style>
        :root { --accent: {{ $settings['accent_color'] ?? '#111827' }}; }
        .resume-page { padding: 16mm 18mm; }
        .resume-name { text-align:center; font-size: 28px; font-weight: 800; margin: 0; }
        .resume-contact { text-align:center; color:#4b5563; font-size:12px; margin-top: 6px; }
        .resume-section h2 { color:#111827; border-bottom:1px solid #d1d5db; padding-bottom:4px; }
    </style>
</head>
<body>
<main class="resume-page">
    @php($identity = $snapshot['identity'] ?? [])
    <header>
        <h1 class="resume-name">{{ $identity['name'] ?? '' }}</h1>
        <div class="resume-contact">
            {{ collect([$identity['email'] ?? null, $identity['phone'] ?? null, $identity['city'] ?? null])->filter()->implode(' · ') }}
        </div>
    </header>
    @foreach($sections as $section)
        @includeIf("resumes.sections.{$section['key']}", ['section' => $section])
    @endforeach
</main>
</body>
</html>
