// public/assets/js/journal-detail.js

(function () {
    'use strict';

    const app = document.getElementById('journalDetailApp');
    if (!app) return;

    const journalId = app.dataset.journalId;
    const apiBase = app.dataset.apiBase; // e.g. /api/journals

    const els = {
        loading: document.getElementById('journalLoading'),
        error: document.getElementById('journalError'),
        card: document.getElementById('journalCard'),
        coverImage: document.getElementById('journalCoverImage'),
        description: document.getElementById('journalDescription'),
        fieldsGrid: document.getElementById('journalFieldsGrid'),
        aimScopeSection: document.getElementById('aimScopeSection'),
        aimScopeTitle: document.getElementById('aimScopeTitle'),
        aimScopeContent: document.getElementById('aimScopeContent'),

        articlesLoading: document.getElementById('articlesLoading'),
        articlesGrid: document.getElementById('articlesGrid'),
        articlesEmpty: document.getElementById('articlesEmpty'),
        paginationWrapper: document.getElementById('paginationWrapper'),
    };

    // Field definitions: [dataKey, label, formatter]
    const FIELD_DEFS = [
        ['e_issn', 'E-ISSN'],
        ['p_issn', 'P-ISSN'],
        ['issn_online', 'ISSN', (v) => `${v} (Online)`],
        ['abbreviation', 'Journal Abbreviation'],
        ['publication_language', 'Publication language'],
        ['publishing_frequency', 'Publishing frequency'],
        ['publishing_months', null, (v) => `(${v})`],
        ['volume', 'Volume'],
        ['issue', 'Issue'],
        ['time_to_first_decision', 'Time to First Decision'],
        ['time_to_review', 'Time to Review'],
        ['acceptance_to_publication', 'Acceptance to Publication'],
        ['latest_volume', 'Latest Volume'],
    ];

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderJournal(journal) {
        els.coverImage.src = journal.cover_image_url;
        els.coverImage.alt = journal.title ?? '';

        if (journal.description) {
            els.description.innerHTML = journal.description;
            els.description.classList.remove('d-none');
        } else {
            els.description.classList.add('d-none');
        }

        // Article template link (special case: has an <a> tag)
        const fieldsHtml = FIELD_DEFS
            .filter(([key]) => journal[key])
            .map(([key, label, formatter]) => {
                const value = formatter ? formatter(journal[key]) : journal[key];
                const text = label ? `${label} : ${value}` : value;
                return `<div class="field-item field-item-2">${escapeHtml(text)}</div>`;
            })
            .join('');

        let templateHtml = '';
        if (journal.article_template_url) {
            templateHtml = `<div class="field-item field-item-2">
                Download Article Template <a href="${escapeHtml(journal.article_template_url)}" target="_blank">click here</a>
            </div>`;
        }

        els.fieldsGrid.innerHTML = fieldsHtml + templateHtml;

        if (journal.aim_and_scope) {
            els.aimScopeTitle.textContent = journal.aim_and_scope_title || 'Aim and Scope';
            els.aimScopeContent.innerHTML = journal.aim_and_scope;
            els.aimScopeSection.classList.remove('d-none');
        }

        els.loading.classList.add('d-none');
        els.card.classList.remove('d-none');
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
                const number = (article._rowNumber ?? index + 1);
                const authors = article.co_authors || article.full_name || '';
                const pdfBtn = article.pdf_url
                    ? `<a href="${escapeHtml(article.pdf_url)}" class="pdf-btn" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>`
                    : '';

                return `
                <div class="article-card">
                    <div class="article-number">${number}</div>
                    <div class="article-content">
                        <h3>
                            <a href="/article/${escapeHtml(article.uuid)}" class="link_connect">
                                ${escapeHtml(article.manuscript_title)}
                            </a>
                        </h3>
                        <span class="authors">${escapeHtml(authors)}</span>
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

    async function loadArticles(page = 1) {
        els.articlesLoading.classList.remove('d-none');
        els.articlesGrid.innerHTML = '';
        els.articlesEmpty.classList.add('d-none');

        try {
            const res = await fetch(`${apiBase}/${journalId}/detail?page=${page}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();

            if (!json.status) {
                els.articlesLoading.classList.add('d-none');
                els.articlesEmpty.classList.remove('d-none');
                return;
            }

            renderArticles(json.data.articles);
            renderPagination(json.data.pagination);
        } catch (err) {
            console.error('Failed to load articles', err);
            els.articlesLoading.classList.add('d-none');
            els.articlesEmpty.classList.remove('d-none');
        }
    }

    async function init() {
        try {
            const res = await fetch(`${apiBase}/${journalId}/detail`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();

            if (!json.status) {
                els.loading.classList.add('d-none');
                els.error.classList.remove('d-none');
                els.articlesLoading.classList.add('d-none');
                return;
            }

            renderJournal(json.data.journal);
            renderArticles(json.data.articles);
            renderPagination(json.data.pagination);
        } catch (err) {
            console.error('Failed to load journal detail', err);
            els.loading.classList.add('d-none');
            els.error.classList.remove('d-none');
            els.articlesLoading.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();