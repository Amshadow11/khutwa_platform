@if(count($section['items']))
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        @foreach($section['items'] as $item)
            <div class="resume-item">
                <div style="font-weight:700">{{ $item['name'] }}</div>
                <div class="muted small">{{ $item['issuing_organization'] ?? '' }} @if(!empty($item['issued_at'])) · {{ $item['issued_at'] }} @endif</div>
            </div>
        @endforeach
    </section>
@endif
