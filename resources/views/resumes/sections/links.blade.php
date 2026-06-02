@if(count($section['items']))
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        <div class="links small">
            @foreach($section['items'] as $label => $url)
                <span>{{ ucfirst($label) }}: {{ $url }}</span>
            @endforeach
        </div>
    </section>
@endif
