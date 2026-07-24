<!-- Section 1 -->
<div class="guide-card">

    <span class="guide-badge">
        {{ $content->author_badge }}
    </span>

    <h2>{{ $content->author_heading }}</h2>

    <p>
        {!! $content->author_description !!}
    </p>
</div>

<!-- Section 2 -->
<div class="guide-card">
    <span class="guide-badge">{{ $content->process_badge }}</span>

    <h2>{{ $content->process_heading }}</h2>

    <p>
        {!! $content->process_description !!}
    </p>
</div>

<!-- Section 3 -->
<div class="guide-card">
    <span class="guide-badge">{{ $content->manuscript_badge }}</span>

    <h2>{{ $content->manuscript_heading }}</h2>

    <p>{!! $content->manuscript_description !!}</p>

</div>

<!-- Section 4 -->
<div class="guide-card">
    <span class="guide-badge">{{ $content->formatting_badge1 }}</span>

    <h2>{{ $content->formatting_heading }}</h2>

    <p>{{ $content->formatting_description }}</p>

    <div class="highlight-box">{{ $content->formatting_badge2 }}</div>
</div>

<!-- Section 5 -->
<div class="guide-card">
    <span class="guide-badge">{{ $content->layout_badge1 }}</span>

    <h2>{{ $content->layout_heading }}</h2>

    <p>{!! $content->layout_description !!}</p>
</div>


<!-- Section 6 -->
<div class="guide-card">
    <span class="guide-badge">{{ $content->acknowlegdement_badge1 }}</span>

    <h2>{{$content -> acknowlegdement_heading}}</h2>

    <p>{!!$content -> acknowlegdement_description!!}</p>
</div>