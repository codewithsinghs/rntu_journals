// assets/frontend/home.js
document.addEventListener('DOMContentLoaded', function () {

    //Aim & Scope + Why RNTU ─────────────────────────────────────────────────────────────
       
    async function loadAimScopeWhy() {
        const loadingEl = document.getElementById('aimScopeLoading');
        if (!loadingEl) return;

        try {
            const res = await fetch('/api/public/home-content');
            if (!res.ok) throw new Error(`Request failed with status ${res.status}`);
            const json = await res.json();
            const c = json.data;

            loadingEl.classList.add('d-none');
            if (!c) return;

            if (c.aim_section_image) {
                document.getElementById('aimSectionImageWrap').innerHTML =
                    `<img src="/storage/${c.aim_section_image}" alt="Aim and Scope">`;
            }
            if (c.aim_and_scope_title_1) {
                document.getElementById('aimTitle1').textContent = c.aim_and_scope_title_1;
                document.getElementById('aimTitle1').classList.remove('d-none');
            }
            document.getElementById('aimTitle2').textContent = c.aim_and_scope_title_2 ?? '';
            document.getElementById('aimDescription').innerHTML = c.aim_and_scope_description ?? '';
            document.getElementById('aimTitle3').textContent = c.aim_and_scope_title_3 ?? '';
            document.getElementById('scopeDescription').innerHTML = c.scope_of_publication_description ?? '';

            if (c.university_highlight_quote) {
                document.getElementById('quoteText').innerHTML = c.university_highlight_quote;
                document.getElementById('quoteBoxWrap').classList.remove('d-none');
            }
            document.getElementById('aimScopeSection').classList.remove('d-none');

            if (c.why_rntu_title_1) {
                document.getElementById('whyTitle1').textContent = c.why_rntu_title_1;
                document.getElementById('whyTitle1').classList.remove('d-none');
            }
            document.getElementById('whyTitle2').textContent = c.why_rntu_title_2 ?? '';

            const features = [
                { value: c.why_rntu_years, label: c.why_rntu_years_label, icon: 'why_1.png' },
                { value: c.why_rntu_articles, label: c.why_rntu_articles_label, icon: 'why_2.png' },
                { value: c.why_rntu_journals, label: c.why_rntu_journals_label, icon: 'why_3.png' },
                { value: c.why_rntu_readers, label: c.why_rntu_readers_label, icon: 'why_4.png' },
                { value: c.why_rntu_access, label: c.why_rntu_access_label, icon: 'why_5.png' },
            ];

            const featuresWrap = document.getElementById('whyFeatures');
            features.forEach(f => {
                if (!f.value) return;
                const box = document.createElement('div');
                box.className = 'feature_box';
                box.innerHTML = `
                    <div class="feature_icon"><img src="/storage/home_page/${f.icon}" alt=""></div>
                    <h4>${f.value}</h4>
                    <p>${f.label ?? ''}</p>
                `;
                featuresWrap.appendChild(box);
            });

            if (c.support_section_heading) {
                document.getElementById('supportHeading').textContent = c.support_section_heading;
                document.getElementById('supportArticlesCount').textContent = c.support_articles_count ?? '';
                document.getElementById('supportShortHeading').textContent = c.support_short_heading ?? '';
                document.getElementById('supportDescription').innerHTML = c.support_section_description ?? '';
                document.getElementById('supportCard').classList.remove('d-none');
            }

            document.getElementById('whyRntuSection').classList.remove('d-none');
        } catch (e) {
            loadingEl.classList.add('d-none');
            console.error('Failed to load aim-scope/why-rntu content', e);
        }
    }

    // Journal Wrapper ─────────────────────────────────────────────────────────────

    function renderJournalCard(journal) {
        const fields = (journal.fields_covered ?? []).slice(0, 6);

        const fieldsHtml = fields.length ? `
            <div class="fields-box">
                <div class="fields-title">${journal.title_2 || 'Fields Covered'}</div>
                <div class="fields-grid">
                    ${fields.map((f, i) => `
                        <div class="field-item">
                            <span class="number">${i + 1}</span>
                            <span>${f}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : '';

        const exploreHtml = journal.explore_journals_link ? `
            <a href="${journal.explore_journals_link}" class="secondary-link">
                <div class="icon-circle">
                    <img src="/storage/home_page/explore_icon.png" alt="Explore"
                         onerror="this.style.display='none';">
                </div>
                ${journal.explore_journals_label || 'Explore Journals'}
            </a>
        ` : '';

        return `
            <div class="journal-card">
                <div class="journal-image">
                    ${journal.cover_image_url
                ? `<img src="${journal.cover_image_url}" alt="${journal.title}">`
                : `<div class="journal-cover-placeholder"></div>`}
                </div>
                <div class="journal-content">
                    <h2 style="font-size:24px;">${journal.title}</h2>
                    ${journal.description ? `<p>${journal.description}</p>` : ''}
                    ${fieldsHtml}
                    <div class="journal-buttons">
                        <a href="${journal.view_all_issues_link || '#'}" class="primary-btn">
                            ${journal.view_all_issues_label || 'View All Issues'}
                        </a>
                        ${exploreHtml}
                    </div>
                </div>
            </div>
        `;
    }

    async function loadJournalWrapper() {
        const wraps = document.querySelectorAll('.js-journal-wrapper');
        if (!wraps.length) return;

        try {
            const res = await fetch('/api/public/journals');
            if (!res.ok) throw new Error(`Request failed with status ${res.status}`);
            const json = await res.json();
            const journals = (json.data ?? []).slice(0, 2);

            const html = journals.length
                ? journals.map(renderJournalCard).join('')
                : `<div class="text-center py-4">No journals available.</div>`;

            const loadingEl = document.getElementById('journalWrapperLoading');
            if (loadingEl) loadingEl.classList.add('d-none');

            wraps.forEach(el => { el.innerHTML = html; });
        } catch (e) {
            const loadingEl = document.getElementById('journalWrapperLoading');
            if (loadingEl) loadingEl.classList.add('d-none');
            wraps.forEach(el => { el.innerHTML = `<div class="text-center py-4">Failed to load journals.</div>`; });
            console.error('Failed to load journals', e);
        }
    }

    //Latest Journal Wrapper ─────────────────────────────────────────────────────────────
    function renderSimpleJournalCard(journal) {
        const coverUrl = journal.cover_image_url || '/assets/home_page/latest_1.png';

        return `
        <div class="journal_card">
            <div class="journal_img">
                <img src="${coverUrl}" alt="${journal.title}">
            </div>
            <div class="journal_content">
                <h4>${journal.title}</h4>
                <ul>
                    <li>E-ISSN : ${journal.e_issn ?? ''} P-ISSN : ${journal.p_issn ?? ''}</li>
                    <li>Volume : ${journal.volume ?? ''} | Issue : ${journal.issue ?? ''}</li>
                    <li>Latest Volume : ${journal.latest_volume ?? ''}</li>
                    <li>Indexing & Impact Factor : ${journal.indexing_impact_factor ?? ''}</li>
                </ul>
            </div>
        </div>
    `;
    }

    async function loadSimpleJournalWrapper() {
        const listEl = document.querySelector('.js-journal-wrapper-simple');
        if (!listEl) return;

        try {
            const res = await fetch('/api/public/journals');
            if (!res.ok) throw new Error(`Request failed with status ${res.status}`);
            const json = await res.json();
            const journals = (json.data ?? []).slice(0, 2);

            listEl.innerHTML = journals.length
                ? journals.map(renderSimpleJournalCard).join('')
                : `<div class="text-center py-4">No journals available.</div>`;
        } catch (e) {
            listEl.innerHTML = `<div class="text-center py-4">Failed to load journals.</div>`;
            console.error('Failed to load simple journal wrapper', e);
        }
    }

    //Announcement Bar  ─────────────────────────────────────────────────────────────  
    async function loadAnnouncements() {
        const wrapEl = document.getElementById('announcementContent');
        if (!wrapEl) return;

        try {
            const res = await fetch('/api/public/announcements');
            if (!res.ok) throw new Error(`Request failed with status ${res.status}`);
            const json = await res.json();
            const announcements = json.data ?? [];

            if (!announcements.length) {
                wrapEl.innerHTML = `<div class="announcement-item">No announcements available.</div>`;
                return;
            }

            const itemHtml = a => `
                <div class="announcement-item">
                    <a href="${a.url}" target="_blank">📢 ${a.name}</a>
                </div>
            `;

            wrapEl.innerHTML = announcements.map(itemHtml).join('') + announcements.map(itemHtml).join('');
        } catch (e) {
            wrapEl.innerHTML = `<div class="announcement-item">Failed to load announcements.</div>`;
            console.error('Failed to load announcements', e);
        }
    }

    //Latest Issues Section ─────────────────────────────────────────────────────────────
    async function loadLatestJournalSection() {
        const headingEl = document.getElementById('latestJournalHeading');
        if (!headingEl) return;

        try {
            const [homeRes, articlesRes] = await Promise.all([
                fetch('/api/public/home-content'),
                fetch('/api/public/latest-articles'),
            ]);
            const homeJson = await homeRes.json();
            const articlesJson = await articlesRes.json();

            const c = homeJson.data;
            if (c) {
                if (c.latest_journal_title) {
                    document.getElementById('latestJournalTitle').textContent = c.latest_journal_title;
                    document.getElementById('latestJournalTitle').classList.remove('d-none');
                }
                headingEl.textContent = c.latest_journal_heading ?? '';
                document.getElementById('latestJournalDescription').innerHTML = c.latest_journal_description ?? '';
            }

            const { latest = [], by_year = {} } = articlesJson.data ?? {};

            const renderArticle = a => `
                <div class="issue_item">
                    <div class="issue_date">
                        <h4>${new Date(a.created_at).getDate().toString().padStart(2, '0')}</h4>
                        <span>${new Date(a.created_at).toLocaleString('en-US', { month: 'short' })}</span>
                    </div>
                    <div class="issue_content">
                        <h5><a href="/article/${a.uuid}" class="link_connect">${a.manuscript_title}</a></h5>
                        <p>${a.full_name}</p>
                    </div>
                </div>
            `;

            const latestTab = document.getElementById('tab-latest');
            if (latestTab) {
                latestTab.innerHTML = latest.length
                    ? latest.map(renderArticle).join('')
                    : '<p>No articles approved yet.</p>';
            }

            const tabsWrap = document.getElementById('issueTabs');
            const tabsContentWrap = document.getElementById('issueTabsContent');
            if (tabsWrap && tabsContentWrap) {
                Object.keys(by_year).sort((a, b) => b - a).forEach(year => {
                    const btn = document.createElement('button');
                    btn.className = 'tab-btn';
                    btn.textContent = year;
                    btn.onclick = (e) => openCity(e, `tab-${year}`);
                    tabsWrap.appendChild(btn);

                    const tabDiv = document.createElement('div');
                    tabDiv.id = `tab-${year}`;
                    tabDiv.className = 'w3-container city';
                    tabDiv.style.display = 'none';
                    tabDiv.innerHTML = by_year[year].map(renderArticle).join('');
                    tabsContentWrap.appendChild(tabDiv);
                });
            }
        } catch (e) {
            console.error('Failed to load latest journal section', e);
        }
    }

    //Run whichever sections exist on the current page ───────────────────────────────────────────────────────────── 
    loadAimScopeWhy();
    loadJournalWrapper();
    loadSimpleJournalWrapper();
    loadAnnouncements();
    loadLatestJournalSection();

});