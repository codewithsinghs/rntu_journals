@extends('layouts.admin')

@section('content')
    <!-- Overview of total Journals submitted -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Overview of total Journals submitted
                </div>

                <div class="table-controls">
                    <button class="add-btn"><a href="{{ url('/admin/dashboard/reports') }}">View Reports</a></button>
                </div>

                <div id="overviewLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" style="width:24px;height:24px;" role="status"></div>
                </div>

                <div class="grid_colums_card d-none" id="overviewGrid">

                    <!-- Journals -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Total Journals</p>
                            <h3 id="stat_total_journals">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_1.png">
                        </div>
                    </div>

                    <!-- Total Articles -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Total Articles</p>
                            <h3 id="stat_total_articles">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_2.png">
                        </div>
                    </div>

                    <!-- Submitted Articles -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Submitted Articles</p>
                            <h3 id="stat_submitted_articles">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_3.png">
                        </div>
                    </div>

                    <!-- Under Review -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Under Review</p>
                            <h3 id="stat_under_review">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_4.png">
                        </div>
                    </div>

                    <!-- Pending Submission -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Pending Submission</p>
                            <h3 id="stat_pending_submission">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_4.png">
                        </div>
                    </div>

                    <!-- Revision Requested -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Revision Requested</p>
                            <h3 id="stat_revision_requested">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_4.png">
                        </div>
                    </div>

                    <!-- Accepted -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Accepted Articles</p>
                            <h3 id="stat_accepted_articles">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/accept.png">
                        </div>
                    </div>

                    <!-- Rejected -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Rejected Articles</p>
                            <h3 id="stat_rejected_articles">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/min.png">
                        </div>
                    </div>

                    <!-- Published -->
                    <div class="card_d">
                        <div class="card-content">
                            <p>Published Articles</p>
                            <h3 id="stat_published_articles">0</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/upload.png">
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <div class="row">
        <div class="col-xl-6 col-md-6 col-sm-12">

            <!-- Monthly Article Submission -->
            <section class="inner_p">
                <div class="content_top_wrapper">
                    <div class="p_cards">

                        <div class="heading">
                            Monthly Article Submission
                        </div>

                        <div class="table-controls">
                            <button class="add-btn"><a
                                    href="{{ url('/admin/dashboard/monthly-submissions/export') }}">Download
                                    Excel</a></button>
                        </div>

                        <div class="card_d">
                            <div class="chart-card">
                                <canvas id="submissionChart"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

        </div>

        <div class="col-xl-6 col-md-6 col-sm-12">

            <!-- Monthly Published Articles -->
            <section class="inner_p">
                <div class="content_top_wrapper">
                    <div class="p_cards">

                        <div class="heading">
                            Monthly Published Articles
                        </div>

                        <div class="table-controls">
                            <button class="add-btn"><a
                                    href="{{ url('/admin/dashboard/monthly-published/export') }}">Download
                                    Excel</a></button>
                        </div>

                        <div class="card_d">
                            <div class="chart-card">
                                <canvas id="publishedChart"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

        </div>

        <div class="col-12">

            <!-- Article Downloads -->
            <section class="inner_p">
                <div class="content_top_wrapper">
                    <div class="p_cards">

                        <div class="heading">
                            Article Downloads
                        </div>

                        <div class="table-controls">
                            <button class="add-btn"><a
                                    href="{{ url('/admin/dashboard/article-downloads/export') }}">Download
                                    Excel</a></button>
                        </div>

                        <div class="card_d">
                            <div class="chart-card">
                                <canvas id="downloadChart"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- Recent Submitted Articles -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Recent Submitted Articles
                </div>

                <div class="table-controls">
                    <button class="add-btn"><a href="{{ url('/admin/dashboard/recent-submissions/export') }}">Download
                            Excel</a></button>
                    <button class="add-btn"><a href="{{ route('admin.submit-articles.index') }}">View All</a></button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Journal</th>
                                <th>Manuscript Title</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="recentSubmissionsBody">
                            <tr>
                                <td colspan="5" class="text-center py-3">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>

    <!-- Latest Publications -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Latest Publications
                </div>

                <div class="table-controls">
                    <button class="add-btn"><a href="{{ url('/admin/dashboard/latest-publications/export') }}">Download
                            Excel</a></button>
                    <button class="add-btn"><a href="{{ route('admin.submit-articles.index') }}">View All</a></button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Journal</th>
                                <th>Volume</th>
                                <th>Issue</th>
                                <th>Published Date</th>
                            </tr>
                        </thead>
                        <tbody id="latestPublicationsBody">
                            <tr>
                                <td colspan="5" class="text-center py-3">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/admin/dashboard.js') }}"></script>
@endsection
