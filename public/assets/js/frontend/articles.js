// public/assets/js/article-detail.js

(function () {
    'use strict';

    const app = document.getElementById('articleApp');
    if (!app) return;

    const articleUuid = app.dataset.articleUuid;
    const apiBase = app.dataset.apiBase; // e.g. /api/public/articles

    const els = {
        loading: document.getElementById('articleLoading'),
        error: document.getElementById('articleError'),
        content: document.getElementById('articleContent'),
        title: document.getElementById('articleTitle'),
        authors: document.getElementById('articleAuthors'),
        abstract: document.getElementById('articleAbstract'),
        keywords: document.getElementById('articleKeywords'),
        pdfLink: document.getElementById('articlePdfLink'),
        publishedDate: document.getElementById('articlePublishedDate'),
        copyright: document.getElementById('articleCopyright'),
        citation: document.getElementById('articleCitation'),
        references: document.getElementById('articleReferences'),
        volumeIssueYear: document.getElementById('articleVolumeIssueYear'),
        volumeIssueYearDetails: document.getElementById('articleVolumeIssueYearDetails'),
        totalDownloads: document.getElementById('articleTotalDownloads'),
        mostDownloadsBtn: document.getElementById('mostDownloadsBtn'),
    };

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function authorsLine(article) {
        const names = (article.co_authors || []).map((c) => c.name);
        let text = article.full_name;
        if (names.length > 0) {
            text += ', ' + names.join(', ');
        }
        return text;
    }

    function volumeIssueYearLine(article) {
        const parts = [];
        if (article.volume) parts.push(`Volume ${article.volume}`);
        if (article.issue) parts.push(`Issue ${article.issue}`);
        if (article.year) parts.push(`Year ${article.year}`);
        return parts.length ? parts.join(', ') : 'Volume –, Issue –, Year –';
    }

    function renderArticle(article) {
        els.title.textContent = article.manuscript_title;
        els.authors.textContent = authorsLine(article);
        els.abstract.textContent = article.abstract_summary || '';

        els.keywords.innerHTML = (article.keywords || [])
            .map((k) => `<span>${escapeHtml(k)}</span>`)
            .join('');

        if (article.has_pdf && article.pdf_url) {
            els.pdfLink.innerHTML = `<a href="${escapeHtml(article.pdf_url)}" style="color:inherit;text-decoration:none;">Download PDF</a>`;
        } else {
            els.pdfLink.textContent = 'Download PDF';
        }

        els.publishedDate.textContent = article.published_date || '—';

        const authorsCopyright = authorsLine(article);
        const year = article.year || new Date().getFullYear();
        els.copyright.textContent = `Copyright (c) ${year} ${authorsCopyright}`;

        const citationAuthors = authorsLine(article);
        els.citation.textContent =
            `${citationAuthors}. ${year}. \u201C${article.manuscript_title}\u201D. ` +
            `${article.journal_title || 'Journal'}. Volume 8 (3):152-65. ` +
            `https://doi.org/10.54392/irjmt26310.`;

        els.references.textContent = article.references
            ? article.references
            : 'No references provided for this submission.';

        const vij = volumeIssueYearLine(article);
        if (els.volumeIssueYear) els.volumeIssueYear.textContent = vij;
        if (els.volumeIssueYearDetails) els.volumeIssueYearDetails.textContent = vij;

        const totalDownloads = article.total_downloads ?? 0;
        if (els.totalDownloads) els.totalDownloads.textContent = totalDownloads;

        els.loading.classList.add('d-none');
        els.content.classList.remove('d-none');

        initChart(article.downloads_by_month || []);
    }

    function initChart(downloadsByMonth) {
        const canvas = document.getElementById('downloadChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const data = downloadsByMonth.length
            ? downloadsByMonth
            : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'].map((label) => ({ label, count: 0 }));

        const labels = data.map((d) => d.label);
        const counts = data.map((d) => d.count);
        const maxCount = Math.max(...counts);
        const maxIndex = counts.lastIndexOf(maxCount);

        const barColors = counts.map((_, i) => (i === maxIndex && maxCount > 0 ? '#0b356b' : '#e8edf3'));

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data: counts,
                    backgroundColor: barColors,
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
                    y: { beginAtZero: true, ticks: { stepSize: Math.max(1, Math.ceil(maxCount / 3)) }, grid: { color: '#e5e5e5' } }
                }
            }
        });

        if (els.mostDownloadsBtn) {
            els.mostDownloadsBtn.textContent = maxCount > 0
                ? `Most downloads in ${labels[maxIndex]}`
                : 'No downloads yet';
        }
    }

    async function init() {
        try {
            const res = await fetch(`${apiBase}/${articleUuid}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();

            if (!json.status) {
                els.loading.classList.add('d-none');
                els.error.classList.remove('d-none');
                return;
            }

            renderArticle(json.data);
        } catch (err) {
            console.error('Failed to load article', err);
            els.loading.classList.add('d-none');
            els.error.classList.remove('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();