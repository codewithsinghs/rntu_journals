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
        const year = new Date().getFullYear();
        els.copyright.textContent = `Copyright (c) ${year} ${authorsCopyright}`;

        const citationAuthors = authorsLine(article);
        els.citation.textContent =
            `${citationAuthors}. ${year}. \u201C${article.manuscript_title}\u201D. ` +
            `${article.journal_title || 'Journal'}. Volume 8 (3):152-65. ` +
            `https://doi.org/10.54392/irjmt26310.`;

        els.references.textContent = article.references
            ? article.references
            : 'No references provided for this submission.';

        els.loading.classList.add('d-none');
        els.content.classList.remove('d-none');

        initChart();
    }

    function initChart() {
        const canvas = document.getElementById('downloadChart');
        if (!canvas || typeof Chart === 'undefined') return;

        new Chart(canvas, {
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