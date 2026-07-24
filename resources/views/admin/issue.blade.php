@extends('layouts.admin')

@section('content')
    {{-- Issue List --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Issue List
                </div>

                <div class="table-controls">
                    <button type="button" class="add-btn" onclick="openCreateModal()">+ Add Issue</button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Journal</th>
                                <th>Volume</th>
                                <th>Issue</th>
                                <th>Year</th>
                                <th>Published Date</th>
                                <th>Status</th>
                                <th>Current</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="issue-table-body">
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-footet-two ">
                    <div id="paginationInfo"></div>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-end" id="pagination"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

    <!-- Create / Edit Modal -->
    <div class="modal fade" id="issueModal" tabindex="-1" aria-labelledby="issueModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="issueForm">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="issue_id" name="id">

                        <div class="top">
                            <div class="pop-title" id="issueModalTitle">Add Volume</div>
                        </div>

                        <div class="middle-3 middle">

                            <!-- Select journal-->
                            <span class="input-set">
                                <label>Journal <span class="text-danger">*</span></label>
                                <select class="form-select" name="journal_id" id="journal_id" required
                                    onchange="loadVolumeOptions(this.value)">
                                    <option value="">Select journal...</option>
                                </select>
                            </span>

                            <!-- Select volume -->
                            <span class="input-set">
                                <label>Volume <span class="text-danger">*</span></label>
                                <select class="form-select" name="volume_id" id="volume_id" required>
                                    <option value="">Select volume...</option>
                                </select>
                            </span>

                            <!-- Issue -->
                            <span class="input-set">
                                <label>Issue <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" name="issue" id="issue"
                                    placeholder="e.g. Issue 3" required>
                            </span>

                            <!-- Year -->
                            <span class="input-set">
                                <label>Year <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" name="year" id="year"
                                    placeholder="e.g. 2025">
                            </span>

                            <!-- Published Date -->
                            <span class="input-set">
                                <label>Published Date <span class="text-danger">*</span></label>
                                <input type="date" class="content_show" name="published_date" id="published_date">
                            </span>

                            <!-- Status -->
                            <span class="input-set">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published/Archived</option>
                                </select>
                            </span>

                        </div>

                        <!-- Current -->
                        <div class=" mt-5">
                            <input class="form-check-input" type="checkbox" name="is_current" id="is_current">
                            <label class="form-check-label" for="is_current">Set as current issue for this volume</label>
                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">
                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>
                            <button type="submit" class="blue">Save</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title">Issue Details</div>
                    </div>

                    <div class="middle-3 middle"></div>

                    <!-- Data Load -->
                    <div class="reason" id="viewModalBody">Loading...</div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = "/api/admin/issues";
        const JOURNALS_API = "/api/admin/journals?page=1&per_page=100";
        const VOLUMES_API = "/api/admin/volumes?page=1&per_page=200";
        const token = localStorage.getItem('token');
        let currentPage = 1;
        let allVolumes = [];

        function authHeaders() {
            return {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            };
        }

        async function loadJournalOptions(selectedId = null) {
            const res = await fetch(JOURNALS_API, {
                headers: authHeaders()
            });
            const json = await res.json();
            const select = document.getElementById('journal_id');
            select.innerHTML = '<option value="">Select journal...</option>';

            (json.data?.data ?? []).forEach(j => {
                const selected = selectedId == j.id ? 'selected' : '';
                select.innerHTML += `<option value="${j.id}" ${selected}>${j.title}</option>`;
            });
        }

        async function loadAllVolumes() {
            const res = await fetch(VOLUMES_API, {
                headers: authHeaders()
            });
            const json = await res.json();
            allVolumes = json.data?.data ?? [];
        }

        function loadVolumeOptions(journalId, selectedVolumeId = null) {
            const select = document.getElementById('volume_id');
            select.innerHTML = '<option value="">Select volume...</option>';

            const seen = new Set();

            allVolumes
                .filter(v => v.journal_id == journalId)
                .filter(v => {
                    if (seen.has(v.volume)) return false;
                    seen.add(v.volume);
                    return true;
                })
                .forEach(v => {
                    const selected = selectedVolumeId == v.id ? 'selected' : '';
                    select.innerHTML += `<option value="${v.id}" ${selected}>${v.volume}</option>`;
                });
        }

        async function loadIssues(page = 1) {
            currentPage = page;
            const res = await fetch(`${API_BASE}?page=${page}`, {
                headers: authHeaders()
            });
            const json = await res.json();

            const tbody = document.getElementById('issue-table-body');
            tbody.innerHTML = '';

            if (!json.status || !json.data.data.length) {
                tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4">No issues found.</td></tr>`;
                return;
            }

            json.data.data.forEach(i => {
                tbody.innerHTML += `
            <tr>
                <td>${i.id}</td>
                <td>${i.journal?.title ?? '-'}</td>
                <td>${i.volume?.volume ?? '-'}</td>
                <td>${i.issue}</td>
                <td>${i.year ?? '-'}</td>
                <td>${i.published_date ?? '-'}</td>
                <td><span class="green-btn">${i.status}</span></td>
                <td>
                    ${i.is_current
                        ? '<span class="green-btn">Current</span>'
                        : `<button class="edit-btn" onclick="toggleCurrent(${i.id})">Archived</button>`}
                </td>
                <td>
                    <button class="edit-btn" onclick="viewIssue(${i.id})">View</button>
                    <button class="edit-btn" onclick="editIssue(${i.id})">Edit</button>
                    <button class="delete-btn" onclick="deleteIssue(${i.id}, '${i.issue}')">Delete</button>
                </td>
            </tr>`;
            });

            renderPagination(json.data);
        }

        function renderPagination(pageData) {
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            if (!pageData.last_page || pageData.last_page <= 1) return;

            for (let i = 1; i <= pageData.last_page; i++) {
                pagination.innerHTML += `
            <li class="page-item ${i === pageData.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadIssues(${i}); return false;">${i}</a>
            </li>`;
            }
        }

        async function openCreateModal() {
            document.getElementById('issueForm').reset();
            document.getElementById('issue_id').value = '';
            document.getElementById('issueModalTitle').innerText = 'Add Issue';
            await loadJournalOptions();
            await loadAllVolumes();
            document.getElementById('volume_id').innerHTML = '<option value="">Select volume...</option>';
            new bootstrap.Modal(document.getElementById('issueModal')).show();
        }

        async function editIssue(id) {
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders()
            });
            const json = await res.json();
            if (!json.status) return alert('Failed to load issue.');

            const i = json.data;
            await loadJournalOptions(i.journal_id);
            await loadAllVolumes();
            loadVolumeOptions(i.journal_id, i.volume_id);

            document.getElementById('issue_id').value = i.id;
            document.getElementById('issue').value = i.issue;
            document.getElementById('year').value = i.year ?? '';
            document.getElementById('published_date').value = i.published_date ?? '';
            document.getElementById('status').value = i.status;
            document.getElementById('is_current').checked = !!i.is_current;
            document.getElementById('issueModalTitle').innerText = 'Edit Issue';

            new bootstrap.Modal(document.getElementById('issueModal')).show();
        }

        async function viewIssue(id) {
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders()
            });
            const json = await res.json();
            if (!json.status) return alert('Failed to load issue.');

            const i = json.data;
            document.getElementById('viewModalBody').innerHTML = `
        <p><strong>Journal:</strong> ${i.journal?.title ?? '-'}</p>
        <p><strong>Volume:</strong> ${i.volume?.volume ?? '-'}</p>
        <p><strong>Issue:</strong> ${i.issue}</p>
        <p><strong>Year:</strong> ${i.year ?? '-'}</p>
        <p><strong>Published Date:</strong> ${i.published_date ?? '-'}</p>
        <p><strong>Status:</strong> ${i.status}</p>
        <p><strong>Current:</strong> ${i.is_current ? 'Yes' : 'No'}</p>
    `;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        async function toggleCurrent(id) {
            const res = await fetch(`${API_BASE}/${id}/toggle-current`, {
                method: 'PATCH',
                headers: authHeaders(),
            });
            const json = await res.json();
            if (json.status) loadIssues(currentPage);
            else alert(json.message ?? 'Failed to update current issue.');
        }

        async function deleteIssue(id, name) {
            if (!confirm(`Delete issue "${name}"? This cannot be undone.`)) return;

            const res = await fetch(`${API_BASE}/${id}`, {
                method: 'DELETE',
                headers: authHeaders(),
            });
            const json = await res.json();
            if (json.status) loadIssues(currentPage);
            else alert(json.message ?? 'Failed to delete issue.');
        }

        document.getElementById('issueForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const id = document.getElementById('issue_id').value;

            const payload = {
                journal_id: document.getElementById('journal_id').value,
                volume_id: document.getElementById('volume_id').value,
                issue: document.getElementById('issue').value,
                year: document.getElementById('year').value,
                published_date: document.getElementById('published_date').value,
                status: document.getElementById('status').value,
                is_current: document.getElementById('is_current').checked,
            };

            const url = id ? `${API_BASE}/${id}` : API_BASE;
            const method = id ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: authHeaders(),
                body: JSON.stringify(payload),
            });

            const json = await res.json();

            if (json.status) {
                bootstrap.Modal.getInstance(document.getElementById('issueModal')).hide();
                loadIssues(currentPage);
            } else {
                alert(json.message ?? 'Something went wrong.');
                console.error(json.errors);
            }
        });

        loadIssues();
    </script>
@endsection
