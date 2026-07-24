@extends('layouts.admin')

@section('content')
    {{--  Journal Statistics (card_d pattern) --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Journal Statistics</div>

                <div class="grid_colums_card">

                    <div class="card_d">
                        <div class="card-content">
                            <p>Total Journals</p>
                            <h3 id="statTotal">0 Journals</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_1.png">
                        </div>
                    </div>

                    <div class="card_d">
                        <div class="card-content">
                            <p>Active Journals</p>
                            <h3 id="statActive">0 Journals</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_2.png">
                        </div>
                    </div>

                    <div class="card_d">
                        <div class="card-content">
                            <p>Inactive Journals</p>
                            <h3 id="statInactive">0 Journals</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_2.png">
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{--  Journal List (status-table pattern) --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Journal List</div>

                <div class="table-controls">
                    <button class="add-btn" id="openAddBtn" onclick="openCreateModal()">Add Journal</button> &nbsp; &nbsp;
                    <input type="text" id="searchInput" class="form-control form-control-sm" oninput="onSearchInput()"
                        placeholder="Search by title..." style="max-width: 240px;">
                </div>

                <div id="tableLoading" class="text-center py-4">Loading...</div>
                <div id="tableEmpty" class="text-center py-4" style="display:none;">No journals found.</div>

                <div class="table-container" style="margin: 0; display:none;" id="tableWrap">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Abbreviation</th>
                                <th>ISSN</th>
                                <th>Volume / Issue</th>
                                <th>Sequence</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="journalTableBody"></tbody>
                    </table>
                </div>

                <div class="jm-toolbar">
                    <div>
                        <select id="perPage" class="form-select form-select-sm" style="width: 90px; display:inline-block;"
                            onchange="onPerPageChange()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-muted small ms-1">entries</span>
                    </div>

                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="entriesInfo">Showing 0 to 0 of 0 entries</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

    {{--  Add / Edit Journal Modal (input-set / reason / bottom-btn pattern) --}}
    <div class="modal fade" id="AddJournal" tabindex="-1" aria-labelledby="AddJournalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="journalForm">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title" id="journalModalTitle">Add Journal</div>
                        </div>

                        <input type="hidden" id="journal_id">

                        <div class="middle-3 middle">

                            {{-- Cover Image --}}
                            <span class="input-set">
                                <label>Cover Image</label>
                                <input type="file" id="cover_image" accept="image/*">
                                <img id="coverPreviewCurrent" class="journal-page-img-show" style="display:none;">
                            </span>

                            {{-- Title --}}
                            <span class="input-set">
                                <label>Title *</label>
                                <input type="text" id="title" placeholder="e.g. Anusandhan (RNTUJ-AN)">
                            </span>

                            {{-- Heading / title_2 --}}
                            <span class="input-set">
                                <label>Heading (secondary title)</label>
                                <input type="text" id="heading_1" placeholder="e.g. Our Flagship Journal">
                            </span>

                            {{-- Abbreviation --}}
                            <span class="input-set">
                                <label>Abbreviation</label>
                                <input type="text" id="abbreviation"
                                    placeholder="e.g. Int. Res. J. Multidiscip. Technovation">
                            </span>

                            {{-- Badge --}}
                            <span class="input-set">
                                <label>Badge</label>
                                <input type="text" id="badge">
                            </span>

                            {{-- ISSN --}}
                            <span class="input-set">
                                <label>e-ISSN</label>
                                <input type="text" id="e_issn">
                            </span>

                            <span class="input-set">
                                <label>p-ISSN</label>
                                <input type="text" id="p_issn">
                            </span>

                            <span class="input-set">
                                <label>ISSN Online</label>
                                <input type="text" id="issn_online">
                            </span>

                            {{-- Volume / Issue --}}
                            <span class="input-set">
                                <label>Volume</label>
                                <input type="text" id="volume">
                            </span>

                            <span class="input-set">
                                <label>Issue</label>
                                <input type="text" id="issue">
                            </span>

                            <span class="input-set">
                                <label>Latest Volume</label>
                                <input type="text" id="latest_volume" placeholder="e.g. Sept, 2025">
                            </span>

                            {{-- Publishing info --}}
                            <span class="input-set">
                                <label>Publication Language</label>
                                <input type="text" id="publication_language">
                            </span>

                            <span class="input-set">
                                <label>Publishing Frequency</label>
                                <input type="text" id="publishing_frequency" placeholder="e.g. Bimonthly">
                            </span>

                            <span class="input-set">
                                <label>Publishing Months</label>
                                <input type="text" id="publishing_months"
                                    placeholder="e.g. Jan, Mar, May, Jul, Sept, Nov">
                            </span>

                            {{-- Indexing / review timeline --}}
                            <span class="input-set">
                                <label>Impact Factor</label>
                                <input type="text" id="indexing_impact_factor">
                            </span>

                            <span class="input-set">
                                <label>Time to First Decision</label>
                                <input type="text" id="time_to_first_decision">
                            </span>

                            <span class="input-set">
                                <label>Time to Review</label>
                                <input type="text" id="time_to_review">
                            </span>

                            <span class="input-set">
                                <label>Acceptance to Publication</label>
                                <input type="text" id="acceptance_to_publication">
                            </span>

                            {{-- Article Template --}}
                            <span class="input-set">
                                <label>Article Template URL</label>
                                <input type="text" id="article_template_url">
                            </span>

                            {{-- Sequence + Status --}}
                            <span class="input-set">
                                <label>Sequence</label>
                                <input type="number" id="sequence" value="0" min="0">
                            </span>

                            <span class="input-set">
                                <label>Status</label>
                                <select class="form-select" id="is_active">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </span>

                        </div>

                        {{-- Redirect / CTA Buttons --}}
                        <div class="middle mt-4">

                            <span class="input-set">
                                <label>"View All Issues" Label</label>
                                <input type="text" id="view_all_issues_label" placeholder="e.g. View All Issues">
                            </span>

                            <span class="input-set">
                                <label>"View All Issues" Link</label>
                                <input type="text" id="view_all_issues_link" placeholder="https://...">
                            </span>

                            <span class="input-set">
                                <label>"Explore Journals" Label</label>
                                <input type="text" id="explore_journals_label" placeholder="e.g. Explore Journals">
                            </span>

                            <span class="input-set">
                                <label>"Explore Journals" Link</label>
                                <input type="text" id="explore_journals_link" placeholder="https://...">
                            </span>

                        </div>

                        {{-- Description (CKEditor) --}}
                        <div class="reason">
                            <label>Journal Description</label>
                            <textarea class="d-none" id="description"></textarea>
                        </div>

                        {{-- Aim & Scope --}}
                        <div class="reason">
                            <label>Aim & Scope Title</label>
                            <input type="text" id="aim_and_scope_title" class="form-control"
                                placeholder="e.g. Aim & Scope">
                        </div>

                        <div class="reason">
                            <label>Aim & Scope</label>
                            <textarea class="d-none" id="aim_and_scope"></textarea>
                        </div>

                        {{-- Fields Covered --}}
                        <div class="reason">
                            <label>Fields Covered</label>
                            <div id="fieldsCoveredContainer"></div>
                            <button type="button" class="edit-btn mt-2" id="addFieldBtn">+ Add Field</button>
                        </div>

                    </div>

                    <div class="bottom-btn mb-5">
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close">
                            Cancel</button>
                        <button type="submit" class="green" id="saveJournalBtn"> <span id="saveJournalBtnText">Create
                                Journal</span> </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="delete_popup" tabindex="-1" aria-labelledby="delete_popupLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title-remove">Confirm Delete</div>
                    </div>

                    <div class="middle-content">
                        <span>
                            Do you really want to delete "<strong id="deleteJournalName"></strong>"? <br>
                            The cover image will also be deleted. This action cannot be undone.
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="confirmDeleteBtn"> Delete </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Keep it
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{--  Toast --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="ecToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="ecToastIcon"></span>
                    <div>
                        <div id="ecToastTitle" class="fw-semibold" style="font-size:14px;"></div>
                        <div id="ecToastMsg" class="opacity-75" style="font-size:13px;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="ecToastBarInner"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- CKEditor 5 CDN --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <script>
        const API = "/api/admin/journals";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let addJournalModal, deletePopupModal;
        let isEditMode = false;
        let pendingDeleteId = null;
        let descEditor = null;
        let aimScopeEditor = null;

        let allJournals = [];
        let filteredJournals = [];
        let currentPage = 1;
        let perPage = 10;

        const TOOLBAR = [
            'heading', '|',
            'bold', 'italic', 'underline', '|',
            'bulletedList', 'numberedList', '|',
            'blockQuote', 'link', '|',
            'undo', 'redo',
        ];

        // ── Boot ─────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            addJournalModal = new bootstrap.Modal(document.getElementById('AddJournal'));
            deletePopupModal = new bootstrap.Modal(document.getElementById('delete_popup'));

            perPage = parseInt(document.getElementById('perPage').value, 10);
            loadJournals();

            ClassicEditor.create(document.getElementById('description'), {
                toolbar: {
                    items: TOOLBAR
                },
                placeholder: 'Brief description of the journal...',
            }).then(editor => {
                descEditor = editor;
                editor.model.document.on('change:data', () => {
                    document.getElementById('description').value = editor.getData();
                });
            }).catch(err => console.error('CKEditor init failed:', err));

            ClassicEditor.create(document.getElementById('aim_and_scope'), {
                toolbar: {
                    items: TOOLBAR
                },
                placeholder: "Describe the journal's aim & scope...",
            }).then(editor => {
                aimScopeEditor = editor;
                editor.model.document.on('change:data', () => {
                    document.getElementById('aim_and_scope').value = editor.getData();
                });
            }).catch(err => console.error('CKEditor init failed:', err));

            document.getElementById('journalForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveJournal();
            });

            document.getElementById('addFieldBtn').addEventListener('click', () => addFieldRow());

            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (pendingDeleteId === null) return;
                deletePopupModal.hide();
                executeDelete(pendingDeleteId);
                pendingDeleteId = null;
            });
        });

        // ── Toast ──────────────────────────────────────────────────────
        function showToast(type, title, msg) {
            const el = document.getElementById('ecToast');
            if (!el) return;
            document.getElementById('ecToastTitle').textContent = title;
            const msgEl = document.getElementById('ecToastMsg');
            msgEl.textContent = msg || '';
            msgEl.style.display = msg ? 'block' : 'none';
            document.getElementById('ecToastIcon').innerHTML = type === 'success' ?
                `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>` :
                `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
            el.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'text-dark');
            if (type === 'success') el.classList.add('bg-success');
            else if (type === 'warning') el.classList.add('bg-warning', 'text-dark');
            else el.classList.add('bg-danger');
            const bar = document.getElementById('ecToastBarInner');
            bar.style.transition = 'none';
            bar.style.width = '100%';
            requestAnimationFrame(() => requestAnimationFrame(() => {
                bar.style.transition = 'width 4s linear';
                bar.style.width = '0%';
            }));
            bootstrap.Toast.getOrCreateInstance(el, {
                delay: 4000,
                autohide: true
            }).show();
        }

        function showToastLegacy(message, type = 'success') {
            const titles = {
                success: 'Success',
                danger: 'Error',
                warning: 'Heads up'
            };
            showToast(type === 'danger' ? 'error' : type, titles[type] || 'Notice', message);
        }

        function formHeaders() {
            return {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            };
        }

        // ── Load journals ────────────────────────────────────────────
        function loadJournals() {
            document.getElementById('tableLoading').style.display = 'block';
            document.getElementById('tableEmpty').style.display = 'none';
            document.getElementById('tableWrap').style.display = 'none';

            fetch(API, {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(handleAuthErrors)
                .then(res => res.json())
                .then(data => {
                    allJournals = (data.data && data.data.data) ? data.data.data : (data.data || []);
                    applyFilterAndRender();
                    updateStats();
                })
                .catch(err => {
                    document.getElementById('tableLoading').style.display = 'none';
                    if (err.message === 'Not authenticated') return;
                    showToastLegacy('Failed to load journals.', 'danger');
                });
        }

        function updateStats() {
            const total = allJournals.length;
            const active = allJournals.filter(j => !!j.is_active).length;
            const inactive = total - active;

            document.getElementById('statTotal').textContent = `${total} Journals`;
            document.getElementById('statActive').textContent = `${active} Journals`;
            document.getElementById('statInactive').textContent = `${inactive} Journals`;
        }

        // ── Search + Pagination ──────────────────────────────────────
        function onSearchInput() {
            currentPage = 1;
            applyFilterAndRender();
        }

        function onPerPageChange() {
            perPage = parseInt(document.getElementById('perPage').value, 10);
            currentPage = 1;
            applyFilterAndRender();
        }

        function applyFilterAndRender() {
            const query = document.getElementById('searchInput').value.trim().toLowerCase();
            filteredJournals = query ?
                allJournals.filter(j => (j.title || '').toLowerCase().includes(query)) :
                allJournals;

            document.getElementById('tableLoading').style.display = 'none';
            renderTable();
            renderPagination();
        }

        function renderTable() {
            const tbody = document.getElementById('journalTableBody');

            if (!filteredJournals.length) {
                document.getElementById('tableEmpty').style.display = 'block';
                document.getElementById('tableWrap').style.display = 'none';
                document.getElementById('entriesInfo').textContent = 'Showing 0 to 0 of 0 entries';
                tbody.innerHTML = '';
                return;
            }

            document.getElementById('tableEmpty').style.display = 'none';
            document.getElementById('tableWrap').style.display = 'block';

            const totalPages = Math.max(1, Math.ceil(filteredJournals.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;
            const pageItems = filteredJournals.slice(start, start + perPage);

            let rows = '';
            pageItems.forEach(j => {
                const cover = j.cover_image ?
                    `<img src="/storage/${j.cover_image}" class="table-cover-journal-image" alt="">` :
                    `<div class="table-cover-journal-image--empty">N/A</div>`;

                const issn = j.e_issn || j.p_issn || j.issn_online || '-';
                const volIssue = (j.volume || j.issue) ? `Vol ${j.volume ?? '-'} / Issue ${j.issue ?? '-'}` : '-';

                rows += `
                <tr>
                    <td>${cover}</td>
                    <td><strong>${escapeHtml(j.title || '')}</strong></td>
                    <td>${escapeHtml(j.abbreviation || '-')}</td>
                    <td>${escapeHtml(issn)}</td>
                    <td>${escapeHtml(volIssue)}</td>
                    <td>${j.sequence ?? 0}</td>
                    <td>
                        <button class="green-btn" style="color:${j.is_active ? 'white' : 'White'};"
                                onclick="toggleStatus(${j.id})">
                            ${j.is_active ? 'Active' : 'Inactive'}
                        </button>
                    </td>
                    <td>
                        <button class="edit-btn" onclick="openEditModal(${j.id})">Edit</button>
                        <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#delete_popup"
                                onclick="promptDelete(${j.id}, '${escAttr(j.title || '')}')">Delete</button>
                    </td>
                </tr>`;
            });

            tbody.innerHTML = rows;
            document.getElementById('entriesInfo').textContent =
                `Showing ${start + 1} to ${Math.min(start + perPage, filteredJournals.length)} of ${filteredJournals.length} entries`;
        }

        function renderPagination() {
            const totalPages = Math.max(1, Math.ceil(filteredJournals.length / perPage));
            const ul = document.getElementById('pagination');
            let html = '';

            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">Previous</a>
                 </li>`;
            for (let p = 1; p <= totalPages; p++) {
                html += `<li class="page-item ${p === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="goToPage(${p}); return false;">${p}</a>
                     </li>`;
            }
            html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">Next</a>
                 </li>`;

            ul.innerHTML = html;
        }

        function goToPage(page) {
            const totalPages = Math.max(1, Math.ceil(filteredJournals.length / perPage));
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable();
            renderPagination();
        }

        // ── Fields Covered repeater ──────────────────────────────────
        function addFieldRow(value) {
            value = value || '';
            const container = document.getElementById('fieldsCoveredContainer');
            const row = document.createElement('div');
            row.className = 'journal-fileds';
            row.innerHTML = `
            <input type="text" class="form-control field-covered-input" placeholder="e.g. Computer Science" value="${escAttr(value)}">
            <button type="button" class="edit-btn" onclick="this.closest('.journal-fileds').remove()">✕</button>`;
            container.appendChild(row);
        }

        function collectFieldsCovered() {
            return Array.from(document.querySelectorAll('.field-covered-input'))
                .map(i => i.value.trim())
                .filter(v => v.length > 0);
        }

        // ── Create / Edit ─────────────────────────────────────────────
        function openCreateModal() {
            isEditMode = false;
            document.getElementById('journalForm').reset();
            document.getElementById('journal_id').value = '';
            document.getElementById('journalModalTitle').textContent = 'Add Journal';
            document.getElementById('saveJournalBtnText').textContent = 'Create Journal';
            document.getElementById('coverPreviewCurrent').style.display = 'none';
            document.getElementById('is_active').value = '1';
            document.getElementById('sequence').value = '0';

            if (descEditor) descEditor.setData('');
            if (aimScopeEditor) aimScopeEditor.setData('');

            document.getElementById('fieldsCoveredContainer').innerHTML = '';
            addFieldRow();

            addJournalModal.show();
        }

        function openEditModal(id) {
            const journal = allJournals.find(j => j.id === id);
            if (!journal) {
                showToastLegacy('Journal not found.', 'danger');
                return;
            }

            isEditMode = true;
            document.getElementById('journal_id').value = journal.id;
            document.getElementById('journalModalTitle').textContent = 'Edit Journal';
            document.getElementById('saveJournalBtnText').textContent = 'Update Journal';

            const fields = ['title', 'abbreviation', 'badge', 'e_issn', 'p_issn', 'issn_online', 'volume', 'issue',
                'latest_volume', 'publication_language', 'publishing_frequency', 'publishing_months',
                'indexing_impact_factor', 'time_to_first_decision', 'time_to_review', 'acceptance_to_publication',
                'article_template_url', 'sequence', 'aim_and_scope_title',
                'view_all_issues_label', 'view_all_issues_link', 'explore_journals_label', 'explore_journals_link'
            ];

            fields.forEach(f => {
                const el = document.getElementById(f);
                if (el) el.value = journal[f] ?? '';
            });

            document.getElementById('heading_1').value = journal.title_2 || journal.heading_1 || '';
            document.getElementById('is_active').value = journal.is_active ? '1' : '0';

            if (descEditor) descEditor.setData(journal.description || '');
            if (aimScopeEditor) aimScopeEditor.setData(journal.aim_and_scope || '');

            document.getElementById('coverPreviewCurrent').style.display = 'none';
            if (journal.cover_image) {
                document.getElementById('coverPreviewCurrent').src = `/storage/${journal.cover_image}`;
                document.getElementById('coverPreviewCurrent').style.display = 'block';
            }

            document.getElementById('fieldsCoveredContainer').innerHTML = '';
            const fc = journal.fields_covered || [];
            fc.length ? fc.forEach(f => addFieldRow(f)) : addFieldRow();

            addJournalModal.show();
        }

        // ── Save ───────────────────────────────────────────────────────
        function saveJournal() {
            const id = document.getElementById('journal_id').value;

            const formData = new FormData();
            formData.append('title', document.getElementById('title').value.trim());
            formData.append('heading_1', document.getElementById('heading_1').value.trim());
            formData.append('description', descEditor ? descEditor.getData() : '');
            formData.append('abbreviation', document.getElementById('abbreviation').value.trim());
            formData.append('badge', document.getElementById('badge').value.trim());
            formData.append('e_issn', document.getElementById('e_issn').value.trim());
            formData.append('p_issn', document.getElementById('p_issn').value.trim());
            formData.append('issn_online', document.getElementById('issn_online').value.trim());
            formData.append('volume', document.getElementById('volume').value.trim());
            formData.append('issue', document.getElementById('issue').value.trim());
            formData.append('latest_volume', document.getElementById('latest_volume').value.trim());
            formData.append('publication_language', document.getElementById('publication_language').value.trim());
            formData.append('publishing_frequency', document.getElementById('publishing_frequency').value.trim());
            formData.append('publishing_months', document.getElementById('publishing_months').value.trim());
            formData.append('indexing_impact_factor', document.getElementById('indexing_impact_factor').value.trim());
            formData.append('time_to_first_decision', document.getElementById('time_to_first_decision').value.trim());
            formData.append('time_to_review', document.getElementById('time_to_review').value.trim());
            formData.append('acceptance_to_publication', document.getElementById('acceptance_to_publication').value.trim());
            formData.append('article_template_url', document.getElementById('article_template_url').value.trim());
            formData.append('aim_and_scope_title', document.getElementById('aim_and_scope_title').value.trim());
            formData.append('aim_and_scope', aimScopeEditor ? aimScopeEditor.getData() : '');
            formData.append('view_all_issues_label', document.getElementById('view_all_issues_label').value.trim());
            formData.append('view_all_issues_link', document.getElementById('view_all_issues_link').value.trim());
            formData.append('explore_journals_label', document.getElementById('explore_journals_label').value.trim());
            formData.append('explore_journals_link', document.getElementById('explore_journals_link').value.trim());
            formData.append('sequence', document.getElementById('sequence').value || 0);
            formData.append('is_active', document.getElementById('is_active').value);

            collectFieldsCovered().forEach(f => formData.append('fields_covered[]', f));

            const fileInput = document.getElementById('cover_image');
            if (fileInput.files.length) formData.append('cover_image', fileInput.files[0]);

            let url = API;
            if (isEditMode) {
                url = `${API}/${id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.getElementById('saveJournalBtn');
            const btnText = document.getElementById('saveJournalBtnText');
            btn.disabled = true;
            btnText.textContent = isEditMode ? 'Updating…' : 'Saving…';

            fetch(url, {
                    method: 'POST',
                    credentials: 'include',
                    headers: formHeaders(),
                    body: formData
                })
                .then(handleAuthErrors)
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        if (res.status === 422 && data.errors) {
                            showToastLegacy(Object.values(data.errors).map(e => e[0]).join(' | '), 'danger');
                            throw new Error('Validation failed');
                        }
                        throw new Error(data.message || 'Save failed');
                    }
                    return data;
                })
                .then(data => {
                    loadJournals();
                    addJournalModal.hide();
                    showToastLegacy(data.message || (isEditMode ? 'Journal updated.' : 'Journal created.'), 'success');
                })
                .catch(err => {
                    if (err.message !== 'Validation failed' && err.message !== 'Not authenticated') {
                        showToastLegacy(err.message, 'danger');
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btnText.textContent = isEditMode ? 'Update Journal' : 'Create Journal';
                });
        }

        // ── Toggle status ────────────────────────────────────────────
        function toggleStatus(id) {
            fetch(`${API}/${id}/toggle`, {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: formHeaders()
                })
                .then(handleAuthErrors)
                .then(res => res.json())
                .then(data => {
                    loadJournals();
                    showToastLegacy(data.message || 'Status updated.', 'success');
                })
                .catch(err => {
                    if (err.message !== 'Not authenticated') showToastLegacy(err.message || 'Failed.', 'danger');
                });
        }

        // ── Delete ────────────────────────────────────────────────────
        function promptDelete(id, title) {
            pendingDeleteId = id;
            document.getElementById('deleteJournalName').textContent = title;
            deletePopupModal.show();
        }

        function executeDelete(id) {
            fetch(`${API}/${id}`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: formHeaders()
                })
                .then(handleAuthErrors)
                .then(res => res.json())
                .then(data => {
                    loadJournals();
                    showToastLegacy(data.message || 'The journal has been deleted.', 'success');
                })
                .catch(err => {
                    if (err.message !== 'Not authenticated') showToastLegacy(err.message || 'Delete failed.', 'danger');
                });
        }

        // ── Auth errors ───────────────────────────────────────────────
        function handleAuthErrors(res) {
            if (res.status === 401) {
                showToastLegacy('Session expired. Redirecting...', 'warning');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1200);
                throw new Error('Not authenticated');
            }
            if (res.status === 419) {
                showToastLegacy('CSRF expired. Reloading...', 'warning');
                setTimeout(() => window.location.reload(), 1500);
                throw new Error('CSRF token expired');
            }
            return res;
        }

        // ── Helpers ───────────────────────────────────────────────────
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str == null ? '' : str;
            return div.innerHTML;
        }

        function escAttr(s) {
            return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
    </script>
@endsection
