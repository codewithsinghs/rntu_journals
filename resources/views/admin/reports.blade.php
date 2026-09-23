@extends('layouts.admin')

@section('content')

    <!-- ============================= -->
    <!-- PRINTABLE REPORT AREA START   -->
    <!-- ============================= -->
    <div id="printable-report">

        <section class="inner_p">
            <div class="content_top_wrapper">
                <div class="p_cards">

                    <div class="heading">
                        Overview of total Journals submitted
                    </div>

                    <div class="table-controls no-print">
                        <button class="add-btn" onclick="window.print()">
                            <i class="fa fa-print"></i> Print Report
                        </button>
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
                                <img src="/images/dashboard/d_1.png">
                            </div>
                        </div>

                        <!-- Total Articles -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Total Articles</p>
                                <h3 id="stat_total_articles">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/d_2.png">
                            </div>
                        </div>

                        <!-- Submitted Articles -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Submitted Articles</p>
                                <h3 id="stat_submitted_articles">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/d_3.png">
                            </div>
                        </div>

                        <!-- Under Review -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Under Review</p>
                                <h3 id="stat_under_review">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/d_4.png">
                            </div>
                        </div>

                        <!-- Pending Submission -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Pending Submission</p>
                                <h3 id="stat_pending_submission">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/d_4.png">
                            </div>
                        </div>

                        <!-- Revision Requested -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Revision Requested</p>
                                <h3 id="stat_revision_requested">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/d_4.png">
                            </div>
                        </div>

                        <!-- Accepted -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Accepted Articles</p>
                                <h3 id="stat_accepted_articles">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/accept.png">
                            </div>
                        </div>

                        <!-- Rejected -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Rejected Articles</p>
                                <h3 id="stat_rejected_articles">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/min.png">
                            </div>
                        </div>

                        <!-- Published -->
                        <div class="card_d">
                            <div class="card-content">
                                <p>Published Articles</p>
                                <h3 id="stat_published_articles">0</h3>
                            </div>
                            <div class="card-image">
                                <img src="/images/dashboard/upload.png">
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>

        <!-- Recent Submitted Articles -->
        <section class="inner_p">
            <div class="content_top_wrapper">
                <div class="p_cards">

                    <div class="heading">
                        Recent Submitted Articles
                    </div>

                    <div class="table-container" style="margin: 0;">
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Journal</th>
                                    <th>Manuscript Title</th>
                                    <th>Submitted</th>
                                    <th class="no-print">Action</th>
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

        <div class="print-footer" style="display:none;">
            Report generated on <span id="printGeneratedDate"></span>
        </div>

    </div>
    <!-- ============================= -->
    <!-- PRINTABLE REPORT AREA END     -->
    <!-- ============================= -->

@endsection

@section('scripts')
    <!-- Chart.js kept only if the JS below still references charts on this page; remove if not needed -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/admin/dashboard.js') }}"></script>

    <script>
        // Stamp the print date/time when the user actually prints
        window.addEventListener('beforeprint', function () {
            document.getElementById('printGeneratedDate').innerText = new Date().toLocaleString();
        });
    </script>
@endsection

@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | PRINT STYLES
    |--------------------------------------------------------------------------
    | Hides everything on the page (sidebar, topbar, buttons, nav) except the
    | #printable-report block when the browser print dialog is triggered.
    */
    @media print {

        /* Hide absolutely everything by default */
        body * {
            visibility: hidden;
        }

        /* Un-hide only the report block and its children */
        #printable-report,
        #printable-report * {
            visibility: visible;
        }

        /* Pull the report to the top-left of the printed page */
        #printable-report {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Hide anything explicitly marked no-print (buttons, action column, etc.) */
        .no-print,
        .no-print * {
            display: none !important;
        }

        /* Show the "generated on" footer only when printing */
        .print-footer {
            display: block !important;
            margin-top: 20px;
            font-size: 12px;
            color: #555;
            text-align: right;
        }

        /* Avoid cards/rows splitting awkwardly across pages */
        .card_d,
        .status-table tr {
            page-break-inside: avoid;
        }

        /* Force card grid into a clean printable layout */
        .grid_colums_card {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 10px !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        th, td {
            border: 1px solid #ccc !important;
            padding: 6px !important;
        }
    }
</style>
@endpush