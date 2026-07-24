@extends('layouts.admin')

@section('content')
    <!-- Memder List -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Editorial Board Members</div>

                <div class="table-controls" style="display:flex; align-items:center;">
                    <button class="add-btn d-none" id="ebEmpty" onclick="document.getElementById('openEbModal').click()">No
                        editorial board members found.</button>

                    <!-- Right-aligned group: Add button + Journal filter -->
                    <div style="display:flex; align-items:center;" >
                        <button class="add-btn" id="openEbModal" style="width: 100%;">Add Member</button> &nbsp; &nbsp;
                        <select id="journalFilter" class="form-select form-select-sm">
                            <option value="">All Journals</option>
                        </select>
                    </div>
                </div>

                <div class="table-container" style="margin: 0;" id="ebTableWrap">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Journal</th>
                                <th>Role</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Seq</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ebTbody">
                            <tr>
                                <div id="ebLoading" class="text-center py-5">
                                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status">
                                    </div>
                                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                                </div>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="pagination-container" class="an-pagination"></div>

            </div>
        </div>
    </section>

    <!-- Add / Edit Modal -->
    <div class="modal fade" id="ebModal" tabindex="-1" aria-labelledby="ebModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="ebForm" novalidate>
                    @csrf
                    <input type="hidden" id="ebId">
                    <input type="hidden" id="ebMethod" value="POST">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title" id="ebModalTitle">Add Member</div>
                        </div>

                        <div class="middle-3 middle">

                            <!-- Journal -->
                            <span class="input-set">
                                <label>Journal</label>
                                <select class="form-select" id="journal_id" name="journal_id">
                                    <option value="">— None —</option>
                                </select>
                                <div class="invalid-feedback" id="err_journal_id"></div>
                            </span>

                            <!-- Role -->
                            <span class="input-set">
                                <label>Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select role</option>
                                    <option value="Editor-in-Chief">Editor-in-Chief</option>
                                    <option value="Managing Editor">Managing Editor</option>
                                    <option value="Executive Editor">Executive Editor</option>
                                    <option value="Editors">Editors</option>
                                    <option value="Associate Editors">Associate Editors</option>
                                    <option value="Members">Members</option>
                                </select>
                                <div class="invalid-feedback" id="err_role"></div>
                            </span>

                            <!-- Name -->
                            <span class="input-set">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Name" required>
                                <div class="invalid-feedback" id="err_name"></div>
                            </span>

                            <!-- Designation -->
                            <span class="input-set">
                                <label>Designation</label>
                                <input type="text" id="designation" name="designation" placeholder="Name">
                                <div class="invalid-feedback" id="err_department"></div>
                            </span>

                            <!-- Department -->
                            <span class="input-set">
                                <label>Department</label>
                                <input type="text" id="department" name="department" placeholder="Name">
                            </span>

                            <!-- Institute -->
                            <span class="input-set">
                                <label>Institute</label>
                                <input type="text" id="institute" name="institute">
                                <div class="invalid-feedback" id="err_institute"></div>
                            </span>

                            <!-- University / Organization -->
                            <span class="input-set">
                                <label>University / Organization</label>
                                <input type="text" id="university_or_org" name="university_or_org">
                                <div class="invalid-feedback" id="err_university_or_org"></div>
                            </span>

                            <!-- City -->
                            <span class="input-set">
                                <label>City</label>
                                <input type="text" id="city" name="city" placeholder="Name">
                                <div class="invalid-feedback" id="err_city"></div>
                            </span>

                            <!-- Email -->
                            <span class="input-set">
                                <label>Email</label>
                                <input type="email" id="email" name="email">
                                <div class="invalid-feedback" id="err_email"></div>
                            </span>

                            <!-- ORCID URL -->
                            <span class="input-set">
                                <label>ORCID URL</label>
                                <input type="url" id="orcid_url" name="orcid_url">
                                <div class="invalid-feedback" id="err_orcid_url"></div>
                            </span>

                            <!-- Scopus URL -->
                            <span class="input-set">
                                <label>Scopus URL</label>
                                <input type="url" id="scopus_url" name="scopus_url">
                                <div class="invalid-feedback" id="err_scopus_url"></div>
                            </span>

                            <!-- Web of Science URL -->
                            <span class="input-set">
                                <label>Web of Science URL</label>
                                <input type="url" id="web_of_science_url" name="web_of_science_url">
                                <div class="invalid-feedback" id="err_web_of_science_url"></div>
                            </span>

                            <!-- Profile Image  -->
                            <span class="input-set">
                                <label>Profile Image (JPEG / PNG / WEBP, max) </label>
                                <input type="file" id="profile_image" name="profile_image"
                                    accept=".jpg,.jpeg,.png,.webp">
                                <div class="invalid-feedback" id="err_profile_image"></div>
                                <img id="ebImagePreview" src="" class="mt-2 rounded d-none"
                                    style="height:60px;width:60px;object-fit:cover;">
                            </span>

                            <!-- Sequence -->
                            <span class="input-set">
                                <label>Sequence</label>
                                <input type="number" min="0" id="sequence" name="sequence" value="0">
                                <div class="invalid-feedback" id="err_sequence"></div>
                            </span>

                            <!-- checklist -->
                            <div class="rjf-checklist">
                                <label for="is_active">
                                    <input type="checkbox" role="switch" id="is_active" name="is_active" checked>
                                    Active
                                </label>
                            </div>

                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">

                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>

                            <button type="submit" class="blue" id="ebSaveBtn">
                                <span id="ebSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                                <span id="ebSaveBtnText">Save</span>
                            </button>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div class="modal fade" id="ebDeleteModal" tabindex="-1" aria-labelledby="ebDeleteModalLabel" aria-hidden="true">
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
                            Do you really want to Delete? <br>
                            The member and their profile image will be permanently removed.
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="ebConfirmDeleteBtn"> <span id="ebDeleteSpinner">
                                Delete
                            </span> </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Keep it
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="ebToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="ebToastIcon"></span>
                    <div>
                        <div id="ebToastTitle" class="fw-semibold" style="font-size:14px;"></div>
                        <div id="ebToastMsg" class="opacity-75" style="font-size:13px;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="ebToastBar"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const API_BASE = '{{ url('/api/admin/editorial-board') }}';
            // ASSUMPTION: adjust this if your journals admin API lives elsewhere.
            const JOURNALS_API = '{{ url('/api/admin/journals') }}?page=1&per_page=100';
            const TOKEN = localStorage.getItem('jwt_token') || '';
            const authHeaders = () => ({
                'Accept': 'application/json',
                'Authorization': `Bearer ${TOKEN}`
            });

            const ebModalEl = document.getElementById('ebModal');
            const ebDeleteModalEl = document.getElementById('ebDeleteModal');
            let deleteTargetId = null;
            let cachedMembers = [];
            let cachedJournals = [];

            const ROLE_ORDER = [
                'Editor-in-Chief',
                'Managing Editor',
                'Executive Editor',
                'Editors',
                'Associate Editors',
                'Members',
            ];

            /* ── Toast ──────────────────────────────────────────────────── */
            function showToast(type, title, msg) {
                const el = document.getElementById('ebToast');
                document.getElementById('ebToastTitle').textContent = title;
                const msgEl = document.getElementById('ebToastMsg');
                msgEl.textContent = msg || '';
                msgEl.style.display = msg ? 'block' : 'none';
                document.getElementById('ebToastIcon').innerHTML = type === 'success' ?
                    `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>` :
                    `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
                el.classList.remove('bg-success', 'bg-danger');
                el.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
                const bar = document.getElementById('ebToastBar');
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

            /* ── Errors ─────────────────────────────────────────────────── */
            function clearErrors() {
                document.querySelectorAll('[id^="err_"]').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            }

            function showErrors(errors) {
                Object.entries(errors).forEach(([field, msgs]) => {
                    const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                    const err = document.getElementById(`err_${field}`);
                    if (err) err.textContent = msg;
                    document.getElementById(field)?.classList.add('is-invalid');
                });
            }

            /* ── Form helpers ───────────────────────────────────────────── */
            const TEXT_FIELDS = [
                'journal_id', 'role', 'name', 'designation', 'department', 'institute',
                'university_or_org', 'city', 'email',
                'orcid_url', 'scopus_url', 'web_of_science_url', 'sequence',
            ];

            function resetForm() {
                document.getElementById('ebForm').reset();
                document.getElementById('ebId').value = '';
                document.getElementById('ebMethod').value = 'POST';
                document.getElementById('is_active').checked = true;
                document.getElementById('sequence').value = 0;
                document.getElementById('journal_id').value = '';
                document.getElementById('ebImagePreview').classList.add('d-none');
                document.getElementById('ebImagePreview').src = '';
                clearErrors();
            }

            function fillForm(m) {
                TEXT_FIELDS.forEach(f => {
                    const el = document.getElementById(f);
                    if (el) el.value = m[f] ?? '';
                });
                document.getElementById('is_active').checked = !!Number(m.is_active);
                document.getElementById('ebId').value = m.id;
                document.getElementById('ebMethod').value = 'PUT';

                if (m.profile_image) {
                    const img = document.getElementById('ebImagePreview');
                    img.src = `/storage/${m.profile_image}`;
                    img.classList.remove('d-none');
                }
            }

            /* ── Load journals for dropdowns ───────────────────────────── */
            async function loadJournals() {
                try {
                    const res = await fetch(JOURNALS_API, {
                        headers: authHeaders()
                    });
                    const json = await res.json();
                    // JournalsController::adminIndex() paginates, so the array
                    // is nested at json.data.data (Laravel paginate() shape).
                    cachedJournals = json.data?.data ?? [];

                    const formSelect = document.getElementById('journal_id');
                    const filterSelect = document.getElementById('journalFilter');

                    cachedJournals.forEach(j => {
                        const opt1 = document.createElement('option');
                        opt1.value = j.id;
                        opt1.textContent = j.title;
                        formSelect.appendChild(opt1);

                        const opt2 = document.createElement('option');
                        opt2.value = j.id;
                        opt2.textContent = j.title;
                        filterSelect.appendChild(opt2);
                    });
                } catch (e) {
                    console.error('Failed to load journals list:', e.message);
                }
            }

            document.getElementById('journalFilter').addEventListener('change', () => loadMembers());

            /* ── Open Add modal ─────────────────────────────────────────── */
            document.getElementById('openEbModal').addEventListener('click', () => {
                resetForm();
                document.getElementById('ebModalTitle').textContent = 'Add Member';
                document.getElementById('ebSaveBtnText').textContent = 'Save';
                bootstrap.Modal.getOrCreateInstance(ebModalEl).show();
            });

            /* ── Image preview on select ───────────────────────────────── */
            document.getElementById('profile_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                const img = document.getElementById('ebImagePreview');
                img.src = URL.createObjectURL(file);
                img.classList.remove('d-none');
            });

            /* ── Save (Create / Update) ────────────────────────────────── */
            document.getElementById('ebSaveBtn').addEventListener('click', async () => {
                clearErrors();

                const id = document.getElementById('ebId').value;
                const method = document.getElementById('ebMethod').value;
                const spinner = document.getElementById('ebSaveSpinner');
                const btnText = document.getElementById('ebSaveBtnText');

                spinner.classList.remove('d-none');
                btnText.textContent = method === 'PUT' ? 'Updating…' : 'Saving…';

                const formData = new FormData(document.getElementById('ebForm'));
                formData.set('is_active', document.getElementById('is_active').checked ? '1' : '0');
                if (method === 'PUT') formData.append('_method', 'PUT');

                const url = method === 'PUT' ? `${API_BASE}/${id}` : API_BASE;

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${TOKEN}`,
                            'Accept': 'application/json'
                        },
                        body: formData,
                    });
                    const json = await res.json();

                    if (!res.ok) {
                        if (res.status === 422 && json.errors) {
                            showErrors(json.errors);
                            showToast('error', 'Validation failed',
                                'Please fix the highlighted fields.');
                        } else {
                            showToast('error', 'Error', json.message ?? 'Something went wrong.');
                        }
                        return;
                    }

                    bootstrap.Modal.getOrCreateInstance(ebModalEl).hide();
                    showToast('success', method === 'PUT' ? 'Updated!' : 'Created!', json.message ??
                    '');
                    loadMembers();

                } catch (err) {
                    showToast('error', 'Request failed', err.message);
                } finally {
                    spinner.classList.add('d-none');
                    btnText.textContent = method === 'PUT' ? 'Update' : 'Save';
                }
            });

            /* ── Delete flow ────────────────────────────────────────────── */
            function askDelete(id) {
                deleteTargetId = id;
                bootstrap.Modal.getOrCreateInstance(ebDeleteModalEl).show();
            }

            document.getElementById('ebConfirmDeleteBtn').addEventListener('click', async () => {
                if (!deleteTargetId) return;
                const spinner = document.getElementById('ebDeleteSpinner');
                spinner.classList.remove('d-none');

                try {
                    const res = await fetch(`${API_BASE}/${deleteTargetId}`, {
                        method: 'DELETE',
                        headers: authHeaders(),
                    });
                    const json = await res.json();

                    if (!res.ok) {
                        showToast('error', 'Error', json.message ?? 'Failed to delete member.');
                        return;
                    }

                    bootstrap.Modal.getOrCreateInstance(ebDeleteModalEl).hide();
                    showToast('success', 'Deleted!', json.message ?? '');
                    loadMembers();

                } catch (err) {
                    showToast('error', 'Request failed', err.message);
                } finally {
                    spinner.classList.add('d-none');
                    deleteTargetId = null;
                }
            });

            /* ── Toggle status ──────────────────────────────────────────── */
            async function toggleStatus(id) {
                try {
                    const res = await fetch(`${API_BASE}/${id}/toggle`, {
                        method: 'PATCH',
                        headers: authHeaders(),
                    });
                    const json = await res.json();

                    if (!res.ok) {
                        showToast('error', 'Error', json.message ?? 'Failed to update status.');
                        return;
                    }

                    showToast('success', 'Status updated', '');
                    loadMembers();

                } catch (err) {
                    showToast('error', 'Request failed', err.message);
                }
            }

            /* ── Render ─────────────────────────────────────────────────── */
            const esc = s => s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : '';
            const initials = name => (name || '?').trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('')
                .toUpperCase();

            function journalCell(m) {
                if (m.journal && m.journal.title) {
                    return `<span class="edit-btn" title="${esc(m.journal.title)}">${esc(m.journal.title)}</span>`;
                }
                return `<span class="eb-muted-cell">Site-wide</span>`;
            }

            function renderRows(members) {
                return members.map(m => `
                <tr>
                    <td></td>
                    <td>${journalCell(m)}</td>
                    <td><span class="green-btn" title="${esc(m.role)}">${esc(m.role)}</span></td>
                    <td class="eb-name-cell" title="${esc(m.name)}">${esc(m.name)}</td>
                    <td class="eb-text-cell" title="${esc(m.designation || '')}">${m.designation ? esc(m.designation) : '<span class="eb-muted-cell">—</span>'}</td>
                    <td class="eb-text-cell" title="${esc(m.department || '')}">${m.department ? esc(m.department) : '<span class="eb-muted-cell">—</span>'}</td>
                    <td>${m.sequence ?? 0}</td>
                    <td>
                        <span class="${Number(m.is_active) ? 'green-btn' : 'delete-btn'}" style="cursor:pointer" onclick="window.__ebToggle(${m.id})">
                            ${Number(m.is_active) ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex">
                            <button class="edit-btn" onclick="window.__ebEdit(${m.id})">Edit</button>
                            <button class="delete-btn" onclick="window.__ebDelete(${m.id})">Delete</button>
                        </div>
                    </td>
                </tr>`).join('');
            }

            function renderPage(members) {
                document.getElementById('ebLoading').classList.add('d-none');

                if (!members.length) {
                    document.getElementById('ebEmpty').classList.remove('d-none');
                    document.getElementById('ebTableWrap').classList.add('d-none');
                    return;
                }

                document.getElementById('ebEmpty').classList.add('d-none');
                document.getElementById('ebTableWrap').classList.remove('d-none');

                // sort by role order, then sequence, then name
                const roleRank = r => {
                    const i = ROLE_ORDER.indexOf(r);
                    return i === -1 ? ROLE_ORDER.length : i;
                };
                const sorted = [...members].sort((a, b) => {
                    const rr = roleRank(a.role) - roleRank(b.role);
                    if (rr !== 0) return rr;
                    const seqDiff = (a.sequence ?? 0) - (b.sequence ?? 0);
                    if (seqDiff !== 0) return seqDiff;
                    return (a.name || '').localeCompare(b.name || '');
                });

                document.getElementById('ebTbody').innerHTML = renderRows(sorted);
            }

            /* ── Load (adminIndex — includes inactive) ─────────────────── */
            async function loadMembers() {
                document.getElementById('ebLoading').classList.remove('d-none');
                document.getElementById('ebEmpty').classList.add('d-none');
                document.getElementById('ebTableWrap').classList.add('d-none');

                const journalId = document.getElementById('journalFilter').value;
                const url = journalId ? `${API_BASE}?journal_id=${journalId}` : API_BASE;

                try {
                    const res = await fetch(url, {
                        headers: authHeaders()
                    });
                    const json = await res.json();
                    cachedMembers = json.data || [];
                    renderPage(cachedMembers);
                } catch (e) {
                    document.getElementById('ebLoading').classList.add('d-none');
                    showToast('error', 'Load failed', e.message);
                }
            }

            /* ── Global handlers (used by inline onclick) ─────────────────── */
            window.__ebEdit = (id) => {
                const member = cachedMembers.find(m => m.id === id);
                if (!member) return;
                resetForm();
                fillForm(member);
                document.getElementById('ebModalTitle').textContent = 'Edit Member';
                document.getElementById('ebSaveBtnText').textContent = 'Update';
                bootstrap.Modal.getOrCreateInstance(ebModalEl).show();
            };

            window.__ebDelete = (id) => askDelete(id);
            window.__ebToggle = (id) => toggleStatus(id);

            loadJournals();
            loadMembers();
        });
    </script>
@endsection
