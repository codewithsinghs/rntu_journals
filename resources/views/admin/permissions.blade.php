@extends('layouts.admin')

@section('content')

    <!-- List -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Permissions Management
                </div>

                <div class="table-controls">
                    <button type="button" class="add-btn" id="openCreateModalBtn" data-bs-toggle="modal"
                        data-bs-target="#createPermissionModal">Create Permission</button> &nbsp; &nbsp;
                    <input type="text" id="searchInput" class="form-control form-control-sm" style="width: 220px;"
                        placeholder="Search permissions..."> &nbsp; &nbsp;
                    <button class="btn btn-outline-secondary btn-sm" id="clearSearch">Clear</button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Permission Name</th>
                                <th>Guard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="permissionsTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-style-group">
                    <span>Show</span>
                    <select id="perPageSelect" class="form-select form-select-sm" style="width: 100px;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>Entries</span>
                </div>

                <div class="pagination-footet-two ">
                    <div id="paginationInfo"></div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

    <!-- CREATE MODAL -->
    <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-labelledby="createPermissionModallabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title">Create New Permission</div>
                    </div>

                    <div class="middle-3 middle"></div>

                    <!-- Create Permission -->
                    <div class=" mb-0">
                        <label>Permission Name <span class="text-danger">*</span></label>
                        <input type="text" id="createPermissionName" class="form-control form-control-sm"
                            placeholder="e.g. view users, edit posts">
                        <div class="invalid-feedback" id="createPermissionNameError"></div>
                    </div>

                    <!-- Btn -->
                    <div class="bottom-btn">
                        <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel </button>
                        <button type="button" class="blue" id="createPermissionBtn">
                            <span id="createBtnSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            <span id="createBtnText">Create Permission</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModallabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title">Edit Permission</div>
                    </div>

                    <div class="middle-3 middle"></div>

                    <!-- Edit Permission -->
                    <input type="hidden" id="editPermissionId">
                    <div class=" mb-0">
                        <label>Permission Name <span class="text-danger">*</span></label>
                        <input type="text" id="editPermissionName" class="form-control form-control-sm">
                        <div class="invalid-feedback" id="editPermissionNameError"></div>
                    </div>

                    <!-- Btn -->
                    <div class="bottom-btn">
                        <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                        </button>
                        <button type="button" class="blue" id="editPermissionBtn">
                            <span id="editBtnSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            <span id="editBtnText">Save Changes</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="deletePermissionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-4 px-4">
                    <div class="delete-icon-wrap mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.8rem;"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">Are you sure?</h6>
                    <p class="text-muted mb-3" style="font-size:0.9rem;">
                        You are about to delete permission <strong class="text-dark" id="deletePermissionName"></strong>.
                        <br>This action <strong>cannot</strong> be undone.
                    </p>
                    <input type="hidden" id="deletePermissionId">
                    <div id="deleteModalError" class="alert alert-danger d-none py-2 text-start"
                        style="font-size:0.85rem;">
                        <i class="bi bi-x-circle me-1"></i><span id="deleteModalErrorText"></span>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="deletePermissionBtn">
                        <span id="deleteBtnText"><i class="bi bi-trash3 me-1"></i>Yes, Delete</span>
                        <span id="deleteBtnSpinner" class="spinner-border spinner-border-sm d-none ms-1"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
        <div id="appToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex align-items-center gap-3 px-3 py-3">
                <div id="toastIconWrap" class="toast-icon-wrap flex-shrink-0">
                    <i id="toastIcon" class="bi fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <div id="toastTitle" class="fw-semibold" style="font-size:0.9rem;"></div>
                    <div id="toastMessage" class="opacity-75" style="font-size:0.8rem;"></div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2"
                    data-bs-dismiss="toast"></button>
            </div>
            <div id="toastProgressBar" class="toast-progress-bar"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const API_BASE = '/api/admin/permissions';

        let currentPage = 1;
        let currentSearch = '';
        let currentPerPage = 10;
        let searchTimer = null;

        // ── Authenticated fetch ───────────────────────────────────────────
        async function authFetch(url, options = {}) {
            options.credentials = 'same-origin';
            options.headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...options.headers,
            };
            return fetch(url, options);
        }

        // ── Toast Notification ────────────────────────────────────────────
        function showToast(message, type = 'success') {
            const toast = document.getElementById('appToast');
            const icon = document.getElementById('toastIcon');
            const titleEl = document.getElementById('toastTitle');
            const messageEl = document.getElementById('toastMessage');

            toast.classList.remove('toast-success', 'toast-danger', 'toast-warning');
            toast.classList.add(`toast-${type}`);

            const config = {
                success: {
                    icon: 'bi-check-circle-fill',
                    title: 'Success'
                },
                danger: {
                    icon: 'bi-x-circle-fill',
                    title: 'Error'
                },
                warning: {
                    icon: 'bi-exclamation-circle-fill',
                    title: 'Warning'
                },
            };
            const cfg = config[type] || config.success;

            icon.className = `bi ${cfg.icon} fs-5`;
            titleEl.textContent = cfg.title;
            messageEl.textContent = message;

            const bar = document.getElementById('toastProgressBar');
            bar.style.display = 'none';
            void bar.offsetWidth;
            bar.style.display = '';

            new bootstrap.Toast(toast, {
                delay: 4000
            }).show();
        }

        // ── Field error helpers ───────────────────────────────────────────
        function setFieldError(id, message) {
            const input = document.getElementById(id);
            const error = document.getElementById(id + 'Error');
            if (input) input.classList.add('is-invalid');
            if (error) error.textContent = message;
        }

        function clearAllErrors(...ids) {
            ids.forEach(id => {
                const input = document.getElementById(id);
                const error = document.getElementById(id + 'Error');
                if (input) input.classList.remove('is-invalid');
                if (error) error.textContent = '';
            });
        }

        // ── Load Permissions ──────────────────────────────────────────────
        async function loadPermissions(page = 1) {
            currentPage = page;

            const params = new URLSearchParams({
                page: currentPage,
                per_page: currentPerPage,
            });
            if (currentSearch.trim()) params.append('search', currentSearch.trim());

            const tbody = document.getElementById('permissionsTableBody');
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Loading…</td></tr>`;

            try {
                const res = await authFetch(`${API_BASE}?${params}`);
                const json = await res.json();

                if (!json.status || !json.data.length) {
                    tbody.innerHTML =
                        `<tr><td colspan="4" class="text-center text-muted py-4">No permissions found.</td></tr>`;
                    document.getElementById('paginationInfo').textContent = 'Showing 0 to 0 of 0 entries';
                    document.getElementById('paginationLinks').innerHTML = '';
                    return;
                }

                const {
                    current_page,
                    last_page,
                    per_page,
                    total
                } = json.meta;
                const from = (current_page - 1) * per_page + 1;
                const to = Math.min(current_page * per_page, total);

                tbody.innerHTML = json.data.map((p, index) => `
                <tr>
                    <td>${(currentPage - 1) * currentPerPage + index + 1}</td>
                    <td><span class="green-btn">${p.name}</span></td>
                    <td><span class="adm-pill-muted">${p.guard_name}</span></td>
                    <td>
                        <div class="d-flex">
                            <button class="edit-btn" onclick="openEditModal(${p.id}, '${p.name.replace(/'/g, "\\'")}')">Edit</button>
                            <button class="delete-btn" onclick="openDeleteModal(${p.id}, '${p.name.replace(/'/g, "\\'")}')">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');

                document.getElementById('paginationInfo').textContent =
                    `Showing ${from}–${to} of ${total} permissions`;

                renderPagination(current_page, last_page);

            } catch (err) {
                console.error(err);
                tbody.innerHTML =
                    `<tr><td colspan="4" class="text-center text-danger py-4">Failed to load permissions.</td></tr>`;
                showToast('Failed to load permissions.', 'danger');
            }
        }

        // ── Pagination ────────────────────────────────────────────────────
        function renderPagination(current, last) {
            const ul = document.getElementById('paginationLinks');
            ul.innerHTML = '';
            if (last <= 1) return;

            const makeItem = (label, page, disabled = false, active = false) => {
                const li = document.createElement('li');
                li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.innerHTML = label;
                if (!disabled && !active) {
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        loadPermissions(page);
                    });
                }
                li.appendChild(a);
                ul.appendChild(li);
            };

            makeItem('&laquo;', current - 1, current === 1);
            const pages = new Set();
            [1, last, current - 1, current, current + 1].forEach(p => {
                if (p >= 1 && p <= last) pages.add(p);
            });
            let prev = null;
            [...pages].sort((a, b) => a - b).forEach(p => {
                if (prev && p - prev > 1) makeItem('…', null, true);
                makeItem(p, p, false, p === current);
                prev = p;
            });
            makeItem('&raquo;', current + 1, current === last);
        }

        // ── Search ────────────────────────────────────────────────────────
        document.getElementById('searchInput').addEventListener('input', e => {
            clearTimeout(searchTimer);
            currentSearch = e.target.value;
            searchTimer = setTimeout(() => loadPermissions(1), 400);
        });
        document.getElementById('clearSearch').addEventListener('click', () => {
            document.getElementById('searchInput').value = '';
            currentSearch = '';
            loadPermissions(1);
        });

        // ── Per Page ──────────────────────────────────────────────────────
        document.getElementById('perPageSelect').addEventListener('change', e => {
            currentPerPage = parseInt(e.target.value);
            loadPermissions(1);
        });

        // ── Create ────────────────────────────────────────────────────────
        document.getElementById('openCreateModalBtn').addEventListener('click', () => {
            document.getElementById('createPermissionName').value = '';
            clearAllErrors('createPermissionName');
        });

        document.getElementById('createPermissionBtn').addEventListener('click', async () => {
            clearAllErrors('createPermissionName');

            const btn = document.getElementById('createPermissionBtn');
            const btnText = document.getElementById('createBtnText');
            const spinner = document.getElementById('createBtnSpinner');

            btn.disabled = true;
            btnText.textContent = 'Creating...';
            spinner.classList.remove('d-none');

            try {
                const res = await authFetch(API_BASE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: document.getElementById('createPermissionName').value
                    }),
                });
                const json = await res.json();

                if (!json.status) {
                    if (json.errors?.name) setFieldError('createPermissionName', json.errors.name[0]);
                    else showToast(json.message, 'danger');
                    return;
                }

                bootstrap.Modal.getInstance(document.getElementById('createPermissionModal')).hide();
                showToast(json.message, 'success');
                loadPermissions(1);

            } catch (err) {
                console.error(err);
                showToast('Something went wrong.', 'danger');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Create Permission';
                spinner.classList.add('d-none');
            }
        });

        // ── Edit Modal ────────────────────────────────────────────────────
        function openEditModal(id, name) {
            document.getElementById('editPermissionId').value = id;
            document.getElementById('editPermissionName').value = name;
            clearAllErrors('editPermissionName');
            new bootstrap.Modal(document.getElementById('editPermissionModal')).show();
        }

        document.getElementById('editPermissionBtn').addEventListener('click', async () => {
            const id = document.getElementById('editPermissionId').value;
            clearAllErrors('editPermissionName');

            const btn = document.getElementById('editPermissionBtn');
            const btnText = document.getElementById('editBtnText');
            const spinner = document.getElementById('editBtnSpinner');

            btn.disabled = true;
            btnText.textContent = 'Saving...';
            spinner.classList.remove('d-none');

            try {
                const res = await authFetch(`${API_BASE}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: document.getElementById('editPermissionName').value
                    }),
                });
                const json = await res.json();

                if (!json.status) {
                    if (json.errors?.name) setFieldError('editPermissionName', json.errors.name[0]);
                    else showToast(json.message, 'danger');
                    return;
                }

                bootstrap.Modal.getInstance(document.getElementById('editPermissionModal')).hide();
                showToast(json.message, 'success');
                loadPermissions(currentPage);

            } catch (err) {
                console.error(err);
                showToast('Something went wrong.', 'danger');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Save Changes';
                spinner.classList.add('d-none');
            }
        });

        // ── Delete Modal ──────────────────────────────────────────────────
        function openDeleteModal(id, name) {
            document.getElementById('deletePermissionId').value = id;
            document.getElementById('deletePermissionName').textContent = name;

            const errBox = document.getElementById('deleteModalError');
            errBox.classList.add('d-none');
            document.getElementById('deleteModalErrorText').textContent = '';

            new bootstrap.Modal(document.getElementById('deletePermissionModal')).show();
        }

        document.getElementById('deletePermissionBtn').addEventListener('click', async () => {
            const id = document.getElementById('deletePermissionId').value;
            const errBox = document.getElementById('deleteModalError');
            const errText = document.getElementById('deleteModalErrorText');
            const btn = document.getElementById('deletePermissionBtn');
            const btnText = document.getElementById('deleteBtnText');
            const spinner = document.getElementById('deleteBtnSpinner');

            errBox.classList.add('d-none');
            btn.disabled = true;
            btnText.innerHTML = 'Deleting...';
            spinner.classList.remove('d-none');

            try {
                const res = await authFetch(`${API_BASE}/${id}`, {
                    method: 'DELETE'
                });
                const json = await res.json();

                if (!json.status) {
                    errText.textContent = json.message;
                    errBox.classList.remove('d-none');
                    return;
                }

                bootstrap.Modal.getInstance(document.getElementById('deletePermissionModal')).hide();
                showToast(json.message, 'success');
                loadPermissions(currentPage > 1 ? currentPage - 1 : 1);

            } catch (err) {
                console.error(err);
                errText.textContent = 'Something went wrong. Please try again.';
                errBox.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btnText.innerHTML = '<i class="bi bi-trash3 me-1"></i>Yes, Delete';
                spinner.classList.add('d-none');
            }
        });

        // ── Init ──────────────────────────────────────────────────────────
        loadPermissions();
    </script>
@endsection
