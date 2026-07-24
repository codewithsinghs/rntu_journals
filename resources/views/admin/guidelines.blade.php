@extends('layouts.admin')

@section('content')
    <!-- Guidelines Page Contents -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Guidelines Page Contents
                </div>

                <!-- Data Load -->
                <div id="glLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                </div>

                <div id="glFormContainer" class="d-none">

                    <!-- Form -->
                    <form id="glForm" novalidate>
                        @csrf
                        <input type="hidden" id="glId">
                        <input type="hidden" id="glMethod" value="POST">

                        <!-- Instructions for Authors -->
                        <div class="inner_fp">

                            <div class="ssid">Instructions for Authors</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">AUTHOR GUIDELINES</span></label>
                                            <input type="text" class="content_show" id="author_badge" name="author_badge"
                                                placeholder="AUTHOR GUIDELINES" required>
                                            <div class="invalid-feedback" id="err_author_badge"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="author_heading"
                                                name="author_heading" placeholder="Instructions for Authors" required>
                                            <div class="invalid-feedback" id="err_author_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_author_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="author_description" name="author_description"></textarea>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Submission Process -->
                        <div class="inner_fp">

                            <div class="ssid">Submission Process</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">PROCESS</span></label>
                                            <input type="text" class="content_show" id="process_badge"
                                                name="process_badge" placeholder="PROCESS" required>
                                            <div class="invalid-feedback" id="err_process_badge"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="process_heading"
                                                name="process_heading"placeholder="Submission Process" required>
                                            <div class="invalid-feedback" id="err_process_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_process_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="process_description" name="process_description"></textarea>
                                        <div class="gl-ck-error" id="err_process_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- New Manuscript -->
                        <div class="inner_fp">

                            <div class="ssid">New Manuscript</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">MANUSCRIPT PREPARATION</span></label>
                                            <input type="text" class="content_show" id="manuscript_badge"
                                                name="manuscript_badge" placeholder="MANUSCRIPT PREPARATION" required>
                                            <div class="invalid-feedback" id="err_manuscript_badge"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="manuscript_heading"
                                                name="manuscript_heading" placeholder="New Manuscripts" required>
                                            <div class="invalid-feedback" id="err_manuscript_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_manuscript_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="manuscript_description" name="manuscript_description"></textarea>
                                        <div class="gl-ck-error" id="err_manuscript_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Formatting -->
                        <div class="inner_fp">

                            <div class="ssid">Formatting</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">DOCUMENT FORMAT REFERENCE</span></label>
                                            <input type="text" class="content_show" id="formatting_badge1"
                                                name="formatting_badge1" placeholder="DOCUMENT FORMAT REFERENCE" required>
                                            <div class="invalid-feedback" id="err_formatting_badge1"></div>
                                        </div>

                                        <!-- Badge 2 -->
                                        <div class="partitions_inner">
                                            <label>Badge 2 <span class="text-danger">*</span> <span class="gl-hint">e.g.
                                                    IEEE STYLE</span></label>
                                            <input type="text" class="content_show" id="formatting_badge2"
                                                name="formatting_badge2" placeholder="IEEE STYLE" required>
                                            <div class="invalid-feedback" id="err_formatting_badge2"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="formatting_heading"
                                                name="formatting_heading" placeholder="Formatting" required>
                                            <div class="invalid-feedback" id="err_formatting_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_formatting_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="formatting_description" name="formatting_description"></textarea>
                                        <div class="gl-ck-error" id="err_formatting_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Page Layout -->
                        <div class="inner_fp">

                            <div class="ssid">Page Layout</div>

                            <div class="content_container">

                                <div class="content_inner">


                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">PAGE LAYOUT</span></label>
                                            <input type="text" class="content_show" id="layout_badge1"
                                                name="layout_badge1" placeholder="PAGE LAYOUT" required>
                                            <div class="invalid-feedback" id="err_layout_badge1"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="layout_heading"
                                                name="layout_heading" placeholder="New Manuscripts" required>
                                            <div class="invalid-feedback" id="err_layout_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_layout_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="layout_description" name="layout_description"></textarea>
                                        <div class="gl-ck-error" id="err_layout_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Acknowledgement -->
                        <div class="inner_fp">

                            <div class="ssid">Acknowledgement</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">RNTU JOURNALS</span></label>
                                            <input type="text" class="content_show" id="acknowlegdement_badge1"
                                                name="acknowlegdement_badge1" placeholder="RNTU JOURNALS" required>
                                            <div class="invalid-feedback" id="err_acknowlegdement_badge1"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="acknowlegdement_heading"
                                                name="acknowlegdement_heading" placeholder="New Manuscripts" required>
                                            <div class="invalid-feedback" id="err_acknowlegdement_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_acknowlegdement_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="acknowlegdement_description" name="acknowlegdement_description"></textarea>
                                        <div class="gl-ck-error" id="err_acknowlegdement_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Button -->
                        <section class="term_con">
                            <div class="button_d">
                                <button type="button" class="green_d" id="glSaveBtn">
                                    <span id="glSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                                    <span id="glSaveBtnText">Save</span>
                                </button>
                            </div>
                        </section>

                    </form>

                </div>

            </div>
        </div>
    </section>



    <!-- Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="glToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="glToastIcon"></span>
                    <div>
                        <div id="glToastTitle" class="fw-semibold" style="font-size:14px;"></div>
                        <div id="glToastMsg" class="opacity-75" style="font-size:13px;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="glToastBar"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const API_BASE = '/api/admin/guidelines';
            const TOKEN = localStorage.getItem('jwt_token') || '';
            const authHeaders = () => ({
                'Accept': 'application/json',
                'Authorization': `Bearer ${TOKEN}`
            });

            /* ── CKEditor fields ────────────────────────────────────────── */
            const editors = {};
            const CK_FIELDS = [
                'author_description',
                'process_description',
                'manuscript_description',
                'formatting_description',
                'layout_description',
                'acknowlegdement_description',
            ];

            /*
             * KEY FIX: CKEditor CANNOT mount into hidden (display:none) elements.
             * We initialise editors only once — on the first 'shown.bs.modal' event,
             * then reuse them on subsequent opens.
             */
            let editorsReady = false;
            let pendingFill = null;




            async function initEditors() {
                for (const field of CK_FIELDS) {
                    if (editors[field]) {
                        await editors[field].destroy();
                        delete editors[field];
                    }

                    editors[field] = await CKEDITOR.ClassicEditor.create(
                        document.getElementById(`ck_${field}`), {
                            licenseKey: 'GPL',
                            removePlugins: [
                                'CKBox', 'CKFinder', 'EasyImage',
                                'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges',
                                'RealTimeCollaborativeRevisionHistory', 'PresenceList',
                                'Comments', 'TrackChanges', 'TrackChangesData', 'RevisionHistory',
                                'Pagination', 'WProofreader', 'MathType', 'SlashCommand',
                                'Template', 'DocumentOutline', 'FormatPainter', 'TableOfContents',
                                'PasteFromOfficeEnhanced', 'AIAssistant', 'MultiLevelList',
                                'CaseChange',
                            ],
                            toolbar: {
                                items: [
                                    'heading', '|',
                                    'bold', 'italic', 'underline', '|',
                                    'bulletedList', 'numberedList', '|',
                                    'blockQuote', 'link', '|',
                                    'undo', 'redo'
                                ]
                            },
                            placeholder: 'Enter content…',
                        }
                    );

                    editors[field].model.document.on('change:data', () => {
                        document.getElementById(field).value = editors[field].getData();
                    });
                }
            }

            /* ── Toast ──────────────────────────────────────────────────── */
            function showToast(type, title, msg) {
                const el = document.getElementById('glToast');
                document.getElementById('glToastTitle').textContent = title;
                const msgEl = document.getElementById('glToastMsg');
                msgEl.textContent = msg || '';
                msgEl.style.display = msg ? 'block' : 'none';
                document.getElementById('glToastIcon').innerHTML = type === 'success' ?
                    `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>` :
                    `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
                el.classList.remove('bg-success', 'bg-danger');
                el.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
                const bar = document.getElementById('glToastBar');
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
                document.querySelectorAll('.gl-ck-wrap.is-invalid').forEach(el => el.classList.remove(
                'is-invalid'));
            }

            function showErrors(errors) {
                Object.entries(errors).forEach(([field, msgs]) => {
                    const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                    const err = document.getElementById(`err_${field}`);
                    if (err) err.textContent = msg;
                    if (CK_FIELDS.includes(field)) {
                        document.getElementById(`ck_${field}`)?.classList.add('is-invalid');
                    } else {
                        document.getElementById(field)?.classList.add('is-invalid');
                    }
                });
            }

            /* ── Form helpers ───────────────────────────────────────────── */
            const TEXT_FIELDS = [
                'author_badge', 'author_heading',
                'process_badge', 'process_heading',
                'manuscript_badge', 'manuscript_heading',
                'formatting_badge1', 'formatting_badge2', 'formatting_heading',
                'layout_badge1', 'layout_heading',
                'acknowlegdement_badge1', 'acknowlegdement_heading',
            ];

            function resetForm() {
                document.getElementById('glForm').reset();
                document.getElementById('glId').value = '';
                document.getElementById('glMethod').value = 'POST';
                CK_FIELDS.forEach(f => {
                    if (editors[f]) editors[f].setData('');
                    document.getElementById(f).value = '';
                });
                clearErrors();
            }

            function fillForm(r) {
                TEXT_FIELDS.forEach(f => {
                    const el = document.getElementById(f);
                    if (el) el.value = r[f] ?? '';
                });
                CK_FIELDS.forEach(f => {
                    if (editors[f]) editors[f].setData(r[f] ?? '');
                    document.getElementById(f).value = r[f] ?? '';
                });
                document.getElementById('glId').value = r.id;
                document.getElementById('glMethod').value = 'PUT';
            }

            function syncEditors() {
                CK_FIELDS.forEach(f => {
                    if (editors[f]) document.getElementById(f).value = editors[f].getData();
                });
            }

            document.getElementById('glSaveBtn').addEventListener('click', async () => {
                clearErrors();
                syncEditors();

                let hasError = false;
                CK_FIELDS.forEach(f => {
                    const val = editors[f] ? editors[f].getData() : '';
                    if (!val.trim() || val === '<p>&nbsp;</p>') {
                        document.getElementById(`ck_${f}`)?.classList.add('is-invalid');
                        const errEl = document.getElementById(`err_${f}`);
                        if (errEl) errEl.textContent = 'This field is required.';
                        hasError = true;
                    }
                });
                if (hasError) return;

                const id = document.getElementById('glId').value;
                const method = document.getElementById('glMethod').value;
                const spinner = document.getElementById('glSaveSpinner');
                const btnText = document.getElementById('glSaveBtnText');

                spinner.classList.remove('d-none');
                btnText.textContent = method === 'PUT' ? 'Updating…' : 'Saving…';

                const formData = new FormData(document.getElementById('glForm'));
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

                    showToast('success', method === 'PUT' ? 'Updated!' : 'Created!', json.message ??
                    '');
                    loadRecord();

                } catch (err) {
                    showToast('error', 'Request failed', err.message);
                } finally {
                    spinner.classList.add('d-none');
                    btnText.textContent = method === 'PUT' ? 'Update' : 'Save';
                }
            });

            async function loadRecord() {
                document.getElementById('glLoading').classList.remove('d-none');
                document.getElementById('glFormContainer').classList.add('d-none');
                try {
                    const res = await fetch(API_BASE, {
                        headers: authHeaders()
                    });
                    const json = await res.json();
                    const raw = json.data;
                    let record = null;
                    if (raw) {
                        if (Array.isArray(raw)) record = raw[0] ?? null;
                        else if (Array.isArray(raw.data)) record = raw.data[0] ?? null;
                        else if (raw.id) record = raw;
                    }

                    if (!editorsReady) {
                        await initEditors();
                        editorsReady = true;
                    }

                    if (record && record.id) {
                        fillForm(record);
                        document.getElementById('glSaveBtnText').textContent = 'Update';
                    } else {
                        resetForm();
                        document.getElementById('glSaveBtnText').textContent = 'Save';
                    }

                    document.getElementById('glLoading').classList.add('d-none');
                    document.getElementById('glFormContainer').classList.remove('d-none');
                } catch (e) {
                    document.getElementById('glLoading').classList.add('d-none');
                    showToast('error', 'Load failed', e.message);
                }
            }

            loadRecord();
        });
    </script>
@endsection
