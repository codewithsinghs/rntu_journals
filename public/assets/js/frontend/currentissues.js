// public/assets/js/current-issues.js

(function () {
    'use strict';

    const app = document.getElementById('currentIssuesApp');
    if (!app) return;

    const issueUuid = app.dataset.issueUuid || null;
    const apiBase = app.dataset.apiBase; 
    const articlesRouteBase = app.dataset.articlesRouteBase; 

    const els = {
        heading: document.getElementById('issueHeading'),
        publishedDate: document.getElementById('issuePublishedDate'),
        articlesLoading: document.getElementById('articlesLoading'),
        articlesEmpty: document.getElementById('articlesEmpty'),
        articlesGrid: document.getElementById('articlesGrid'),
        paginationWrapper: document.getElementById('paginationWrapper'),
    };

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return d.toISOString().slice(0, 10); 
    }

    function renderIssueHeading(issue) {
        const volumeLabel = issue.volume?.volume ?? '-';
        els.heading.textContent = `Volume ${volumeLabel}, Issue ${issue.issue}, Year ${issue.year}`;
        els.publishedDate.textContent = formatDate(issue.published_date);
    }

    function renderArticles(articles) {
        els.articlesLoading.classList.add('d-none');

        if (!articles || articles.length === 0) {
            els.articlesEmpty.classList.remove('d-none');
            els.articlesGrid.innerHTML = '';
            return;
        }

        els.articlesEmpty.classList.add('d-none');

        els.articlesGrid.innerHTML = articles
            .map((article, index) => {
                const number = article._rowNumber ?? index + 1;

                const coAuthorNames = (article.co_authors || []).slice(0, 2).map((c) => c.name);
                let authorsText = escapeHtml(article.full_name);
                if (coAuthorNames.length > 0) {
                    authorsText += ', ' + coAuthorNames.map(escapeHtml).join(', ');
                    if ((article.co_authors || []).length > 2) {
                        authorsText += '...';
                    }
                }

                const pdfBtn = article.pdf_url
                    ? `<a href="${escapeHtml(article.pdf_url)}" class="pdf-btn">
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>`
                    : '';

                return `
                <div class="article-card">
                    <div class="article-number">${number}</div>

                    <div class="article-content">
                        <h3>
                            <a href="${articlesRouteBase}/${escapeHtml(article.uuid)}" class="link_connect">
                                ${escapeHtml(article.manuscript_title)}
                            </a>
                        </h3>

                        <p>
                            DOI :
                            <a href="#">https://doi.org/10.54392/ijrmt263</a>
                        </p>

                        <span class="authors">${authorsText}</span>

                        <div class="pages">317-665</div>
                    </div>

                    ${pdfBtn}
                </div>`;
            })
            .join('');
    }

    function renderPagination(pagination) {
        els.paginationWrapper.innerHTML = '';
        if (!pagination || pagination.last_page <= 1) return;

        const nav = document.createElement('nav');
        const ul = document.createElement('ul');
        ul.className = 'pagination';

        const makePageItem = (page, label, disabled, active) => {
            const li = document.createElement('li');
            li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = label;
            if (!disabled && !active) {
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    loadArticles(page);
                });
            }
            li.appendChild(a);
            return li;
        };

        ul.appendChild(makePageItem(pagination.current_page - 1, 'Previous', pagination.current_page <= 1, false));

        for (let p = 1; p <= pagination.last_page; p++) {
            ul.appendChild(makePageItem(p, String(p), false, p === pagination.current_page));
        }

        ul.appendChild(makePageItem(pagination.current_page + 1, 'Next', pagination.current_page >= pagination.last_page, false));

        nav.appendChild(ul);
        els.paginationWrapper.appendChild(nav);
    }

    function buildUrl(page) {
        const path = issueUuid ? `${apiBase}/${issueUuid}/articles` : `${apiBase}/articles`;
        return `${path}?page=${page}`;
    }

    async function loadArticles(page = 1) {
        els.articlesLoading.classList.remove('d-none');
        els.articlesGrid.innerHTML = '';
        els.articlesEmpty.classList.add('d-none');

        try {
            const res = await fetch(buildUrl(page), { headers: { Accept: 'application/json' } });
            const json = await res.json();

            if (!json.status) {
                els.articlesLoading.classList.add('d-none');
                els.articlesEmpty.classList.remove('d-none');
                return;
            }

            renderIssueHeading(json.data.issue);
            renderArticles(json.data.articles);
            renderPagination(json.data.pagination);
        } catch (err) {
            console.error('Failed to load current issue articles', err);
            els.articlesLoading.classList.add('d-none');
            els.articlesEmpty.classList.remove('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', () => loadArticles(1));
})();