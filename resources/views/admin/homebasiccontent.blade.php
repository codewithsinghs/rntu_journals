@extends('layouts.admin')

@section('content')


<section class="inner_p">
    <div class="content_top_wrapper">
        <div class="p_cards">

            <div class="heading">
                Settings
            </div>

            <div id="hbcLoading" class="text-center py-5">
                <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
            </div>


            <div id="hbcFormContainer" class="d-none">

                <form id="hbcForm" enctype="multipart/form-data" novalidate>

                    @csrf

                    <input type="hidden" id="hbcId">
                    <input type="hidden" id="hbcMethod" value="POST">


                    <!-- Aim & Scope -->

                    <div class="inner_fp">

                        <div class="ssid">Aim & Scope</div>

                        <div class="content_container">
                            <div class="content_inner">
                                <div class="content_partitions">

                                    <div class="partitions_inner">
                                        <label>Badge / Eyebrow Label <span class="text-danger">*</span></label>
                                        <input type="text" class="content_show" id="aim_and_scope_title_1" name="aim_and_scope_title_1" placeholder="RNTU JOURNAL" required>
                                        <div class="invalid-feedback" id="err_aim_and_scope_title_1"></div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Section Heading<span class="text-danger">*</span></label>
                                        <input type="text" class="content_show" id="aim_and_scope_title_2" name="aim_and_scope_title_2" placeholder="Aim and Scope" required>
                                        <div class="invalid-feedback" id="err_aim_and_scope_title_2"></div>
                                    </div>

                                    <div class="partitions_inner mar_part">
                                        <label>Scope Sub-Heading<span class="text-danger">*</span></label>
                                        <input type="text" class="content_show" id="aim_and_scope_title_3" name="aim_and_scope_title_3" placeholder="Scope of Publication" required>
                                        <div class="invalid-feedback" id="err_aim_and_scope_title_3"></div>
                                    </div>

                                </div>
                            </div>


                            <div class="content_inner">
                                <label>Aim & Scope Description<span class="text-danger">*</span></label>
                                <div id="ck_aim_and_scope_description" class="ckeditor-wrapper"></div>
                                <textarea id="aim_and_scope_description" name="aim_and_scope_description" class="content_show d-none"></textarea>
                                <div class="error-red" id="err_aim_and_scope_description"></div>
                            </div>

                            <div class="content_inner">
                                <label>Scope of Publication Description<span class="text-danger">*</span></label>
                                <div id="ck_scope_of_publication_description" class="ckeditor-wrapper"></div>
                                <textarea id="scope_of_publication_description" name="scope_of_publication_description" class="content_show d-none"></textarea>
                                <div class="error-red" id="err_scope_of_publication_description"></div>
                            </div>


                            <div class="content_inner">
                                <label>University Highlight Quote</label>
                                <div id="ck_university_highlight_quote" class="ckeditor-wrapper ckeditor-wrapper--short"></div>
                                <textarea id="university_highlight_quote" name="university_highlight_quote" class="content_show d-none"></textarea>
                            </div>




                            <div class="content_inner">

                                <label>Aim Section Image</label>
                                <input type="file" id="aim_section_image" name="aim_section_image" accept="image/jpg,image/jpeg,image/png,image/webp" class="content_show">
                                <div class="invalid-feedback" id="err_aim_section_image"></div>
                                <div id="currentImagePreview" class="mt-2 d-none">
                                    <small class="text-muted">Current image:</small><br>
                                    <img id="currentImageThumb" src="" style="width:15%;border-radius:6px;border:1px solid #dee2e6;object-fit:cover;">
                                </div>

                            </div>

                        </div>
                    </div>
            </div>

            <!-- Why RNTU Stats -->

            <div class="inner_fp mt-4">

                <div class="ssid">Why RNTU Stats</div>

                <div class="content_container">
                    <div class="content_inner">
                        <div class="content_partitions">

                            <div class="partitions_inner">
                                <label>Stats Section Heading</label>
                                <input type="text" class="content_show" id="why_rntu_title_1" name="why_rntu_title_1" placeholder="Why Choose RNTU Journals?" required>
                                <div class="invalid-feedback" id="err_why_rntu_title_1"></div>
                            </div>


                            <div class="partitions_inner">
                                <label>Stats Sub-Heading</label>
                                <input type="text" class="content_show" id="why_rntu_title_2" name="why_rntu_title_2" placeholder="Trusted by researchers worldwide">
                            </div>

                        </div>

                    </div>

                    <div class="content_inner">


                        @foreach([
                        ['why_rntu_years','why_rntu_years_label','Years'],
                        ['why_rntu_articles','why_rntu_articles_label','Articles'],
                        ['why_rntu_journals','why_rntu_journals_label','Journals'],
                        ['why_rntu_readers','why_rntu_readers_label','Readers'],
                        ['why_rntu_access','why_rntu_access_label','Access']
                        ] as [$val,$lbl,$name])


                        <label>{{ $name }}</label>
                        <input type="text" class="content_show" id="{{ $val }}" name="{{ $val }}" placeholder="{{ $name }} value" required>
                        <div class="invalid-feedback" id="err_{{ $val }}"></div>
                        <input type="text" class="content_show" id="{{ $lbl }}" name="{{ $lbl }}" placeholder="{{ $name }} label" required>
                        <div class="invalid-feedback" id="err_{{ $lbl }}"></div>
                        @endforeach

                    </div>
                </div>
            </div>

            <!-- Support Section -->
            <div class="inner_fp mt-4">

                <div class="ssid">Support Section</div>

                <div class="content_container">

                    <div class="content_inner">

                        <div class="content_partitions">

                            <div class="partitions_inner">
                                <label>Section Heading <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" id="support_section_heading" name="support_section_heading" required>
                                <div class="invalid-feedback" id="err_support_section_heading"></div>
                            </div>

                            <div class="partitions_inner">
                                <label>Articles Count <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" id="support_articles_count" name="support_articles_count" required>
                                <div class="invalid-feedback" id="err_support_articles_count"></div>
                            </div>

                            <div class="partitions_inner">
                                <label>Short Heading <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" id="support_short_heading" name="support_short_heading" required>
                                <div class="invalid-feedback" id="err_support_short_heading"></div>
                            </div>

                        </div>

                    </div>

                    <div class="content_inner">
                        <label>Section Description <span class="text-danger">*</span></label>
                        <div id="ck_support_section_description" class="ckeditor-wrapper ckeditor-wrapper--short"></div>
                        <textarea id="support_section_description" name="support_section_description" rows="2" class="content_show d-none"></textarea>
                        <div class="error-red" id="err_support_section_description"></div>
                    </div>

                </div>

            </div>


            <!-- Latest Journal -->
            <div class="inner_fp mt-4">

                <div class="ssid">Latest Journal</div>

                <div class="content_container">

                    <div class="content_inner">

                        <div class="content_partitions">

                            <div class="partitions_inner">
                                <label>Badge / Eyebrow <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" id="latest_journal_title" name="latest_journal_title" placeholder="LATEST ISSUES" required>
                                <div class="invalid-feedback" id="err_latest_journal_title"></div>
                            </div>

                            <div class="partitions_inner">
                                <label>Section Heading <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" id="latest_journal_heading" name="latest_journal_heading" placeholder="Latest Journal Issues" required>
                                <div class="invalid-feedback" id="err_latest_journal_heading"></div>
                            </div>

                        </div>

                    </div>

                    <div class="content_inner">

                        <label>Section Description <span class="text-danger">*</span></label>
                        <div id="ck_latest_journal_description" class="ckeditor-wrapper ckeditor-wrapper--short"></div>
                        <textarea id="latest_journal_description" name="latest_journal_description" rows="2" class="content_show d-none"></textarea>
                        <div class="error-red" id="err_latest_journal_description"></div>

                    </div>

                </div>

            </div>


            <!-- Footer Section -->
            <div class="inner_fp mt-4">

                <div class="ssid">Footer Section</div>

                <div class="content_container">

                    <div class="content_inner">

                        <label>Footer Description <span class="text-danger">*</span></label>
                        <div id="ck_footer_about_description" class="ckeditor-wrapper ckeditor-wrapper--short"></div>
                        <textarea id="footer_about_description" name="footer_about_description" rows="2" class="content_show d-none"></textarea>
                        <div class="error-red" id="err_footer_about_description"></div>

                    </div>

                </div>

            </div>

            <!-- Button -->
            <section class="term_con">

                <div class="button_d">
                    <button type="submit" class="green_d" id="hbcSaveBtn">
                        <span id="hbcSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>

                        <span id="hbcSaveBtnText">
                            Save
                        </span>
                    </button>
                </div>

            </section>

            </form>

        </div>

    </div>

    </div>

</section>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.addEventListener('keydown', function(e) {
            if (e.code === 'Space') {
                console.log('SPACE seen. defaultPrevented so far:', e.defaultPrevented, 'target:', e.target.tagName, e.target.id || e.target.className);
            }
        }, true);

        const API_BASE = '/api/admin/home-content';
        const TOKEN = localStorage.getItem('jwt_token') || '';

        const authHeaders = () => ({
            'Accept': 'application/json',
            'Authorization': `Bearer ${TOKEN}`,
        });

        const CK_FIELDS = [{
                id: 'aim_and_scope_description',
                required: true
            },
            {
                id: 'scope_of_publication_description',
                required: true
            },
            {
                id: 'university_highlight_quote',
                required: false
            },
            {
                id: 'support_section_description',
                required: true
            },
            {
                id: 'latest_journal_description',
                required: true
            },
            {
                id: 'footer_about_description',
                required: true
            },
        ];

        const editors = {};
        let editorsReady = false;
        let pendingFill = null;
        let cachedRecord = null;
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
                            'PasteFromOfficeEnhanced', 'AIAssistant', 'MultiLevelList', 'CaseChange',
                            'RestrictedEditingMode', 'RestrictedEditingModeEditing', 'RestrictedEditingModeUI', 
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

        /*  Plain (non-CK) form fields */
        const PLAIN_FIELDS = [
            'aim_and_scope_title_1', 'aim_and_scope_title_2', 'aim_and_scope_title_3',
            'why_rntu_title_1', 'why_rntu_title_2',
            'why_rntu_years', 'why_rntu_years_label',
            'why_rntu_articles', 'why_rntu_articles_label',
            'why_rntu_journals', 'why_rntu_journals_label',
            'why_rntu_readers', 'why_rntu_readers_label',
            'why_rntu_access', 'why_rntu_access_label',
            'support_section_heading', 'support_articles_count', 'support_short_heading',
            'latest_journal_title', 'latest_journal_heading',
        ];

        /*  Toast */
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

        /* Error helpers */
        function clearErrors() {
            document.querySelectorAll('[id^="err_"]').forEach(el => {
                el.textContent = '';
            });
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.ckeditor-wrapper.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }

        function showErrors(errors) {
            Object.entries(errors).forEach(([field, msgs]) => {
                const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                const errEl = document.getElementById(`err_${field}`);
                if (errEl) errEl.textContent = msg;
                if (editors[field]) {
                    document.getElementById(`ck_${field}`)?.classList.add('is-invalid');
                } else {
                    document.getElementById(field)?.classList.add('is-invalid');
                }
            });
        }

        /*  Form helpers */
        function resetForm() {
            document.getElementById('hbcForm').reset();
            document.getElementById('hbcId').value = '';
            document.getElementById('hbcMethod').value = 'POST';
            CK_FIELDS.forEach(({
                id
            }) => {
                if (editors[id]) editors[id].setData('');
                const ta = document.getElementById(id);
                if (ta) ta.value = '';
            });
            document.getElementById('currentImagePreview').classList.add('d-none');
            document.getElementById('currentImageThumb').src = '';
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
            document.getElementById('hbcId').value = r.id;
            document.getElementById('hbcMethod').value = 'PUT';
            const imgUrl = r.aim_section_image_url || r.aim_section_image || null;
            if (imgUrl) {
                document.getElementById('currentImageThumb').src = imgUrl;
                document.getElementById('currentImagePreview').classList.remove('d-none');
            } else {
                document.getElementById('currentImagePreview').classList.add('d-none');
            }
        }

        function syncEditors() {
            CK_FIELDS.forEach(({
                id
            }) => {
                if (editors[id]) document.getElementById(id).value = editors[id].getData();
            });
        }

        /* Render helpers */
        function esc(s) {
            if (!s) return '';
            return String(s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function htmlOrDash(s) {
            return s || '<span style="color:#adb5bd;font-style:italic;font-size:12px;">—</span>';
        }

        function extractRecord(json) {
            const raw = json.data;
            if (!raw) return null;
            if (Array.isArray(raw)) return raw[0] ?? null;
            if (Array.isArray(raw.data)) return raw.data[0] ?? null;
            if (raw.id) return raw;
            return null;
        }

        /* Load */

        document.getElementById('hbcSaveBtn').addEventListener('click', async () => {
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

            const id = document.getElementById('hbcId').value;
            const method = document.getElementById('hbcMethod').value;
            const spinner = document.getElementById('hbcSaveSpinner');
            const btnText = document.getElementById('hbcSaveBtnText');

            spinner.classList.remove('d-none');
            btnText.textContent = method === 'PUT' ? 'Updating…' : 'Saving…';

            const formData = new FormData(document.getElementById('hbcForm'));
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
                        showToast('error', 'Validation failed', 'Please fix the highlighted fields.');
                    } else {
                        showToast('error', 'Error', json.message ?? 'Something went wrong.');
                    }
                    return;
                }

                showToast('success', method === 'PUT' ? 'Updated!' : 'Created!', json.message ?? '');
                loadRecord();

            } catch (err) {
                showToast('error', 'Request failed', err.message);
            } finally {
                spinner.classList.add('d-none');
                btnText.textContent = method === 'PUT' ? 'Update' : 'Save';
            }
        });

        async function loadRecord() {
            document.getElementById('hbcLoading').classList.remove('d-none');
            document.getElementById('hbcFormContainer').classList.add('d-none');
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
                    document.getElementById('hbcSaveBtnText').textContent = 'Update';
                } else {
                    resetForm();
                    document.getElementById('hbcSaveBtnText').textContent = 'Save';
                }

                document.getElementById('hbcLoading').classList.add('d-none');
                document.getElementById('hbcFormContainer').classList.remove('d-none');
            } catch (e) {
                document.getElementById('hbcLoading').classList.add('d-none');
                showToast('error', 'Load failed', e.message);
            }
        }

        loadRecord();
    });
</script>
@endsection