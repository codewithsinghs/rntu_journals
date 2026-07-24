@extends('layouts.app')

@section('content')

<div class="jrn-page">

    <!-- Top -->
    <div class="jrn-breadcrumb">

        <!-- breadcrumb — static, no Volume/Issue linkage in DB yet -->
        <div><a href="{{ route('home') }}">Home /</a> <a href="#">Archives /</a> <span class="active">Volume 8, Issue 3, Year 2026</span></div>

        <!-- DOI — static, no doi column in DB yet -->
        <div class="jrn-top-meta">
            <span>
                DOI :
                <a href="#">https://doi.org/10.54392/ijrmt263</a>
            </span>
        </div>

    </div>

    <!-- Title -->
    <h1 class="jrn-title">
        {{ $article->manuscript_title }}
    </h1>

    <!-- Authors — main author + co-authors, from DB -->
    <div class="jrn-authors">
        {{ $article->full_name }}@if($article->coAuthors->isNotEmpty()),
            @foreach($article->coAuthors as $coAuthor)
                {{ $coAuthor->name }}{{ !$loop->last ? ',' : '' }}
            @endforeach
        @endif
    </div>

    <div class="row">

        <div class="col-xl-6 col-md-12 col-sm-12">
            <div class="jrn-main-content">

                <!-- Abstract -->
                <div class="jrn-card">

                    <div class="jrn-card-header">
                        <div class="jrn-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>Abstract</h3>
                    </div>

                    <div class="jrn-card-body">
                        <p>{{ $article->abstract_summary }}</p>
                    </div>

                    <!-- Keywords -->
                    <div class="card_j_line"></div>

                    <div class="jrn-card-header">
                        <div class="jrn-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3>Keywords</h3>
                    </div>

                    <div class="jrn-keyword-list">
                        @foreach(($article->keywords ?? []) as $keyword)
                            <span>{{ $keyword }}</span>
                        @endforeach
                    </div>

                </div>

            </div>
        </div>

        <div class="col-xl-6 col-md-12 col-sm-12">

            <div class="row">
                <!-- Downloads — static, no downloads tracking in DB yet -->
                <div class="col-xl-6 col-md-12 col-sm-12">
                    <div class="jrn-sidebar">
                        <div class="jrn-card">

                            <div class="jrn-card-header">
                                <div class="jrn-icon">
                                    <i class="fas fa-chart-column"></i>
                                </div>
                                <h3>Downloads</h3>
                            </div>

                            <div class="jrn-download-box">
                                <span>Total Downloads</span>
                                <strong>20</strong>
                            </div>

                            <div class="chart-wrapper">
                                <canvas id="downloadChart"></canvas>
                            </div>

                            <button class="jrn-btn">
                                Most downloads in May 2026
                            </button>

                        </div>
                    </div>
                </div>

                <!-- PDF / Details / Copyright -->
                <div class="col-xl-6 col-md-12 col-sm-12">
                    <div class="jrn-sidebar">

                        <!-- PDF — real file, from DB -->
                        <div class="jrn-card">
                            <div class="jrn-card-header m-0">
                                <div class="jrn-icon">
                                    <i class="fas fa-download"></i>
                                </div>
                                <h3>
                                    @if($article->signed_manuscript_pdf)
                                        <a href="{{ route('article.download-manuscript', $article->id) }}" style="color:inherit;text-decoration:none;">
                                            Download PDF
                                        </a>
                                    @else
                                        Download PDF
                                    @endif
                                </h3>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="jrn-card">
                            <div class="jrn-card-header">
                                <div class="jrn-icon">
                                    <i class="fas fa-file-lines"></i>
                                </div>
                                <h3>Article Details</h3>
                            </div>

                            <!-- Volume/Issue — static, no linkage in DB yet -->
                            <p class="deatils_j">
                                Volume 8, Issue 3, Year 2026
                            </p>

                            <!-- DOI — static -->
                            <p class="deatils_j">
                                DOI :
                                <a href="#">
                                    https://doi.org/10.54392/ijrmt263
                                </a>
                            </p>

                            <!-- Published date — real, from DB -->
                            <p class="deatils_j">
                                Published :
                                {{ optional($article->review?->updated_at ?? $article->submission_date)->format('Y-m-d') ?? '—' }}
                            </p>
                        </div>

                        <!-- Copyright — author names real, year/text static -->
                        <div class="jrn-card">
                            <div class="jrn-card-header mb-2">
                                <div class="jrn-icon">
                                    <i class="fas fa-copyright"></i>
                                </div>
                                <h3>Copyrights & License</h3>
                            </div>

                            <p class="copyright_j">
                                Copyright (c) {{ now()->year }} {{ $article->full_name }}@if($article->coAuthors->isNotEmpty()), @foreach($article->coAuthors as $coAuthor){{ $coAuthor->name }}{{ !$loop->last ? ',' : '' }} @endforeach @endif
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Citation — static formatting/DOI, real title/authors -->
                <div class="col-12">
                    <div class="jrn-card">

                        <div class="jrn-card-header">
                            <div class="jrn-icon">
                                <i class="fas fa-quote-right"></i>
                            </div>
                            <h3>How to Cite</h3>
                        </div>

                        <p class="Citation">
                            {{ $article->full_name }}@if($article->coAuthors->isNotEmpty()), @foreach($article->coAuthors as $coAuthor){{ $coAuthor->name }}{{ !$loop->last ? ',' : '' }} @endforeach @endif.
                            {{ now()->year }}. &ldquo;{{ $article->manuscript_title }}&rdquo;.
                            {{ $article->journal?->title ?? 'Journal' }}. Volume 8 (3):152-65.
                            https://doi.org/10.54392/irjmt26310.
                        </p>

                        <div class="jrn-btn-wrap">
                            <button class="jrn-btn">
                                More Citation Formats
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- References — real free-text field from DB, static fallback if empty -->
    <div class="jrn-card">

        <div class="jrn-card-header">
            <div class="jrn-icon">
                <i class="fas fa-link"></i>
            </div>
            <h3>References</h3>
        </div>

        @if($article->references)
            <p style="white-space: pre-line;">{{ $article->references }}</p>
        @else
            <p class="text-muted">No references provided for this submission.</p>
        @endif

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    new Chart(document.getElementById('downloadChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                data: [15, 28, 18, 22, 27, 22],
                backgroundColor: ['#e8edf3', '#e8edf3', '#e8edf3', '#e8edf3', '#0b356b', '#e8edf3'],
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 14
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, max: 30, ticks: { stepSize: 10 }, grid: { color: '#e5e5e5' } }
            }
        }
    });
</script>

@endsection