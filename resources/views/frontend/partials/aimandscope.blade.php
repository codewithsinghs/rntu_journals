<section class="aim_scope_section">
    <div class="s__container_custom">
        <div class="row align-items-center gy-5">

            <!-- LEFT IMAGE -->
            <div class="col-lg-6">
                <div class="journal_image_wrapper">
                    <div class="bg_journal">
                        @if ($content && $content->aim_section_image)
                            <img src="{{ asset('storage/' . $content->aim_section_image) }}" alt="Aim and Scope">
                        @endif
                    </div>
                </div>
            </div>

            <!-- RIGHT CONTENT -->
            <div class="col-lg-6">
                <div class="aim_scope_content">

                    @if ($content && $content->aim_and_scope_title_1)
                        <span class="journal_tag">{{ $content->aim_and_scope_title_1 }}</span>
                    @endif

                    @if ($content && $content->aim_and_scope_title_2)
                        <h2>{{ $content->aim_and_scope_title_2 }}</h2>
                    @endif

                    @if ($content && $content->aim_and_scope_description)
                        <p>{!! $content->aim_and_scope_description !!}</p>
                    @endif

                    @if ($content && $content->aim_and_scope_title_3)
                        <h3>{{ $content->aim_and_scope_title_3 }}</h3>
                    @endif

                    @if ($content && $content->scope_of_publication_description)
                        <p>{!! $content->scope_of_publication_description !!}</p>
                    @endif

                    @if ($content && $content->university_highlight_quote)
                        <div class="quote_box">
                            <div class="quote_line"></div>
                            <p>{!! $content->university_highlight_quote !!}</p>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</section>


{{-- ═══ Why RNTU Section ════════════════════════════════════════ --}}


<section class="why_rntu_section">
    <div class="s__container_custom">

        <div class="section_top text-center">

            @if ($content && $content->why_rntu_title_1)
                <span class="section_tag">
                    {{ $content->why_rntu_title_1 }}
                </span>
            @endif

            @if ($content && $content->why_rntu_title_2)
                <h2>
                    {{ $content->why_rntu_title_2 }}
                </h2>
            @endif

        </div>

        {{-- Features --}}
        <div class="why_features">

            @if ($content && $content->why_rntu_years)
                <div class="feature_box">
                    <div class="feature_icon">
                        <img src="{{ asset('storage/home_page/why_1.png') }}" alt="Years">
                    </div>
                    <h4>{{ $content->why_rntu_years }}</h4>
                    <p>{{ $content->why_rntu_years_label }}</p>
                </div>
            @endif

            @if ($content && $content->why_rntu_articles)
                <div class="feature_box">
                    <div class="feature_icon">
                        <img src="{{ asset('storage/home_page/why_2.png') }}" alt="Articles">
                    </div>
                    <h4>{{ $content->why_rntu_articles }}</h4>
                    <p>{{ $content->why_rntu_articles_label }}</p>
                </div>
            @endif

            @if ($content && $content->why_rntu_journals)
                <div class="feature_box">
                    <div class="feature_icon">
                        <img src="{{ asset('storage/home_page/why_3.png') }}" alt="Journals">
                    </div>
                    <h4>{{ $content->why_rntu_journals }}</h4>
                    <p>{{ $content->why_rntu_journals_label }}</p>
                </div>
            @endif

            @if ($content && $content->why_rntu_readers)
                <div class="feature_box">
                    <div class="feature_icon">
                        <img src="{{ asset('storage/home_page/why_4.png') }}" alt="Readers">
                    </div>
                    <h4>{{ $content->why_rntu_readers }}</h4>
                    <p>{{ $content->why_rntu_readers_label }}</p>
                </div>
            @endif

            @if ($content && $content->why_rntu_access)
                <div class="feature_box">
                    <div class="feature_icon">
                        <img src="{{ asset('storage/home_page/why_5.png') }}" alt="Access">
                    </div>
                    <h4>{{ $content->why_rntu_access }}</h4>
                    <p>{{ $content->why_rntu_access_label }}</p>
                </div>
            @endif

        </div>

        {{-- Support Card --}}
        @if ($content && $content->support_section_heading)
            <div class="research_card">

                <div class="research_overlay"></div>

                <div class="research_content">

                    <div>
                        <h3>{{ $content->support_section_heading }}</h3>

                    </div>

                    <div class="research_bottom">

                        @if ($content->support_articles_count)
                            <h4>{{ $content->support_articles_count }}</h4>
                        @endif

                        @if ($content->support_section_description)
                            <h5>{{ $content->support_short_heading }}</h5>
                            <p>{!! $content->support_section_description !!}</p>
                        @endif

                    </div>

                </div>
            </div>
        @endif

    </div>
</section>
