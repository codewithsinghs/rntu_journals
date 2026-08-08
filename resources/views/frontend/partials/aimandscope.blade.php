{{-- frontend/partials/aim-scope-why.blade.php --}}
<div id="aimScopeLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<section class="aim_scope_section d-none" id="aimScopeSection">
    <div class="s__container_custom">
        <div class="row align-items-center gy-5">

            <div class="col-lg-6">
                <div class="journal_image_wrapper">
<<<<<<< HEAD
                    <div class="bg_journal">
                        @if ($content && $content->aim_section_image)
                            <img src="{{ asset('storage/' . $content->aim_section_image) }}" alt="Aim and Scope">
                        @endif
                    </div>
=======
                    <div class="bg_journal" id="aimSectionImageWrap"></div>
>>>>>>> main
                </div>
            </div>

            <div class="col-lg-6">
                <div class="aim_scope_content">
<<<<<<< HEAD

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

=======
                    <span class="journal_tag d-none" id="aimTitle1"></span>
                    <h2 id="aimTitle2"></h2>
                    <p id="aimDescription"></p>
                    <h3 id="aimTitle3"></h3>
                    <p id="scopeDescription"></p>
                    <div class="quote_box d-none" id="quoteBoxWrap">
                        <div class="quote_line"></div>
                        <p id="quoteText"></p>
                    </div>
>>>>>>> main
                </div>
            </div>

        </div>
    </div>
</section>

<section class="why_rntu_section d-none" id="whyRntuSection">
    <div class="s__container_custom">

        <div class="section_top text-center">
<<<<<<< HEAD

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
=======
            <span class="section_tag d-none" id="whyTitle1"></span>
            <h2 id="whyTitle2"></h2>
        </div>

        <div class="why_features" id="whyFeatures"></div>

        <div class="research_card d-none" id="supportCard">
            <div class="research_overlay"></div>
            <div class="research_content">
                <div><h3 id="supportHeading"></h3></div>
                <div class="research_bottom">
                    <h4 id="supportArticlesCount"></h4>
                    <h5 id="supportShortHeading"></h5>
                    <p id="supportDescription"></p>
                </div>
            </div>
        </div>
>>>>>>> main

    </div>
</section>
