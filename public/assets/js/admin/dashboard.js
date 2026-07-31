document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/dashboard";
    const TOKEN = localStorage.getItem("jwt_token") || "";

    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    const MONTHS = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ];

    function esc(s) {
        if (!s) return "";
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    // Formats any date/datetime string down to just the date part,
    // e.g. "2026-07-17T09:05:57.000000Z" -> "17 Jul 2026".
    // Falls back to the raw (escaped) string if it isn't a parseable date.
    function formatDate(dateStr) {
        if (!dateStr) return "-";
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return esc(dateStr);
        const day = String(d.getDate()).padStart(2, "0");
        const month = d.toLocaleString("en-US", {
            month: "short",
        });
        const year = d.getFullYear();
        return `${day} ${month} ${year}`;
    }

    // Truncates a title down to ~10 words and adds an ellipsis if cut off.
    function truncateWords(str, wordLimit = 10) {
        if (!str) return "-";
        const words = String(str).trim().split(/\s+/);
        if (words.length <= wordLimit) return esc(str);
        return esc(words.slice(0, wordLimit).join(" ")) + "…";
    }

    // Fill a full 12-month array with zeros for any month with no data,
    // so charts always render Jan–Dec even when the API only returns
    // rows for months that actually had activity.
    function toMonthlySeries(rows) {
        const map = {};
        (rows || []).forEach((r) => {
            map[r.month] = r.total;
        });
        return MONTHS.map((m) => map[m] ?? 0);
    }

    function highlightLast(dataArr) {
        // Highlights the most recent non-zero month, matching the
        // original design's single-highlighted-bar look.
        let lastIdx = 0;
        dataArr.forEach((v, i) => {
            if (v > 0) lastIdx = i;
        });
        return dataArr.map((_, i) => (i === lastIdx ? "#003A65" : "#E8EDF3"));
    }

    /* ---------- Overview cards ---------- */
    async function loadOverview() {
        try {
            const res = await fetch(`${API_BASE}/overview`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            const d = json.data || {};

            document.getElementById("stat_total_journals").textContent =
                d.total_journals ?? 0;
            document.getElementById("stat_total_articles").textContent =
                d.total_articles ?? 0;
            document.getElementById("stat_submitted_articles").textContent =
                d.submitted_articles ?? 0;
            document.getElementById("stat_under_review").textContent =
                d.under_review ?? 0;
            document.getElementById("stat_pending_submission").textContent =
                d.pending_submission ?? 0;
            document.getElementById("stat_revision_requested").textContent =
                d.revision_requested ?? 0;
            document.getElementById("stat_accepted_articles").textContent =
                d.accepted_articles ?? 0;
            document.getElementById("stat_rejected_articles").textContent =
                d.rejected_articles ?? 0;
            document.getElementById("stat_published_articles").textContent =
                d.published_articles ?? 0;

            document.getElementById("overviewLoading").classList.add("d-none");
            document.getElementById("overviewGrid").classList.remove("d-none");
        } catch (e) {
            document.getElementById("overviewLoading").innerHTML =
                `<p class="text-danger mb-0">Failed to load overview stats.</p>`;
        }
    }

    /* ---------- Monthly Article Submission chart ---------- */
    async function loadSubmissionChart() {
        try {
            const res = await fetch(`${API_BASE}/monthly-submissions`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            const data = toMonthlySeries(json.data);

            new Chart(document.getElementById("submissionChart"), {
                type: "bar",
                data: {
                    labels: MONTHS,
                    datasets: [
                        {
                            data,
                            backgroundColor: highlightLast(data),
                            borderRadius: 8,
                            borderSkipped: false,
                            barThickness: 16,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "#E5E5E5",
                            },
                        },
                    },
                },
            });
        } catch (e) {
            console.error("Submission chart failed", e);
        }
    }

    /* ---------- Monthly Published Articles chart ---------- */
    async function loadPublishedChart() {
        try {
            const res = await fetch(`${API_BASE}/monthly-published`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            const data = toMonthlySeries(json.data);

            new Chart(document.getElementById("publishedChart"), {
                type: "bar",
                data: {
                    labels: MONTHS,
                    datasets: [
                        {
                            data,
                            backgroundColor: highlightLast(data),
                            borderRadius: 8,
                            borderSkipped: false,
                            barThickness: 16,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5,
                            },
                            grid: {
                                color: "#E5E5E5",
                            },
                        },
                    },
                },
            });
        } catch (e) {
            console.error("Published chart failed", e);
        }
    }

    /* ---------- Article Downloads chart ---------- */
    async function loadDownloadsChart() {
        try {
            const res = await fetch(`${API_BASE}/article-downloads`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            const data = toMonthlySeries(json.data);

            new Chart(document.getElementById("downloadChart"), {
                type: "bar",
                data: {
                    labels: MONTHS,
                    datasets: [
                        {
                            data,
                            backgroundColor: highlightLast(data),
                            borderRadius: 8,
                            borderSkipped: false,
                            barThickness: 16,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "#E5E5E5",
                            },
                        },
                    },
                },
            });
        } catch (e) {
            console.error("Downloads chart failed", e);
        }
    }

    /* ---------- Recent Submitted Articles table ---------- */
    async function loadRecentSubmissions() {
        const tbody = document.getElementById("recentSubmissionsBody");
        try {
            const res = await fetch(`${API_BASE}/recent-submissions`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            const rows = json.data || [];

            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3">No submissions yet.</td></tr>`;
                return;
            }

            tbody.innerHTML = rows
                .map(
                    (r) => `
                <tr>
                    <td>${esc(r.display_id ?? "ART" + String(r.id).padStart(3, "0"))}</td>
                    <td>${esc(r.journal?.title ?? "-")}</td>
                    <td title="${esc(r.manuscript_title ?? "")}">${truncateWords(r.manuscript_title, 10)}</td>
                    <td>${formatDate(r.submission_date ?? r.created_at)}</td>
                    <td>
                        <button class="edit-btn"><a href="/admin/submit-articles/${r.id}">View</a></button>
                    </td>
                </tr>
            `,
                )
                .join("");
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger">Failed to load.</td></tr>`;
        }
    }

    /* ---------- Latest Publications table ---------- */
    async function loadLatestPublications() {
        const tbody = document.getElementById("latestPublicationsBody");
        try {
            const res = await fetch(`${API_BASE}/latest-publications`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            const rows = json.data || [];

            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3">No publications yet.</td></tr>`;
                return;
            }

            tbody.innerHTML = rows
                .map(
                    (r) => `
                <tr>
                    <td title="${esc(r.manuscript_title ?? "")}">${truncateWords(r.manuscript_title, 10)}</td>
                    <td>${esc(r.journal?.title ?? "-")}</td>
                    <td>${esc(r.issue?.volume?.volume ?? "-")}</td>
                    <td>${esc(r.issue?.issue ?? "-")}</td>
                    <td>${formatDate(r.approval_date)}</td>
                </tr>
            `,
                )
                .join("");
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
