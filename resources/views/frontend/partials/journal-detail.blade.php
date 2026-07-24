<!-- JOURNAL SECTION -->
<div class="s__container_custom mt-5">

    @if (isset($journal))

        <!-- TOP HEADING -->
        <div class="journal_top text-center mb-2">
            <h2>Journal Description</h2>
        </div>

        <div class="journal-card" style="align-items: center;">

            <div class="journal-image">
                @if ($journal->cover_image)
                    <img src="{{ Storage::url($journal->cover_image) }}" alt="{{ $journal->title }}">
                @else
                    <img src="{{ asset('assets/home_page/hero_1.jpg') }}" alt="{{ $journal->title }}">
                @endif
            </div>

            <div class="journal-content">

                @if ($journal->description)
                    <p class="text_wrap_3">
                        {!! $journal->description !!}
                    </p>
                @endif

                <div class="fields-box">
                    <div class="fields-grid">

                        @if ($journal->e_issn)
                            <div class="field-item field-item-2">E-ISSN : {{ $journal->e_issn }}</div>
                        @endif

                        @if ($journal->p_issn)
                            <div class="field-item field-item-2">P-ISSN : {{ $journal->p_issn }}</div>
                        @endif

                        @if ($journal->issn_online)
                            <div class="field-item field-item-2">ISSN: {{ $journal->issn_online }} (Online)</div>
                        @endif

                        @if ($journal->abbreviation)
                            <div class="field-item field-item-2">Journal Abbreviation: {{ $journal->abbreviation }}
                            </div>
                        @endif

                        @if ($journal->publication_language)
                            <div class="field-item field-item-2">Publication language:
                                {{ $journal->publication_language }}</div>
                        @endif

                        @if ($journal->publishing_frequency)
                            <div class="field-item field-item-2">Publishing frequency:
                                {{ $journal->publishing_frequency }}</div>
                        @endif

                        @if ($journal->publishing_months)
                            <div class="field-item field-item-2">({{ $journal->publishing_months }})</div>
                        @endif

                        @if ($journal->article_template_url)
                            <div class="field-item field-item-2">
                                Download Article Template <a href="{{ $journal->article_template_url }}"
                                    target="_blank">click here</a>
                            </div>
                        @endif

                        @if ($journal->volume)
                            <div class="field-item field-item-2">Volume : {{ $journal->volume }}</div>
                        @endif

                        @if ($journal->issue)
                            <div class="field-item field-item-2">Issue : {{ $journal->issue }}</div>
                        @endif

                        @if ($journal->time_to_first_decision)
                            <div class="field-item field-item-2">Time to First Decision :
                                {{ $journal->time_to_first_decision }}</div>
                        @endif

                        @if ($journal->time_to_review)
                            <div class="field-item field-item-2">Time to Review : {{ $journal->time_to_review }}</div>
                        @endif

                        @if ($journal->acceptance_to_publication)
                            <div class="field-item field-item-2">Acceptance to Publication:
                                {{ $journal->acceptance_to_publication }}</div>
                        @endif

                        @if ($journal->latest_volume)
                            <div class="field-item field-item-2">Latest Volume : {{ $journal->latest_volume }}</div>
                        @endif

                    </div>
                </div>


            </div>

        </div>

        <!-- Aim and Scope -->
        @if ($journal->aim_and_scope)
            <section class="editorial-section s__container_custom mt-5">
                <div class="section-title">{{ $journal->aim_and_scope_title ?: 'Aim and Scope' }}</div>

                <div class="editor-card single-card">
                    <p style="line-height: 1.5;">
                        {!! $journal->aim_and_scope !!}
                    </p>
                </div>
            </section>
        @endif
    @else
        <div class="text-center py-4">Journal not found.</div>
    @endif

</div>

<!-- Articles -->
<div class="s__container_custom mt-5" id="current_issues">

    <div class="text-center">
        <span class="journal_tag">Article</span>
    </div>

    <h2 class="current-title text-center">Latest & Most Viewed</h2>

    <!-- Articles Grid -->
    <div class="articles-grid" id="articlesGrid">
        @forelse (($articles ?? collect()) as $index => $article)
            <div class="article-card">

                <div class="article-number">
                    {{ (method_exists($articles ?? null, 'firstItem') ? $articles->firstItem() : 1) + $index }}
                </div>

                <div class="article-content">
                    <h3>
                        <a href="{{ url('/article/' . $article->uuid) }}" class="link_connect">
                            {{ $article->manuscript_title }}
                        </a>
                    </h3>

                    <span class="authors">{{ $article->co_authors ?: $article->full_name }}</span>

                </div>

                @if ($article->signed_manuscript_pdf)
                    <a href="{{ Storage::url($article->signed_manuscript_pdf) }}" class="pdf-btn" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>
                @endif

            </div>
        @empty
            <div class="text-center py-4">No articles available for this journal yet.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if (isset($articles) && method_exists($articles, 'links'))
        <div class="pagination-wrapper mt-4 d-flex justify-content-center">
            {{ $articles->links() }}
        </div>
    @endif

</div>
