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
<!-- Recent Submitted Articles -->
<section class="inner_p">
    <div class="content_top_wrapper">
        <div class="p_cards">

            <div class="heading">
                Recent Submitted Articles
            </div>

            <div class="table-controls">
                <button class="add-btn"><a href="{{ url('/admin/dashboard/recent-submissions/export') }}">Download Excel</a></button>
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
                <button class="add-btn"><a href="{{ url('/admin/dashboard/latest-publications/export') }}">Download Excel</a></button>
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

<!-- Dropdown (unrelated to data, left as-is) -->
<script>
    document.querySelectorAll(".dropdown-btn").forEach(button => {
        button.addEventListener("click", function() {
            const parent = this.parentElement;
            document.querySelectorAll(".dropdown-menu-item").forEach(item => {
                if (item !== parent) item.classList.remove("active");
            });
            parent.classList.toggle("active");
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const API_BASE = '/api/admin/dashboard';
        const TOKEN = localStorage.getItem('jwt_token') || '';

        const authHeaders = () => ({
            'Accept': 'application/json',
            'Authorization': `Bearer ${TOKEN}`,
        });

        const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        function esc(s) {
            if (!s) return '';
            return String(s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Formats any date/datetime string down to just the date part,
        // e.g. "2026-07-17T09:05:57.000000Z" -> "17 Jul 2026".
        // Falls back to the raw (escaped) string if it isn't a parseable date.
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return esc(dateStr);
            const day   = String(d.getDate()).padStart(2, '0');
            const month = d.toLocaleString('en-US', { month: 'short' });
            const year  = d.getFullYear();
            return `${day} ${month} ${year}`;
        }

        // Truncates a title down to ~10 words and adds an ellipsis if cut off.
        function truncateWords(str, wordLimit = 10) {
            if (!str) return '-';
            const words = String(str).trim().split(/\s+/);
            if (words.length <= wordLimit) return esc(str);
            return esc(words.slice(0, wordLimit).join(' ')) + '…';
        }

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

        /* ---------- Overview cards ---------- */
        async function loadOverview() {
            try {
                const res = await fetch(`${API_BASE}/overview`, {
                    headers: authHeaders()
                });
                const json = await res.json();
                const d = json.data || {};

                document.getElementById('stat_total_journals').textContent = d.total_journals ?? 0;
                document.getElementById('stat_total_articles').textContent = d.total_articles ?? 0;
                document.getElementById('stat_submitted_articles').textContent = d.submitted_articles ?? 0;
                document.getElementById('stat_under_review').textContent = d.under_review ?? 0;
                document.getElementById('stat_pending_submission').textContent = d.pending_submission ?? 0;
                document.getElementById('stat_revision_requested').textContent = d.revision_requested ?? 0;
                document.getElementById('stat_accepted_articles').textContent = d.accepted_articles ?? 0;
                document.getElementById('stat_rejected_articles').textContent = d.rejected_articles ?? 0;
                document.getElementById('stat_published_articles').textContent = d.published_articles ?? 0;

                document.getElementById('overviewLoading').classList.add('d-none');
                document.getElementById('overviewGrid').classList.remove('d-none');
            } catch (e) {
                document.getElementById('overviewLoading').innerHTML =
                    `<p class="text-danger mb-0">Failed to load overview stats.</p>`;
            }
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

        /* ---------- Recent Submitted Articles table ---------- */
        async function loadRecentSubmissions() {
            const tbody = document.getElementById('recentSubmissionsBody');
            try {
                const res = await fetch(`${API_BASE}/recent-submissions`, {
                    headers: authHeaders()
                });
                const json = await res.json();
                const rows = json.data || [];

                if (!rows.length) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3">No submissions yet.</td></tr>`;
                    return;
                }

                tbody.innerHTML = rows.map(r => `
                <tr>
                    <td>${esc(r.display_id ?? ('ART' + String(r.id).padStart(3, '0')))}</td>
                    <td>${esc(r.journal?.title ?? '-')}</td>
                    <td title="${esc(r.manuscript_title ?? '')}">${truncateWords(r.manuscript_title, 10)}</td>
                    <td>${formatDate(r.submission_date ?? r.created_at)}</td>
                    <td>
                        <button class="edit-btn"><a href="/admin/submit-articles/${r.id}">View</a></button>
                    </td>
                </tr>
            `).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger">Failed to load.</td></tr>`;
            }
        }

        /* ---------- Latest Publications table ---------- */
        async function loadLatestPublications() {
            const tbody = document.getElementById('latestPublicationsBody');
            try {
                const res = await fetch(`${API_BASE}/latest-publications`, {
                    headers: authHeaders()
                });
                const json = await res.json();
                const rows = json.data || [];

                if (!rows.length) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3">No publications yet.</td></tr>`;
                    return;
                }

                tbody.innerHTML = rows.map(r => `
                <tr>
                    <td title="${esc(r.manuscript_title ?? '')}">${truncateWords(r.manuscript_title, 10)}</td>
                    <td>${esc(r.journal?.title ?? '-')}</td>
                    <td>${esc(r.issue?.volume?.volume ?? '-')}</td>
                    <td>${esc(r.issue?.issue ?? '-')}</td>
                    <td>${formatDate(r.approval_date)}</td>
                </tr>
            `).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger">Failed to load.</td></tr>`;
            }
        }

        loadOverview();
        loadSubmissionChart();
        loadPublishedChart();
        loadDownloadsChart();
        loadRecentSubmissions();
        loadLatestPublications();

    });
</script>

@endsection