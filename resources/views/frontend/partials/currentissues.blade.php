@extends('layouts.app')

@section('content')

    <div class="current-banner">

        <div class="current-icon">
            <i class="fa-solid fa-file-lines"></i>
        </div>

        <div class="current-content">
            <h1>
                Volume {{ $issue->volume->volume ?? '-' }},
                Issue {{ $issue->issue }},
                Year {{ $issue->year }}
            </h1>

            <p>
                <i class="fa-regular fa-calendar"></i>
                Published:
                {{ $issue->published_date ? \Carbon\Carbon::parse($issue->published_date)->format('Y-m-d') : '-' }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                DOI :
                <a href="#">https://doi.org/10.54392/ijrmt263</a>
            </p>
        </div>

    </div>

    <div class="s__container_custom">

        <span class="journal_tag">CURRENTS</span>

        <h2 class="current-title">Articles</h2>

        <div class="articles-grid" id="articlesGrid">

            @forelse ($articles as $index => $article)
                <div class="article-card">

                    <div class="article-number">{{ $articles->firstItem() + $index }}</div>

                    <div class="article-content">
                        <h3>
                            <a href="{{ route('articles', $article->uuid) }}" class="link_connect">
                                {{ $article->manuscript_title }}
                            </a>
                        </h3>

                        <p>
                            DOI :
                            <a href="#">https://doi.org/10.54392/ijrmt263</a>
                        </p>

                        <span class="authors">
                            {{ $article->full_name }}@if ($article->coAuthors->isNotEmpty())
                                , @foreach ($article->coAuthors->take(2) as $coAuthor)
                                    {{ $coAuthor->name }}{{ !$loop->last ? ',' : '' }}
                                    @endforeach @if ($article->coAuthors->count() > 2)
                                        ...
                                    @endif
                                @endif
                        </span>

                        <div class="pages">317-665</div>
                    </div>

                    @if ($article->signed_manuscript_pdf)
                        <a href="{{ route('article.download-manuscript', $article->id) }}" class="pdf-btn">
                            <i class="fa-solid fa-file-pdf"></i>
                        </a>
                    @endif

                </div>
                @empty
                    <p class="text-center py-4">No published articles yet in this issue.</p>
                @endforelse

            </div>

        </div>

        <div class="pagination-wrap d-flex justify-content-center py-4">
            {{ $articles->onEachSide(1)->links() }}
        </div>

    @endsection
