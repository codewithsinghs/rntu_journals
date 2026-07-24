@extends('layouts.admin')

@section('content')

    <!-- About Page Contents -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    About Page Contents
                </div>

                <!-- Data Loading -->
                <div id="abcLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                </div>

                <div id="abcFormContainer" class="d-none">

                    <!-- From -->
                    <form id="abcForm" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" id="abcId">
                        <input type="hidden" id="abcMethod" value="POST">

                        <!-- Section Top -->

                        <div class="inner_fp">

                            <div class="ssid">About Section</div>

                            <div class="content_container">

                                <div class="content_inner">
                                    <div class="content_partitions">

                                        <!-- Badge / Eyebrow Label -->
                                        <div class="partitions_inner">
                                            <label>Badge / Eyebrow Label <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="about_badge" name="about_badge"
                                                placeholder="ABOUT" required>
                                            <div class="invalid-feedback" id="err_about_badge"></div>
                                        </div>

                                        <!-- Main Heading -->
                                        <div class="partitions_inner">
                                            <label>Main Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="about_heading"
                                                name="about_heading" placeholder="RNTU Journals" required>
                                            <div class="invalid-feedback" id="err_about_heading"></div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Description Top -->
                                <div class="content_inner">
                                    <div class="heading_p">Description Top <span class="text-danger">*</span></div>
                                    <div id="ck_about_description_1" class="ckeditor-wrapper"></div>
                                    <textarea class="content_show d-none" rows="2" id="about_description_1" name="about_description_1"></textarea>
                                    <div class="error-red" id="err_about_description_1"></div>
                                </div>

                                <!-- Description Bottom -->
                                <div class="content_inner">
                                    <div class="heading_p">Description Bottom</div>
                                    <div id="ck_about_description_2" class="ckeditor-wrapper"></div>
                                    <textarea class="content_show d-none" id="about_description_2" name="about_description_2"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <!-- Image 1-->
                                        <div class="content_inner">
                                            <div class="heading_p">Image<span class="hbc-hint">JPG/PNG/WEBP — max 2MB</span>
                                            </div>
                                            <input type="file" class="form-control form-control-sm"
                                                id="about_section_img1" name="about_section_img1"
                                                accept="image/jpg,image/jpeg,image/png,image/webp">
                                            <div class="invalid-feedback" id="err_about_section_img1"></div>
                                            <div id="previewAboutImg1" class="hbc-img-preview d-none">
                                                <div class="text-muted">Current:</div>
                                                <img id="thumbAboutImg1" src="" alt="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <!-- Image 2-->
                                        <div class="content_inner">
                                            <div class="heading_p">Image<span class="hbc-hint">JPG/PNG/WEBP — max 2MB</span>
                                            </div>
                                            <input type="file" class="form-control form-control-sm"
                                                id="about_section_img2" name="about_section_img2"
                                                accept="image/jpg,image/jpeg,image/png,image/webp">
                                            <div class="invalid-feedback" id="err_about_section_img2"></div>
                                            <div id="previewAboutImg2" class="hbc-img-preview d-none">
                                                <div class="text-muted">Current:</div>
                                                <img id="thumbAboutImg2" src="" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Section Second -->

                        <div class="inner_fp">

                            <div class="ssid">Second Two</div>

                            <div class="content_container">

                                <div class="content_inner">
                                    <div class="content_partitions">

                                        <!-- Badge / Eyebrow Label -->
                                        <div class="partitions_inner">
                                            <label>Badge / Eyebrow Label <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="why_badge" name="why_badge"
                                                placeholder="PUBLISHING" required>
                                            <div class="invalid-feedback" id="err_why_badge"></div>
                                        </div>

                                        <!-- Main Heading -->
                                        <div class="partitions_inner">
                                            <label>Main Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="why_heading"
                                                name="why_heading" placeholder="Why Researchers Trust Us" required>
                                            <div class="invalid-feedback" id="err_why_heading"></div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Description Top -->
                                <div class="content_inner">
                                    <div class="heading_p">Description Top <span class="text-danger">*</span></div>
                                    <div id="ck_why_description_1" class="ckeditor-wrapper"></div>
                                    <textarea class="content_show d-none" rows="2" id="why_description_1" name="why_description_1"></textarea>
                                    <div class="error-red" id="err_why_description_1"></div>
                                </div>

                                <!-- Description Bottom -->
                                <div class="content_inner">
                                    <div class="heading_p">Description Bottom</div>
                                    <div id="ck_why_description_2" class="ckeditor-wrapper"></div>
                                    <textarea class="content_show d-none" id="why_description_2" name="why_description_2"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <!-- Image -->
                                        <div class="content_inner">
                                            <div class="heading_p">Image<span class="hbc-hint">JPG/PNG/WEBP — max
                                                    2MB</span></div>
                                            <input type="file" class="form-control form-control-sm"
                                                id="why_section_image" name="why_section_image"
                                                accept="image/jpg,image/jpeg,image/png,image/webp">
                                            <div class="invalid-feedback" id="err_why_section_image"></div>
                                            <div id="previewWhyImg" class="hbc-img-preview d-none">
                                                <div class="text-muted">Current:</div>
                                                <img id="thumbWhyImg" src="" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <section class="term_con">

                                    <!-- Approve Abstract -->
                                    <div class="button_d">
                                        <button type="button" class="green_d" id="abcSaveBtn">
                                            <span id="abcSaveSpinner"
                                                class="spinner-border spinner-border-sm d-none me-1"></span>
                                            <span id="abcSaveBtnText">Save</span>
                                        </button>
                                    </div>

                                </section>

                            </div>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const API_BASE = '/api/admin/about-content';
            const TOKEN = localStorage.getItem('jwt_token') || '';

            const authHeaders = () => ({
                'Accept': 'application/json',
                'Authorization': `Bearer ${TOKEN}`,
            });

            /* ─────────────────────────────────────────────────────────────
               CKEditor — initialise ONLY after modal is fully visible.
               CKEditor CANNOT mount into hidden (display:none) elements.
               Uses the CKEDITOR global already loaded in admin.blade.php.
            ───────────────────────────────────────────────────────────── */
            const CK_FIELDS = [{
                    id: 'about_description_1',
                    required: true
                },
                {
                    id: 'about_description_2',
                    required: false
                },
                {
                    id: 'why_description_1',
                    required: true
                },
                {
                    id: 'why_description_2',
                    required: false
                },
            ];

            const editors = {};
            let editorsReady = false;
            let pendingFill = null;
            const formModalEl = document.getElementById('formModal');

            const TOOLBAR = [
                'heading', '|',
                'bold', 'italic', 'underline', '|',
                'bulletedList', 'numberedList', '|',
                'blockQuote', 'link', '|',
                'undo', 'redo',
            ];



            async function initEditors() {
                for (const {
                        id
                    }
                    of CK_FIELDS) {
                    if (editors[id]) {
                        await editors[id].destroy();
                        delete editors[id];
                    }
                    editors[id] = await CKEDITOR.ClassicEditor.create(
                        document.getElementById(`ck_${id}`), {
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
                                items: TOOLBAR
                            },
                            placeholder: 'Enter content…',
                        }
                    );
                    editors[id].model.document.on('change:data', () => {
                        document.getElementById(id).value = editors[id].getData();
                    });
                }
            }

            /* ─────────────────────────────────────────────────────────────
               Plain (non-CK) text fields
            ───────────────────────────────────────────────────────────── */
            const PLAIN_FIELDS = [
                'about_badge', 'about_heading',
                'why_badge', 'why_heading',
            ];

            /* ─────────────────────────────────────────────────────────────
               Image preview helpers
            ───────────────────────────────────────────────────────────── */
            const IMAGE_FIELDS = [{
                    input: 'about_section_img1',
                    preview: 'previewAboutImg1',
                    thumb: 'thumbAboutImg1'
                },
                {
                    input: 'about_section_img2',
                    preview: 'previewAboutImg2',
                    thumb: 'thumbAboutImg2'
                },
                {
                    input: 'why_section_image',
                    preview: 'previewWhyImg',
                    thumb: 'thumbWhyImg'
                },
            ];

            function showImagePreview(previewId, thumbId, url) {
                if (url) {
                    document.getElementById(thumbId).src = url;
                    document.getElementById(previewId).classList.remove('d-none');
                } else {
                    document.getElementById(previewId).classList.add('d-none');
                    document.getElementById(thumbId).src = '';
                }
            }

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
                document.querySelectorAll('.ckeditor-wrapper.is-invalid').forEach(el => el.classList.remove(
                    'is-invalid'));
            }

            function showErrors(errors) {
                Object.entries(errors).forEach(([field, msgs]) => {
                    const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                    const errEl = document.getElementById(`err_${field}`);
                    if (errEl) errEl.textContent = msg;
                    if (CK_FIELDS.some(f => f.id === field)) {
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
                document.getElementById('abcForm').reset();
                document.getElementById('abcId').value = '';
                document.getElementById('abcMethod').value = 'POST';
                CK_FIELDS.forEach(({
                    id
                }) => {
                    if (editors[id]) editors[id].setData('');
                    const ta = document.getElementById(id);
                    if (ta) ta.value = '';
                });
                IMAGE_FIELDS.forEach(({
                    preview,
                    thumb
                }) => showImagePreview(preview, thumb, null));
                clearErrors();
            }

            function fillForm(r) {
                PLAIN_FIELDS.forEach(f => {
                    const el = document.getElementById(f);
                    if (el) el.value = r[f] ?? '';
                });
                CK_FIELDS.forEach(({
                    id
                }) => {
                    if (editors[id]) editors[id].setData(r[id] ?? '');
                    const ta = document.getElementById(id);
                    if (ta) ta.value = r[id] ?? '';
                });
                document.getElementById('abcId').value = r.id;
                document.getElementById('abcMethod').value = 'PUT';
                showImagePreview('previewAboutImg1', 'thumbAboutImg1', r.about_section_img1_url || null);
                showImagePreview('previewAboutImg2', 'thumbAboutImg2', r.about_section_img2_url || null);
                showImagePreview('previewWhyImg', 'thumbWhyImg', r.why_section_image_url || null);
            }

            function syncEditors() {
                CK_FIELDS.forEach(({
                    id
                }) => {
                    if (editors[id]) document.getElementById(id).value = editors[id].getData();
                });
            }

            /* ─────────────────────────────────────────────────────────────
               Render helpers
            ───────────────────────────────────────────────────────────── */
            function htmlOrDash(s) {
                return s || '<span class="text-muted" style="font-size:12px;font-style:italic;">—</span>';
            }

            /* ─────────────────────────────────────────────────────────────
               Render record — description-only cards
               (Badge / Heading / Images are edited in the modal but are
               intentionally NOT shown in this summary view — only the
               descriptions for "About Section" and "Why Researchers
               Trust Us" are displayed here.)
            ───────────────────────────────────────────────────────────── */


            /* ─────────────────────────────────────────────────────────────
               Add modal
            ───────────────────────────────────────────────────────────── */


            /* ─────────────────────────────────────────────────────────────
               Load
            ───────────────────────────────────────────────────────────── */

            document.getElementById('abcSaveBtn').addEventListener('click', async () => {
                clearErrors();
                syncEditors();

                let hasError = false;
                CK_FIELDS.forEach(f => {
                    if (f.required) {
                        const val = editors[f.id] ? editors[f.id].getData() : '';
                        if (!val.trim() || val === '<p>&nbsp;</p>') {
                            document.getElementById(`ck_${f.id}`)?.classList.add('is-invalid');
                            const errEl = document.getElementById(`err_${f.id}`);
                            if (errEl) errEl.textContent = 'This field is required.';
                            hasError = true;
                        }
                    }
                });
                if (hasError) return;

                const id = document.getElementById('abcId').value;
                const method = document.getElementById('abcMethod').value;
                const spinner = document.getElementById('abcSaveSpinner');
                const btnText = document.getElementById('abcSaveBtnText');

                spinner.classList.remove('d-none');
                btnText.textContent = method === 'PUT' ? 'Updating…' : 'Saving…';

                const formData = new FormData(document.getElementById('abcForm'));
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
                document.getElementById('abcLoading').classList.remove('d-none');
                document.getElementById('abcFormContainer').classList.add('d-none');
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
                        document.getElementById('abcSaveBtnText').textContent = 'Update';
                    } else {
                        resetForm();
                        document.getElementById('abcSaveBtnText').textContent = 'Save';
                    }

                    document.getElementById('abcLoading').classList.add('d-none');
                    document.getElementById('abcFormContainer').classList.remove('d-none');
                } catch (e) {
                    document.getElementById('abcLoading').classList.add('d-none');
                    showToast('error', 'Load failed', e.message);
                }
            }

            loadRecord();
        });
    </script>
@endsection
