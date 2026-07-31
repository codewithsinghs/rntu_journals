@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-6">

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

        <div class="col-6">

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
@endsection


@section('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/admin/reports.js') }}"></script>
@endsection
