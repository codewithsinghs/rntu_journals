// public/assets/js/archives.js

(function () {
    'use strict';

    const app = document.getElementById('archivesApp');
    if (!app) return;

    const journalId = app.dataset.journalId;
    const apiBase = app.dataset.apiBase; 
    const els = {
        loading: document.getElementById('archivesLoading'),
        empty: document.getElementById('archivesEmpty'),
        container: document.getElementById('yearBlocksContainer'),
        sortSelect: document.getElementById('sortSelect'),
        searchInput: document.getElementById('searchInput'),
    };

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function issueRoute(uuid) {
        // Mirrors: route('current-issues', $issue->uuid)
        return `/current-issues/${encodeURIComponent(uuid)}`;
    }

    function renderYearBlocks(issuesByYear) {
        const years = Object.keys(issuesByYear).sort((a, b) => b - a);

        if (years.length === 0) {
            els.empty.classList.remove('d-none');
            els.container.innerHTML = '';
            return;
        }

        els.empty.classList.add('d-none');

        els.container.innerHTML = years
            .map((year) => {
                const issuesInYear = issuesByYear[year];
                const count = issuesInYear.length;
                const label = count === 1 ? 'Issue' : 'Issues';

                const cards = issuesInYear
                    .map((issue) => {
                        const title = `Volume ${issue.volume ?? '-'} Issue ${issue.issue} Year ${issue.year}`;
                        const published = issue.published_date
                            ? issue.published_date
                            : '-';

                        return `
                        <a href="${issueRoute(issue.uuid)}" class="arc-card"
                            data-title="${escapeHtml(title)}"
                            data-date="${escapeHtml(issue.created_at)}">

                            <div class="arc-card-icon"><img src="${escapeHtml('/storage/book_icon.png')}"></div>

                            <div class="arc-card-info">
                                <h4>Volume ${escapeHtml(issue.volume)}, Issue ${escapeHtml(issue.issue)}, Year ${escapeHtml(issue.year)}</h4>
                                <p>Published: ${escapeHtml(published)}</p>
                            </div>

                            <div class="arc-arrow">&rsaquo;</div>
                        </a>`;
                    })
                    .join('');

                return `
                <div class="arc-year-block">
                    <div class="arc-year-header">
                        <span class="arc-toggle">&#9660;</span>
                        <span class="arc-year">${escapeHtml(year)}</span>
                        <span class="arc-badge">${count} ${label}</span>
                    </div>
                    <div class="arc-year-content">
                        <div class="arc-grid">
                            ${cards}
                        </div>
                    </div>
                </div>`;
            })
            .join('');
    }

    async function loadArchives() {
        els.loading.classList.remove('d-none');
        els.empty.classList.add('d-none');
        els.container.innerHTML = '';

        try {
            const res = await fetch(`${apiBase}/${journalId}/archives`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();

            els.loading.classList.add('d-none');

            if (!json.status) {
                els.empty.classList.remove('d-none');
                return;
            }

            renderYearBlocks(json.data.issues || {});
        } catch (err) {
            console.error('Failed to load archives', err);
            els.loading.classList.add('d-none');
            els.empty.classList.remove('d-none');
        }
    }

    // Event delegation: works even though year blocks/cards are added dynamically
    els.container.addEventListener('click', (e) => {
        const header = e.target.closest('.arc-year-header');
        if (!header) return;

        const content = header.nextElementSibling;
        const icon = header.querySelector('.arc-toggle');

        content.classList.toggle('arc-hide');
        icon.innerHTML = content.classList.contains('arc-hide') ? '&#9658;' : '&#9660;';
    });

    if (els.searchInput) {
        els.searchInput.addEventListener('keyup', function () {
            const value = this.value.toLowerCase();

            els.container.querySelectorAll('.arc-card').forEach((card) => {
                const title = card.dataset.title.toLowerCase();
                card.style.display = title.includes(value) ? 'flex' : 'none';
            });
        });
    }

    if (els.sortSelect) {
        els.sortSelect.addEventListener('change', function () {
            const direction = this.value;

            els.container.querySelectorAll('.arc-grid').forEach((grid) => {
                const cards = [...grid.querySelectorAll('.arc-card')];

                cards.sort((a, b) => {
                    const d1 = new Date(a.dataset.date);
                    const d2 = new Date(b.dataset.date);
                    return direction === 'newest' ? d2 - d1 : d1 - d2;
                });

                cards.forEach((card) => grid.appendChild(card));
            });
        });
    }

    document.addEventListener('DOMContentLoaded', loadArchives);
})();