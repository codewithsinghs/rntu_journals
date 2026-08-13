@extends('layouts.app')

@section('content')

<div class="jrn-page" id="articleApp"
     data-journal-slug="{{ $journal }}"
     data-article-uuid="{{ $articleUuid }}"
     data-api-base="{{ url('/api/public/' . $journal . '/articles') }}">

    <!-- Loading state -->
    <div id="articleLoading" class="text-center py-5">Loading article...</div>

    <!-- Error state -->
    <div id="articleError" class="text-center py-5 d-none">Article not found.</div>

    <div id="articleContent" class="d-none">

        <!-- Top -->
        <div class="jrn-breadcrumb">

            <div><a href="{{ route('home') }}">Home /</a> <a href="#">Archives /</a>
                <span class="active" id="articleVolumeIssueYear">Volume –, Issue –, Year –</span>
            </div>

            <!-- DOI — static, no doi column in DB yet -->
            <div class="jrn-top-meta">
                <span>
                    DOI :
                    <a href="#">https://doi.org/10.54392/ijrmt263</a>
                </span>
            </div>

        </div>

        <!-- Title -->
        <h1 class="jrn-title" id="articleTitle"></h1>

        <!-- Authors -->
        <div class="jrn-authors" id="articleAuthors"></div>

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
                            <p id="articleAbstract"></p>
                        </div>

                        <!-- Keywords -->
                        <div class="card_j_line"></div>

                        <div class="jrn-card-header">
                            <div class="jrn-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <h3>Keywords</h3>
                        </div>

                        <div class="jrn-keyword-list" id="articleKeywords"></div>

                    </div>

                </div>
            </div>

            <div class="col-xl-6 col-md-12 col-sm-12">

                <div class="row">
                    <!-- Downloads -->
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
                                    <strong id="articleTotalDownloads">0</strong>
                                </div>

                                <div class="chart-wrapper">
                                    <canvas id="downloadChart"></canvas>
                                </div>

                                <button class="jrn-btn" id="mostDownloadsBtn">
                                    Most downloads —
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- PDF / Details / Copyright -->
                    <div class="col-xl-6 col-md-12 col-sm-12">
                        <div class="jrn-sidebar">

                            <!-- PDF -->
                            <div class="jrn-card">
                                <div class="jrn-card-header m-0">
                                    <div class="jrn-icon">
                                        <i class="fas fa-download"></i>
                                    </div>
                                    <h3 id="articlePdfLink">Download PDF</h3>
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

                                <p class="deatils_j" id="articleVolumeIssueYearDetails">
                                    Volume –, Issue –, Year –
                                </p>

                                <!-- DOI — static -->
                                <p class="deatils_j">
                                    DOI :
                                    <a href="#">
                                        https://doi.org/10.54392/ijrmt263
                                    </a>
                                </p>

                                <!-- Published date -->
                                <p class="deatils_j">
                                    Published : <span id="articlePublishedDate">—</span>
                                </p>
                            </div>

                            <!-- Copyright -->
                            <div class="jrn-card">
                                <div class="jrn-card-header mb-2">
                                    <div class="jrn-icon">
                                        <i class="fas fa-copyright"></i>
                                    </div>
                                    <h3>Copyrights &amp; License</h3>
                                </div>

                                <p class="copyright_j" id="articleCopyright"></p>
                            </div>

                        </div>
                    </div>

                    <!-- Citation -->
                    <div class="col-12">
                        <div class="jrn-card">

                            <div class="jrn-card-header">
                                <div class="jrn-icon">
                                    <i class="fas fa-quote-right"></i>
                                </div>
                                <h3>How to Cite</h3>
                            </div>

                            <p class="Citation" id="articleCitation"></p>

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

        <!-- References -->
        <div class="jrn-card">

            <div class="jrn-card-header">
                <div class="jrn-icon">
                    <i class="fas fa-link"></i>
                </div>
                <h3>References</h3>
            </div>

            <p id="articleReferences" style="white-space: pre-line;"></p>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@endsection