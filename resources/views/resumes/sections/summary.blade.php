@if($section['items'])
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        <p style="margin:0">{{ $section['items'] }}</p>
    </section>
@endif
