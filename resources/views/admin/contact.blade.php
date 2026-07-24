@extends('layouts.admin')

@section('content')
    {{-- Page Start --}}
    <section class="inner_p">

        <div class="content_top_wrapper">

            <div class="p_cards" style="padding: 20px;">

                <div class="heading" style="margin-bottom: 20px;">
                    Contact Page
                </div>

                <div id="ctcLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                </div>

                <div id="ctcFormContainer" class="d-none">
                    <form id="ctcForm" novalidate>
                        @csrf
                        <input type="hidden" id="ctcId">
                        <input type="hidden" id="ctcMethod" value="POST">

                        {{-- Page Badge --}}
                        <div class="inner_fp">

                            <div class="ssid">Page Badge</div>

                            <div class="content_container">

                                <!-- Badge / Eyebrow -->
                                <div class="content_inner">
                                    <div class="heading_p">Badge / Eyebrow <span class="text-danger">*</span></div>
                                    <input type="text" class="content_show" id="contact_badge" name="contact_badge"
                                        placeholder="CONTACT US" required>
                                    <div class="invalid-feedback" id="err_contact_badge"></div>
                                </div>

                            </div>

                        </div>

                        {{-- Contact Block 1 --}}
                        <div class="inner_fp mt-4">

                            <div class="ssid">Contact Block 1</div>

                            <div class="content_container">

                                <!-- Heading -->
                                <div class="content_inner">
                                    <div class="heading_p">Heading <span class="text-danger">*</span> </div>
                                    <input type="text" class="content_show" id="contact_heading1" name="contact_heading1"
                                        required>
                                    <div class="invalid-feedback" id="err_contact_heading1"></div>
                                </div>

                                <!-- Detail -->
                                <div class="content_inner">
                                    <div class="heading_p">Detail <span class="text-danger">*</span></div>
                                    <div id="ck_contact_detail1" class="ctc-ck-wrap"></div>
                                    <textarea class="d-none content_show" id="contact_detail1" name="contact_detail1"></textarea>
                                    <div class="error-red" id="err_contact_detail1"></div>
                                </div>

                            </div>

                        </div>

                        {{-- Contact Block 2 --}}
                        <div class="inner_fp mt-4">

                            <div class="ssid">Contact Block 2</div>

                            <div class="content_container">

                                <!-- Heading -->
                                <div class="content_inner">
                                    <div class="heading_p">Heading <span class="text-danger">*</span> </div>
                                    <input type="text" class="content_show" id="contact_heading2" name="contact_heading2"
                                        required>
                                    <div class="invalid-feedback" id="err_contact_heading2"></div>
                                </div>

                                <!-- Detail -->
                                <div class="content_inner">
                                    <div class="heading_p">Detail <span class="text-danger">*</span></div>
                                    <div id="ck_contact_detail2" class="ctc-ck-wrap"></div>
                                    <textarea class="d-none content_show" id="contact_detail2" name="contact_detail2"></textarea>
                                    <div class="error-red" id="err_contact_detail2"></div>
                                </div>

                            </div>

                        </div>

                        {{-- Contact Block 3 --}}
                        <div class="inner_fp mt-4">

                            <div class="ssid">Contact Block 3</div>

                            <div class="content_container">

                                <!-- Heading -->
                                <div class="content_inner">
                                    <div class="heading_p">Heading <span class="text-danger">*</span> </div>
                                    <input type="text" class="content_show" id="contact_heading3" name="contact_heading3"
                                        required>
                                    <div class="invalid-feedback" id="err_contact_heading3"></div>
                                </div>

                                <!-- Detail -->
                                <div class="content_inner">
                                    <div class="heading_p">Detail <span class="text-danger">*</span></div>
                                    <div id="ck_contact_detail3" class="ctc-ck-wrap"></div>
                                    <textarea class="d-none content_show" id="contact_detail3" name="contact_detail3"></textarea>
                                    <div class="error-red" id="err_contact_detail3"></div>
                                </div>

                            </div>

                            {{-- Button --}}
                            <section class="term_con">
                                <div class="button_d">
                                    <button type="button" class="green_d" id="ctcSaveBtn">
                                        <span id="ctcSaveSpinner"
                                            class="spinner-border spinner-border-sm d-none me-1"></span>
                                        <span id="ctcSaveBtnText">Save</span>
                                    </button>
                                </div>
                            </section>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </section>

    {{-- Toast --}}
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const API_BASE = '/api/admin/contacts';
            const TOKEN = localStorage.getItem('jwt_token') || '';

            const authHeaders = () => ({
                'Accept': 'application/json',
                'Authorization': `Bearer ${TOKEN}`,
            });

            /* ─────────────────────────────────────────────────────────────
               CKEditor fields
            ───────────────────────────────────────────────────────────── */
            const editors = {};
            const CK_FIELDS = ['contact_detail1', 'contact_detail2', 'contact_detail3'];

            let editorsReady = false;

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
                                    'bold', 'italic', 'underline', '|',
                                    'bulletedList', 'numberedList', '|',
                                    'link', '|',
                                    'undo', 'redo'
                                ]
                            },
                            placeholder: 'Enter detail…',
                        }
                    );

                    editors[field].model.document.on('change:data', () => {
                        document.getElementById(field).value = editors[field].getData();
                    });
                }
            }

            /* ─────────────────────────────────────────────────────────────
               Plain (non-CK) fields
            ───────────────────────────────────────────────────────────── */
            const PLAIN_FIELDS = [
                'contact_badge',
                'contact_heading1',
                'contact_heading2',
                'contact_heading3',
            ];

            /* ─────────────────────────────────────────────────────────────
               Toast
            ───────────────────────────────────────────────────────────── */
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
                el.classList.remove('bg-success', 'bg-danger');
                el.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
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

            /* ─────────────────────────────────────────────────────────────
               Error helpers
            ───────────────────────────────────────────────────────────── */
            function clearErrors() {
                document.querySelectorAll('[id^="err_"]').forEach(el => {
                    el.textContent = '';
                });
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.ctc-ck-wrap.is-invalid').forEach(el => el.classList.remove(
                    'is-invalid'));
            }

            function showErrors(errors) {
                Object.entries(errors).forEach(([field, msgs]) => {
                    const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                    const errEl = document.getElementById(`err_${field}`);
                    if (errEl) errEl.textContent = msg;
                    if (CK_FIELDS.includes(field)) {
                        document.getElementById(`ck_${field}`)?.classList.add('is-invalid');
                    } else {
                        document.getElementById(field)?.classList.add('is-invalid');
                    }
                });
            }

            /* ─────────────────────────────────────────────────────────────
               Form helpers
            ───────────────────────────────────────────────────────────── */
            function resetForm() {
                document.getElementById('ctcForm').reset();
                document.getElementById('ctcId').value = '';
                document.getElementById('ctcMethod').value = 'POST';
                CK_FIELDS.forEach(f => {
                    if (editors[f]) editors[f].setData('');
                    document.getElementById(f).value = '';
                });
                clearErrors();
            }

            function fillForm(r) {
                PLAIN_FIELDS.forEach(f => {
                    const el = document.getElementById(f);
                    if (el) el.value = r[f] ?? '';
                });
                CK_FIELDS.forEach(f => {
                    if (editors[f]) editors[f].setData(r[f] ?? '');
                    document.getElementById(f).value = r[f] ?? '';
                });
                document.getElementById('ctcId').value = r.id;
                document.getElementById('ctcMethod').value = 'PUT';
            }

            function syncEditors() {
                CK_FIELDS.forEach(f => {
                    if (editors[f]) document.getElementById(f).value = editors[f].getData();
                });
            }

            /* ─────────────────────────────────────────────────────────────
               Save / Update
            ───────────────────────────────────────────────────────────── */
            document.getElementById('ctcSaveBtn').addEventListener('click', async () => {
                clearErrors();
                syncEditors();

                // Client-side required check for CK fields
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

                const id = document.getElementById('ctcId').value;
                const method = document.getElementById('ctcMethod').value;
                const spinner = document.getElementById('ctcSaveSpinner');
                const btnText = document.getElementById('ctcSaveBtnText');

                spinner.classList.remove('d-none');
                btnText.textContent = method === 'PUT' ? 'Updating…' : 'Saving…';

                const formData = new FormData(document.getElementById('ctcForm'));
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

            /* ─────────────────────────────────────────────────────────────
               Load
            ───────────────────────────────────────────────────────────── */
            async function loadRecord() {
                document.getElementById('ctcLoading').classList.remove('d-none');
                document.getElementById('ctcFormContainer').classList.add('d-none');
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
                        document.getElementById('ctcSaveBtnText').textContent = 'Update';
                    } else {
                        resetForm();
                        document.getElementById('ctcSaveBtnText').textContent = 'Save';
                    }

                    document.getElementById('ctcLoading').classList.add('d-none');
                    document.getElementById('ctcFormContainer').classList.remove('d-none');
                } catch (e) {
                    document.getElementById('ctcLoading').classList.add('d-none');
                    showToast('error', 'Load failed', e.message);
                }
            }

            loadRecord();
        });
    </script>
@endsection
