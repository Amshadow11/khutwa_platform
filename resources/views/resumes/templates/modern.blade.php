<!DOCTYPE html>
<html lang="{{ $resume->locale }}" dir="{{ $resume->direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resume->title }}</title>
    @include('resumes.templates.base-styles')
    <style>
        :root { --accent: {{ $settings['accent_color'] ?? '#1f6feb' }}; }
        .resume-header { border-bottom: 3px solid var(--accent); padding-bottom: 14px; }
        .resume-name { font-size: 30px; font-weight: 800; margin: 0; }
        .resume-headline { color: var(--accent); font-weight: 700; margin-top: 4px; }
        .resume-contact { display:flex; flex-wrap:wrap; gap: 10px; margin-top: 8px; color:#4b5563; font-size:12px; }
    </style>
</head>
<body>
<main class="resume-page">
    @php($identity = $snapshot['identity'] ?? [])
    <header class="resume-header">
        <h1 class="resume-name">{{ $identity['name'] ?? '' }}</h1>
        <div class="resume-headline">{{ $identity['headline'] ?? $identity['current_title'] ?? '' }}</div>
        <div class="resume-contact">
            @foreach(array_filter([$identity['email'] ?? null, $identity['phone'] ?? null, $identity['city'] ?? null]) as $contact)
                <span>{{ $contact }}</span>
            @endforeach
        </div>
    </header>
    @foreach($sections as $section)
        @includeIf("resumes.sections.{$section['key']}", ['section' => $section])
    @endforeach
</main>
</body>
</html>
