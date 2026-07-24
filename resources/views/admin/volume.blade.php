@extends('layouts.admin')

@section('content')
    {{-- Volumes List --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Volumes List
                </div>

                <div class="table-controls">
                    <button type="button" class="add-btn" onclick="openCreateModal()">+ Add Volume</button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Journal</th>
                                <th>Volume</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Current</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="volume-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Loading…</td>
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

    <!-- Toast Container -->
    <div id="toastContainer">
    </div>


    <!-- Create / Edit Modal -->
    <div class="modal fade" id="volumeModal" tabindex="-1" aria-labelledby="volumeModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="volumeForm">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="volume_id" name="id">

                        <div class="top">
                            <div class="pop-title" id="volumeModalTitle">Add Volume</div>
                        </div>

                        <div class="middle-3 middle">

                            <!-- Journal-->
                            <span class="input-set">
                                <label>Journal <span class="text-danger">*</span></label>
                                <select class="form-select" name="journal_id" id="journal_id" required>
                                    <option value="">Select journal...</option>
                                </select>
                            </span>

                            <!-- Volume -->
                            <span class="input-set">
                                <label>Volume <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" name="volume" id="volume"
                                    placeholder="e.g. Volume 12" required>
                            </span>

                            <!-- Year -->
                            <span class="input-set">
                                <label>Year <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" name="year" id="year"
                                    placeholder="e.g. 2025">
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

                        <!-- Role -->
                        <div class=" mt-5">
                            <input class="form-check-input" type="checkbox" name="is_current" id="is_current">
                            <label class="form-check-label" for="is_current">Set as current volume for this journal</label>
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
                        <div class="pop-title">Volume Details</div>
                    </div>

                    <div class="middle-3 middle"></div>

                    <!-- Data Load -->
                    <div class="reason" id="viewModalBody">Loading...</div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = "/api/admin/volumes";
        const JOURNALS_API = "/api/admin/journals?page=1&per_page=100";
        const token = localStorage.getItem('token');
        let currentPage = 1;

        function authHeaders() {
            return {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            };
        }

        // ─── Toast Helper ───────────────────────────────────────────────
        function showToast(message, type = 'success', title = null) {
            const container = document.getElementById('toastContainer');

            const toast = document.createElement('div');
            toast.className = `custom-toast ${type}`;

            const defaultTitle = type === 'success' ? 'Success' : 'Error';

            const icon = type === 'success' ?
                `<div class="toast-status success-icon">✓</div>` :
                `<div class="toast-status error-icon">!</div>`;

            toast.innerHTML = `
        <div class="toast-header">
            <div class="toast-top">
                ${icon}
                <div class="toast-title">${title ?? defaultTitle}</div>
            </div>

            <button class="toast-close" onclick="this.closest('.custom-toast').remove()">
                &times;
            </button>
        </div>

        <div class="toast-body">
            ${message}
        </div>

        <div class="toast-bottom">
            <button class="toast-btn blue" onclick="this.closest('.custom-toast').remove()">
                OK
            </button>
        </div>

        <div class="toast-progress"></div>
    `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'toastSlideOut 0.3s ease forwards';

                setTimeout(() => {
                    toast.remove();
                }, 300);

            }, 3500);
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

        async function loadVolumes(page = 1) {
            currentPage = page;
            const res = await fetch(`${API_BASE}?page=${page}`, {
                headers: authHeaders()
            });
            const json = await res.json();

            const tbody = document.getElementById('volume-table-body');
            tbody.innerHTML = '';

            if (!json.status || !json.data.data.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4">No volumes found.</td></tr>`;
                return;
            }

            json.data.data.forEach(v => {
                tbody.innerHTML += `
            <tr>
                <td>${v.id}</td>
                <td>${v.journal?.title ?? '-'}</td>
                <td>${v.volume}</td>
                <td>${v.year ?? '-'}</td>
                <td><span class="green-btn">${v.status}</span></td>
                <td>
                    ${v.is_current
                        ? '<span class="green-btn">Current</span>'
                        : `<button class="edit-btn" onclick="toggleCurrent(${v.id})">Archieved</button>`}
                </td>
                <td>
                    <button class="edit-btn" onclick="viewVolume(${v.id})">View</button>
                    <button class="edit-btn" onclick="editVolume(${v.id})">Edit</button>
                    <button class="delete-btn" onclick="deleteVolume(${v.id}, '${v.volume}')">Delete</button>
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
                <a class="page-link" href="#" onclick="loadVolumes(${i}); return false;">${i}</a>
            </li>`;
            }
        }

        async function openCreateModal() {
            document.getElementById('volumeForm').reset();
            document.getElementById('volume_id').value = '';
            document.getElementById('volumeModalTitle').innerText = 'Add Volume';
            await loadJournalOptions();
            new bootstrap.Modal(document.getElementById('volumeModal')).show();
        }

        async function editVolume(id) {
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders()
            });
            const json = await res.json();
            if (!json.status) return showToast(json.message ?? 'Failed to load volume.', 'error');

            const v = json.data;
            await loadJournalOptions(v.journal_id);

            document.getElementById('volume_id').value = v.id;
            document.getElementById('volume').value = v.volume;
            document.getElementById('year').value = v.year ?? '';
            document.getElementById('status').value = v.status;
            document.getElementById('is_current').checked = !!v.is_current;
            document.getElementById('volumeModalTitle').innerText = 'Edit Volume';

            new bootstrap.Modal(document.getElementById('volumeModal')).show();
        }

        async function viewVolume(id) {
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders()
            });
            const json = await res.json();
            if (!json.status) return showToast(json.message ?? 'Failed to load volume.', 'error');

            const v = json.data;

            document.getElementById('viewModalBody').innerHTML = `
        <p><strong>Journal:</strong> ${v.journal?.title ?? '-'}</p>
        <p><strong>Volume:</strong> ${v.volume}</p>
        <p><strong>Year:</strong> ${v.year ?? '-'}</p>
        <p><strong>Status:</strong> ${v.status}</p>
        <p><strong>Current:</strong> ${v.is_current ? 'Yes' : 'No'}</p>
    `;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        async function toggleCurrent(id) {
            const res = await fetch(`${API_BASE}/${id}/toggle-current`, {
                method: 'PATCH',
                headers: authHeaders(),
            });
            const json = await res.json();
            if (json.status) {
                showToast(json.message ?? 'Marked as current successfully.', 'success');
                loadVolumes(currentPage);
            } else {
                showToast(json.message ?? 'Failed to update current volume.', 'error');
            }
        }

        async function deleteVolume(id, name) {
            if (!confirm(`Delete volume "${name}"? This cannot be undone.`)) return;

            const res = await fetch(`${API_BASE}/${id}`, {
                method: 'DELETE',
                headers: authHeaders(),
            });
            const json = await res.json();
            if (json.status) {
                showToast(json.message ?? 'Volume deleted successfully.', 'success');
                loadVolumes(currentPage);
            } else {
                showToast(json.message ?? 'Failed to delete volume.', 'error');
            }
        }

        document.getElementById('volumeForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const id = document.getElementById('volume_id').value;

            const payload = {
                journal_id: document.getElementById('journal_id').value,
                volume: document.getElementById('volume').value,
                year: document.getElementById('year').value,
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
                bootstrap.Modal.getInstance(document.getElementById('volumeModal')).hide();
                showToast(json.message ?? 'Volume saved successfully.', 'success');
                loadVolumes(currentPage);
            } else {
                showToast(json.message ?? 'Something went wrong.', 'error');
                console.error(json.errors);
            }
        });

        loadVolumes();
    </script>
@endsection
