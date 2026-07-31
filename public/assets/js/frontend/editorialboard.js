// public/assets/js/editorial-board.js

(function () {
    'use strict';

    const app = document.getElementById('editorialBoardApp');
    if (!app) return;

    const journalParam = app.dataset.journalParam || '';
    const apiBase = app.dataset.apiBase; 

    const els = {
        loading: document.getElementById('boardLoading'),
        empty: document.getElementById('boardEmpty'),
        content: document.getElementById('boardContent'),
    };

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function memberFields(member) {
        return `
            ${member.designation ? `<p>${escapeHtml(member.designation)}</p>` : ''}
            ${member.department ? `<p>${escapeHtml(member.department)}</p>` : ''}
            ${member.institute ? `<p>${escapeHtml(member.institute)}</p>` : ''}
            ${member.university_or_org ? `<p>${escapeHtml(member.university_or_org)}</p>` : ''}
            ${member.city ? `<p>${escapeHtml(member.city)}</p>` : ''}
        `;
    }

    function photoHtml(member) {
        return member.profile_image_url
            ? `<img src="${escapeHtml(member.profile_image_url)}" alt="${escapeHtml(member.name)}" class="editor-photo">`
            : '';
    }

    function linksHtml(member, { includeOrcidFirst = false } = {}) {
        const orcid = member.orcid_url
            ? `<a href="${escapeHtml(member.orcid_url)}" target="_blank" rel="noopener">ORCID</a>`
            : '';
        const scopus = member.scopus_url
            ? `<a href="${escapeHtml(member.scopus_url)}" target="_blank" rel="noopener">Scopus</a>`
            : '';
        const wos = member.web_of_science_url
            ? `<a href="${escapeHtml(member.web_of_science_url)}" target="_blank" rel="noopener">Web of Science</a>`
            : '';

        return includeOrcidFirst
            ? `<div class="links">${orcid}${scopus}${wos}</div>`
            : `<div class="links">${scopus}${wos}${orcid}</div>`;
    }

    function renderEditorInChief(members) {
        if (!members || members.length === 0) return '';

        const cards = members.map((member) => `
            <div class="editor-card single-card">
                ${photoHtml(member)}
                <h3>${escapeHtml(member.name)}</h3>
                ${memberFields(member)}
                ${member.email ? `<p>Email: ${escapeHtml(member.email)}</p>` : ''}
                ${linksHtml(member, { includeOrcidFirst: true })}
            </div>
        `).join('');

        return `
        <section class="editorial-section">
            <div class="section-title">Editor-in-Chief</div>
            ${cards}
        </section>`;
    }

    function renderManagingExecutive(roles) {
        const managing = roles['Managing Editor'] || [];
        const executive = roles['Executive Editor'] || [];

        if (managing.length === 0 && executive.length === 0) return '';

        const renderRole = (role, members) => {
            if (members.length === 0) return '';
            const cards = members.map((member) => `
                <div class="editor-card">
                    ${photoHtml(member)}
                    <h3>${escapeHtml(member.name)}</h3>
                    ${memberFields(member)}
                    ${member.email ? `<p>Email: ${escapeHtml(member.email)}</p>` : ''}
                    ${linksHtml(member)}
                </div>
            `).join('');

            return `
            <div class="editorial-section">
                <div class="section-title">${escapeHtml(role)}</div>
                ${cards}
            </div>`;
        };

        return `
        <section class="grid-two">
            ${renderRole('Managing Editor', managing)}
            ${renderRole('Executive Editor', executive)}
        </section>`;
    }

    function renderThreeUpSection(role, members) {
        if (!members || members.length === 0) return '';

        const cards = members.map((member) => `
            <div class="editor-card-border">
                ${photoHtml(member)}
                <h3>${escapeHtml(member.name)}</h3>
                ${memberFields(member)}
                ${linksHtml(member)}
            </div>
        `).join('');

        return `
        <section class="editorial-section">
            <div class="section-title">${escapeHtml(role)}</div>
            <div class="grid-three">
                ${cards}
            </div>
        </section>`;
    }

    function renderBoard(data) {
        const roles = data.roles || {};

        const allEmpty = Object.values(roles).every((list) => !list || list.length === 0);
        if (allEmpty) {
            els.empty.classList.remove('d-none');
            els.content.innerHTML = '';
            els.loading.classList.add('d-none');
            return;
        }

        let html = '';
        html += renderEditorInChief(roles['Editor-in-Chief']);
        html += renderManagingExecutive(roles);
        html += renderThreeUpSection('Editors', roles['Editors']);
        html += renderThreeUpSection('Associate Editors', roles['Associate Editors']);
        html += renderThreeUpSection('Members', roles['Members']);

        els.content.innerHTML = html;
        els.loading.classList.add('d-none');
    }

    async function init() {
        try {
            const url = journalParam
                ? `${apiBase}/${encodeURIComponent(journalParam)}`
                : `${apiBase}`;

            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            const json = await res.json();

            if (!json.status) {
                els.loading.classList.add('d-none');
                els.empty.classList.remove('d-none');
                return;
            }

            renderBoard(json.data);
        } catch (err) {
            console.error('Failed to load editorial board', err);
            els.loading.classList.add('d-none');
            els.empty.classList.remove('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();