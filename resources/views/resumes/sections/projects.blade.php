@if(count($section['items']))
    <section class="resume-section">
        <h2>{{ $section['title'] }}</h2>
        @foreach($section['items'] as $item)
            <div class="resume-item">
                <div style="font-weight:700">{{ $item['title'] }}</div>
                @if(!empty($item['description']))<div class="small">{{ $item['description'] }}</div>@endif
                <div class="links small muted">
                    @if(!empty($item['project_url']))<span>{{ $item['project_url'] }}</span>@endif
                    @if(!empty($item['repository_url']))<span>{{ $item['repository_url'] }}</span>@endif
                </div>
            </div>
        @endforeach
    </section>
@endif
