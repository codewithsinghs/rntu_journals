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
                        <button class="add-btn"><a href="{{ url('/admin/dashboard/monthly-submissions/export') }}">Download Excel</a></button>
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
                        <button class="add-btn"><a href="{{ url('/admin/dashboard/monthly-published/export') }}">Download Excel</a></button>
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
                        <button class="add-btn"><a href="{{ url('/admin/dashboard/article-downloads/export') }}">Download Excel</a></button>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const API_BASE = '/api/admin/dashboard';
        const TOKEN = localStorage.getItem('jwt_token') || '';

        const authHeaders = () => ({
            'Accept': 'application/json',
            'Authorization': `Bearer ${TOKEN}`,
        });

        const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Fill a full 12-month array with zeros for any month with no data,
        // so charts always render Jan–Dec even when the API only returns
        // rows for months that actually had activity.
        function toMonthlySeries(rows) {
            const map = {};
            (rows || []).forEach(r => {
                map[r.month] = r.total;
            });
            return MONTHS.map(m => map[m] ?? 0);
        }

        function highlightLast(dataArr) {
            // Highlights the most recent non-zero month, matching the
            // original design's single-highlighted-bar look.
            let lastIdx = 0;
            dataArr.forEach((v, i) => {
                if (v > 0) lastIdx = i;
            });
            return dataArr.map((_, i) => i === lastIdx ? '#003A65' : '#E8EDF3');
        }

        /* ---------- Monthly Article Submission chart ---------- */
        async function loadSubmissionChart() {
            try {
                const res = await fetch(`${API_BASE}/monthly-submissions`, {
                    headers: authHeaders()
                });
                const json = await res.json();
                const data = toMonthlySeries(json.data);

                new Chart(document.getElementById('submissionChart'), {
                    type: 'bar',
                    data: {
                        labels: MONTHS,
                        datasets: [{
                            data,
                            backgroundColor: highlightLast(data),
                            borderRadius: 8,
                            borderSkipped: false,
                            barThickness: 16,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#E5E5E5'
                                }
                            },
                        },
                    },
                });
            } catch (e) {
                console.error('Submission chart failed', e);
            }
        }

        /* ---------- Monthly Published Articles chart ---------- */
        async function loadPublishedChart() {
            try {
                const res = await fetch(`${API_BASE}/monthly-published`, {
                    headers: authHeaders()
                });
                const json = await res.json();
                const data = toMonthlySeries(json.data);

                new Chart(document.getElementById('publishedChart'), {
                    type: 'bar',
                    data: {
                        labels: MONTHS,
                        datasets: [{
                            data,
                            backgroundColor: highlightLast(data),
                            borderRadius: 8,
                            borderSkipped: false,
                            barThickness: 16,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 5
                                },
                                grid: {
                                    color: '#E5E5E5'
                                }
                            },
                        },
                    },
                });
            } catch (e) {
                console.error('Published chart failed', e);
            }
        }

        /* ---------- Article Downloads chart ---------- */
        async function loadDownloadsChart() {
            try {
                const res = await fetch(`${API_BASE}/article-downloads`, {
                    headers: authHeaders()
                });
                const json = await res.json();
                const data = toMonthlySeries(json.data);

                new Chart(document.getElementById('downloadChart'), {
                    type: 'bar',
                    data: {
                        labels: MONTHS,
                        datasets: [{
                            data,
                            backgroundColor: highlightLast(data),
                            borderRadius: 8,
                            borderSkipped: false,
                            barThickness: 16,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#E5E5E5'
                                }
                            },
                        },
                    },
                });
            } catch (e) {
                console.error('Downloads chart failed', e);
            }
        }

        loadSubmissionChart();
        loadPublishedChart();
        loadDownloadsChart();

    });
</script>

@endsection