@if(count($section['items']))
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        @foreach($section['items'] as $item)
            <div class="resume-item">
                <div style="font-weight:700">{{ $item['institution_name'] }}</div>
                <div class="muted small">{{ $item['degree'] ?? '' }} @if(!empty($item['field_of_study'])) · {{ $item['field_of_study'] }} @endif</div>
                @if(!empty($item['description']))<div class="small">{{ $item['description'] }}</div>@endif
            </div>
        @endforeach
    </section>
@endif
