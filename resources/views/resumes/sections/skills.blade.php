@if(count($section['items']))
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        <div class="skill-list">
            @foreach($section['items'] as $skill)
                <span class="skill-pill">
                    {{ $skill['name'] }}
                    @if(!empty($skill['years'])) · {{ $skill['years'] }}y @endif
                </span>
            @endforeach
        </div>
    </section>
@endif
