@if(count($section['items']))
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        @foreach($section['items'] as $item)
            <div class="resume-item">
                <div style="font-weight:700">{{ $item['title'] }}</div>
                <div class="muted small">
                    {{ $item['company_name'] ?? '' }}
                    @if(!empty($item['location'])) · {{ $item['location'] }} @endif
                    · {{ $item['start_date'] ?? 'Present' }} - {{ ($item['is_current'] ?? false) ? 'Present' : ($item['end_date'] ?? 'Present') }}
                </div>
                @if(!empty($item['summary']))
                    <div class="small" style="margin-top:4px; white-space:pre-wrap">{{ $item['summary'] }}</div>
                @endif
            </div>
        @endforeach
    </section>
@endif
