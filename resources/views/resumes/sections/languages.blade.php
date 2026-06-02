@if(count($section['items']))
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        <div class="skill-list">
            @foreach($section['items'] as $item)
                <span class="skill-pill">{{ $item['native_name'] ?: $item['name'] }} @if(!empty($item['level'])) · {{ $item['level'] }} @endif</span>
            @endforeach
        </div>
    </section>
@endif
