<div class="s__container_custom">

    <div class="rjf-header">
        <span class="guide-badge">SUBMISSION FORM</span>
        <h1 class="rjf-title">Manuscript Submission Information</h1>
    </div>

    {{-- Success Popup --}}
    <div id="rjf-success-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:40px 48px;text-align:center;max-width:420px;width:90%;box-shadow:0 8px 40px rgba(0,0,0,0.15);">
            <div style="font-size:52px;margin-bottom:12px;">✅</div>
            <h3 style="color:#1a3c6e;font-size:20px;font-weight:700;margin-bottom:10px;">Submitted Successfully!</h3>
            <p style="color:#555;font-size:14px;margin-bottom:24px;">Your manuscript has been submitted. Please login to continue to your dashboard.</p>
            <button id="rjf-success-ok-btn" style="background:#1a3c6e;color:#fff;border:none;border-radius:8px;padding:10px 32px;font-size:15px;font-weight:600;cursor:pointer;">OK</button>
        </div>
    </div>

    {{-- Error Alert --}}
    <div id="rjf-error-alert" style="display:none;background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:14px;"></div>

    <form id="submit-article-form" enctype="multipart/form-data">
        @csrf

        <!-- Author Information -->
        <div class="rjf-card">
            <div class="rjf-card-heading">
                Name of First Author/Head of Team to give presentation
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <label>Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="rjf-input" placeholder="e.g. Dr. Ramesh Kumar">
                    <small class="field-hint">Letters, spaces, dots, hyphens only</small>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label>Mobile No. <span class="text-danger">*</span></label>
                    <input type="text" name="mobile_no" class="rjf-input" placeholder="10-digit mobile number" maxlength="10">
                    <small class="field-hint">Indian mobile number (6/7/8/9 + 9 digits)</small>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label>Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="rjf-input" placeholder="example@domain.com">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label>Affiliating Institute <span class="text-danger">*</span></label>
                    <input type="text" name="affiliating_institute" class="rjf-input">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label>Department <span class="text-danger">*</span></label>
                    <input type="text" name="department" class="rjf-input">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label>ORCID ID</label>
                    <input type="text" name="orcid_id" class="rjf-input" placeholder="0000-0000-0000-0000" maxlength="19">
                    <small class="field-hint">Format: 0000-0000-0000-0000</small>
                </div>
                <div class="col-12">
                    <label>Affiliating Institute Address <span class="text-danger">*</span></label>
                    <textarea name="affiliating_institute_address" class="rjf-textarea"></textarea>
                </div>
            </div>
        </div>

        <!-- Co Authors -->
        <div class="rjf-card">
            <div class="rjf-card-heading">Name and Mobile Number of Co-Authors</div>
            <div id="author-container">
                <div class="row author-block">
                    <div class="col-lg-3 col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="co_authors[0][name]" class="rjf-input" placeholder="e.g. Dr. Priya Sharma">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label>Email Address</label>
                        <input type="email" name="co_authors[0][email]" class="rjf-input" placeholder="example@domain.com">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label>Affiliation</label>
                        <input type="text" name="co_authors[0][affiliation]" class="rjf-input">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label>ORCID ID</label>
                        <input type="text" name="co_authors[0][orcid_id]" class="rjf-input" placeholder="0000-0000-0000-0000">
                    </div>
                </div>
            </div>
            <div class="rjf-btn-right">
                <button type="button" class="rjf-btn" id="add-author-btn">Add Author +</button>
            </div>
        </div>

        <!-- Abstract -->
        <div class="rjf-card">
            <div class="rjf-card-heading">Abstract Details</div>
            <div class="row">
                <div class="col-12">
                    <label>Select Journal Type <span class="text-danger">*</span></label>
                    <select name="journal_id" class="rjf-input" id="journal-select">
                        <option value="">-- Loading Journals... --</option>
                    </select>
                </div>
                <div class="col-12">
                    <label>Manuscript Title <span class="text-danger">*</span></label>
                    <input type="text" name="manuscript_title" class="rjf-input" placeholder="Minimum 10 characters">
                </div>
                <div class="col-12">
                    <label>Abstract of Research / Project <span class="text-danger">*</span></label>
                    <textarea rows="8" name="abstract_summary" class="rjf-textarea" placeholder="Minimum 100 characters..."></textarea>
                    <small class="field-hint"><span id="abstract-count">0</span> characters (minimum 100)</small>
                </div>
                <div class="col-12">
                    <label>Keywords <span class="text-danger">*</span></label>
                    <div class="row">
                        @for($i = 0; $i < 8; $i++)
                            <div class="col-lg-3 col-md-6">
                            <input type="text" name="keywords[]" class="rjf-input" placeholder="Keyword {{ $i + 1 }}">
                    </div>
                    @endfor
                </div>
            </div>
            <div class="col-6">
                <label>Upload Manuscript PDF <span class="text-danger">*</span></label>
                <input type="file" name="signed_manuscript_pdf" accept=".pdf" class="rjf-input" id="manuscript-pdf" style="padding: 10px 15px;">
                <small class="field-hint">PDF only • Max 50MB</small>
                <small id="manuscript-pdf-error" style="color:#e53935;display:none;"></small>
            </div>
            <div class="col-6">
                <label>Upload Source File (DOCX) <span class="text-danger">*</span></label>
                <input type="file" name="abstract_file" accept=".pdf,.doc,.docx" class="rjf-input" id="abstract-file" style="padding: 10px 15px;">
                <small class="field-hint">PDF, DOC, DOCX only • Max 50MB</small>
                <small id="abstract-file-error" style="color:#e53935;display:none;"></small>
            </div>
        </div>
</div>

<!-- Reviewers -->
<div class="rjf-card">
    <div class="rjf-card-heading">Recommended Reviewers</div>
    <div id="reviewer-container">
        <div class="row reviewer-block">
            <div class="col-lg-3 col-md-6">
                <label>Full Name</label>
                <input type="text" name="reviewers[0][name]" class="rjf-input">
            </div>
            <div class="col-lg-3 col-md-6">
                <label>Email Address</label>
                <input type="email" name="reviewers[0][email]" class="rjf-input" placeholder="example@domain.com">
            </div>
            <div class="col-lg-3 col-md-6">
                <label>Institution</label>
                <input type="text" name="reviewers[0][institution]" class="rjf-input">
            </div>
            <div class="col-lg-3 col-md-6">
                <label>Area of Expertise</label>
                <input type="text" name="reviewers[0][area_of_expertise]" class="rjf-input">
            </div>
        </div>
    </div>
    <div class="rjf-btn-right">
        <button type="button" class="rjf-btn" id="add-reviewer-btn">Add Reviewer +</button>
    </div>
</div>

<!-- Declaration -->
<div class="rjf-card">
    <div class="rjf-card-heading">Author Declaration <span class="text-danger">*</span></div>
    <div class="rjf-checklist">
        <label><input type="checkbox" name="declarations[]" value="original"> The manuscript is original and has not been published previously.</label>
        <label><input type="checkbox" name="declarations[]" value="not_under_review"> The manuscript is not under consideration by another journal.</label>
        <label><input type="checkbox" name="declarations[]" value="all_approved"> All authors have approved the submitted manuscript.</label>
        <label><input type="checkbox" name="declarations[]" value="ethical_approval"> Ethical approval has been obtained where required.</label>
        <label><input type="checkbox" name="declarations[]" value="data_accurate"> Data presented are accurate and authentic.</label>
    </div>
</div>

<!-- Signature -->
<div class="rjf-card">
    <div class="rjf-card-heading">Corresponding Author Signature</div>
    <div class="row">
        <div class="col-lg-4">
            <label>Full Name <span class="text-danger">*</span></label>
            <input type="text" name="author_signature" class="rjf-input" placeholder="As appears on ID proof">
        </div>
        <div class="col-lg-4">
            <label>Signature (Image)</label>
            <input type="file" name="signature_file" accept="image/jpeg,image/jpg,image/png" class="rjf-input" id="signature-file" style="padding: 10px 15px;">
            <small class="field-hint">JPEG or PNG only • Max 2MB</small>
            <small id="signature-file-error" style="color:#e53935;display:none;"></small>
        </div>
        <div class="col-lg-4">
            <label>Date <span class="text-danger">*</span></label>
            <input type="date" name="submission_date" class="rjf-input" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
        </div>
    </div>
    <div class="rjf-submit-section">
        <label>
            <input type="checkbox" name="terms_accepted" value="1">
            I Have Read All Instructions of RNTU Journals. <span class="text-danger">*</span>
        </label>
        <button type="submit" class="rjf-submit-btn" id="submit-btn">Submit</button>
    </div>
</div>

</form>
</div>

<style>
    .field-hint {
        color: #888;
        font-size: 12px;
        display: block;
        margin-top: 3px;
    }

    .rjf-input.is-invalid {
        border-color: #e53935 !important;
    }

    .text-danger {
        color: #e53935;
    }
</style>

<script>
    (function() {

        const MAX_FILE_MB = 50;
        const MAX_FILE_BYTES = MAX_FILE_MB * 1024 * 1024;
        const MAX_SIG_MB = 2;
        const MAX_SIG_BYTES = MAX_SIG_MB * 1024 * 1024;

        const form = document.getElementById('submit-article-form'); // <-- scope root

        // ── Helpers ──────────────────────────────────────────────────────
        function showError(msg) {
            const el = document.getElementById('rjf-error-alert');
            el.innerHTML = msg;
            el.style.display = 'block';
            el.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function hideError() {
            document.getElementById('rjf-error-alert').style.display = 'none';
        }

        function fieldError(inputEl, msg) {
            inputEl.classList.add('is-invalid');
            const errEl = document.getElementById(inputEl.id + '-error');
            if (errEl) {
                errEl.textContent = msg;
                errEl.style.display = 'block';
            }
        }

        function clearFieldError(inputEl) {
            inputEl.classList.remove('is-invalid');
            const errEl = document.getElementById(inputEl.id + '-error');
            if (errEl) {
                errEl.textContent = '';
                errEl.style.display = 'none';
            }
        }

        // ── Formatters ───────────────────────────────────────────────────

        // Auto-format ORCID as user types: 0000-0000-0000-0000
        form.querySelectorAll('input[name="orcid_id"], input[name*="orcid_id"]').forEach(el => {
            el.addEventListener('input', function() {
                let val = this.value.replace(/[^0-9X]/gi, '').toUpperCase();
                if (val.length > 4) val = val.slice(0, 4) + '-' + val.slice(4);
                if (val.length > 9) val = val.slice(0, 9) + '-' + val.slice(9);
                if (val.length > 14) val = val.slice(0, 14) + '-' + val.slice(14);
                this.value = val.slice(0, 19);
            });
        });

        // Allow only digits in mobile field
        const mobileInput = form.querySelector('input[name="mobile_no"]');
        if (mobileInput) {
            mobileInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 10);
            });
        }

        // Abstract character counter
        const abstractTextarea = form.querySelector('textarea[name="abstract_summary"]');
        const abstractCount = document.getElementById('abstract-count');
        if (abstractTextarea && abstractCount) {
            abstractTextarea.addEventListener('input', function() {
                abstractCount.textContent = this.value.length;
                abstractCount.style.color = this.value.length < 100 ? '#e53935' : '#16a34a';
            });
        }

        // ── File size validation on change ───────────────────────────────
        function attachFileSizeCheck(inputId, maxBytes, maxLabel, allowedExts) {
            const el = document.getElementById(inputId); // ids are page-unique, fine as-is
            if (!el) return;
            el.addEventListener('change', function() {
                clearFieldError(this);
                if (!this.files.length) return;

                const file = this.files[0];
                const ext = file.name.split('.').pop().toLowerCase();

                if (allowedExts && !allowedExts.includes(ext)) {
                    fieldError(this, `Invalid file type. Allowed: ${allowedExts.join(', ').toUpperCase()}`);
                    this.value = '';
                    return;
                }
                if (file.size > maxBytes) {
                    fieldError(this, `File is too large (${(file.size/1024/1024).toFixed(1)}MB). Maximum allowed is ${maxLabel}.`);
                    this.value = '';
                    return;
                }
                if (file.size === 0) {
                    fieldError(this, 'File appears to be empty. Please choose a valid file.');
                    this.value = '';
                }
            });
        }

        attachFileSizeCheck('manuscript-pdf', MAX_FILE_BYTES, MAX_FILE_MB + 'MB', ['pdf']);
        attachFileSizeCheck('abstract-file', MAX_FILE_BYTES, MAX_FILE_MB + 'MB', ['pdf', 'doc', 'docx']);
        attachFileSizeCheck('signature-file', MAX_SIG_BYTES, MAX_SIG_MB + 'MB', ['jpeg', 'jpg', 'png']);

        // ── Client-side validation before submit ─────────────────────────
        // NOTE: every lookup below is scoped to `form`, not `document`,
        // so it can never accidentally read a same-named input from a
        // login popup or other widget elsewhere on the page.
        function validateForm() {
            const errors = [];

            const fullName = form.querySelector('input[name="full_name"]').value.trim();
            if (!fullName) {
                errors.push('Full name is required.');
            } else if (!/^[a-zA-Z\s.\-]+$/.test(fullName)) {
                errors.push('Full name must contain only letters, spaces, dots, or hyphens.');
            }

            const mobile = form.querySelector('input[name="mobile_no"]').value.trim();
            if (!mobile) {
                errors.push('Mobile number is required.');
            } else if (!/^[6-9]\d{9}$/.test(mobile)) {
                errors.push('Please enter a valid 10-digit Indian mobile number (starting with 6, 7, 8, or 9).');
            }

            const email = form.querySelector('input[name="email"]').value.trim();
            if (!email) {
                errors.push('Email address is required.');
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.push('Please enter a valid email address.');
            }

            const requiredFields = [{
                    name: 'affiliating_institute',
                    label: 'Affiliating Institute'
                },
                {
                    name: 'department',
                    label: 'Department'
                },
                {
                    name: 'affiliating_institute_address',
                    label: 'Institute Address'
                },
                {
                    name: 'manuscript_title',
                    label: 'Manuscript Title'
                },
                {
                    name: 'abstract_summary',
                    label: 'Abstract'
                },
                {
                    name: 'author_signature',
                    label: 'Author Signature Name'
                },
                {
                    name: 'submission_date',
                    label: 'Submission Date'
                },
            ];
            requiredFields.forEach(f => {
                const el = form.querySelector(`[name="${f.name}"]`);
                if (el && !el.value.trim()) errors.push(`${f.label} is required.`);
            });

            const title = form.querySelector('input[name="manuscript_title"]').value.trim();
            if (title && title.length < 10) errors.push('Manuscript title must be at least 10 characters.');

            const abstract = form.querySelector('textarea[name="abstract_summary"]').value.trim();
            if (abstract && abstract.length < 100) errors.push('Abstract must be at least 100 characters.');

            const journalId = form.querySelector('select[name="journal_id"]').value;
            if (!journalId) errors.push('Please select a journal.');

            const keywords = [...form.querySelectorAll('input[name="keywords[]"]')]
                .map(el => el.value.trim()).filter(Boolean);
            if (keywords.length === 0) errors.push('Please enter at least one keyword.');

            const manuscriptFile = document.getElementById('manuscript-pdf');
            if (!manuscriptFile || !manuscriptFile.files.length) {
                errors.push('Please upload the manuscript PDF.');
            } else if (manuscriptFile.files[0].size > MAX_FILE_BYTES) {
                errors.push(`Manuscript PDF must not exceed ${MAX_FILE_MB}MB.`);
            }

            const abstractFile = document.getElementById('abstract-file');
            if (!abstractFile || !abstractFile.files.length) {
                errors.push('Please upload the source file (DOCX/PDF).');
            } else if (abstractFile.files[0].size > MAX_FILE_BYTES) {
                errors.push(`Source file must not exceed ${MAX_FILE_MB}MB.`);
            }

            const sigFile = document.getElementById('signature-file');
            if (sigFile && sigFile.files.length && sigFile.files[0].size > MAX_SIG_BYTES) {
                errors.push(`Signature image must not exceed ${MAX_SIG_MB}MB.`);
            }

            const orcid = form.querySelector('input[name="orcid_id"]').value.trim();
            if (orcid && !/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/.test(orcid)) {
                errors.push('ORCID ID must be in format: 0000-0000-0000-0000.');
            }

            const declarations = form.querySelectorAll('input[name="declarations[]"]:checked');
            if (declarations.length === 0) errors.push('Please check at least one declaration.');

            const terms = form.querySelector('input[name="terms_accepted"]');
            if (!terms || !terms.checked) errors.push('You must accept the terms and instructions.');

            form.querySelectorAll('.author-block').forEach((block, i) => {
                const name = block.querySelector(`input[name="co_authors[${i}][name]"]`);
                const email = block.querySelector(`input[name="co_authors[${i}][email]"]`);
                if (name && name.value.trim() && email && email.value.trim()) {
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                        errors.push(`Co-author ${i + 1}: Please enter a valid email address.`);
                    }
                }
            });

            form.querySelectorAll('.reviewer-block').forEach((block, i) => {
                const name = block.querySelector(`input[name="reviewers[${i}][name]"]`);
                const email = block.querySelector(`input[name="reviewers[${i}][email]"]`);
                if (name && name.value.trim() && email && email.value.trim()) {
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                        errors.push(`Reviewer ${i + 1}: Please enter a valid email address.`);
                    }
                }
            });

            return errors;
        }

        // ── Load Journals ────────────────────────────────────────────────
        async function loadJournals() {
            const select = document.getElementById('journal-select');
            try {
                const res = await fetch('/api/submit-article/journals', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                const journals = json.data ?? [];
                select.innerHTML = '<option value="">-- Select Journal --</option>';
                journals.forEach(j => {
                    const opt = document.createElement('option');
                    opt.value = j.id;
                    opt.textContent = j.title;
                    select.appendChild(opt);
                });
            } catch (e) {
                select.innerHTML = '<option value="">-- Failed to load journals --</option>';
            }
        }
        loadJournals();

        // ── Add Author ───────────────────────────────────────────────────
        let authorCount = 1;
        document.getElementById('add-author-btn').addEventListener('click', function() {
            const i = authorCount++;
            const block = document.createElement('div');
            block.className = 'row author-block';
            block.innerHTML = `
                <div class="col-lg-3 col-md-6"><label>Full Name</label>
                    <input type="text" name="co_authors[${i}][name]" class="rjf-input" placeholder="e.g. Dr. Name"></div>
                <div class="col-lg-3 col-md-6"><label>Email Address</label>
                    <input type="email" name="co_authors[${i}][email]" class="rjf-input" placeholder="example@domain.com"></div>
                <div class="col-lg-3 col-md-6"><label>Affiliation</label>
                    <input type="text" name="co_authors[${i}][affiliation]" class="rjf-input"></div>
                <div class="col-lg-3 col-md-6"><label>ORCID ID</label>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <input type="text" name="co_authors[${i}][orcid_id]" class="rjf-input" placeholder="0000-0000-0000-0000" maxlength="19">
                        <button type="button" onclick="removeBlock(this,'author-block')" style="background:#fee2e2;border:none;color:#e53935;border-radius:6px;width:34px;height:38px;cursor:pointer;font-size:16px;flex-shrink:0;">✕</button>
                    </div></div>`;
            document.getElementById('author-container').appendChild(block);
        });

        // ── Add Reviewer ─────────────────────────────────────────────────
        let reviewerCount = 1;
        document.getElementById('add-reviewer-btn').addEventListener('click', function() {
            const i = reviewerCount++;
            const block = document.createElement('div');
            block.className = 'row reviewer-block';
            block.innerHTML = `
                <div class="col-lg-3 col-md-6"><label>Full Name</label>
                    <input type="text" name="reviewers[${i}][name]" class="rjf-input"></div>
                <div class="col-lg-3 col-md-6"><label>Email Address</label>
                    <input type="email" name="reviewers[${i}][email]" class="rjf-input" placeholder="example@domain.com"></div>
                <div class="col-lg-3 col-md-6"><label>Institution</label>
                    <input type="text" name="reviewers[${i}][institution]" class="rjf-input"></div>
                <div class="col-lg-3 col-md-6"><label>Area of Expertise</label>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <input type="text" name="reviewers[${i}][area_of_expertise]" class="rjf-input">
                        <button type="button" onclick="removeBlock(this,'reviewer-block')" style="background:#fee2e2;border:none;color:#e53935;border-radius:6px;width:34px;height:38px;cursor:pointer;font-size:16px;flex-shrink:0;">✕</button>
                    </div></div>`;
            document.getElementById('reviewer-container').appendChild(block);
        });

        // ── Remove Row ───────────────────────────────────────────────────
        window.removeBlock = function(btn, cls) {
            const block = btn.closest('.' + cls);
            if (block.parentElement.querySelectorAll('.' + cls).length > 1) block.remove();
        };

        // ── Submit — direct, no login required ───────────────────────────
// ── Submit — direct, no login required ───────────────────────────
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideError();

    const errors = validateForm();
    if (errors.length > 0) {
        showError(errors.join('<br>'));
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    try {
        const formData = new FormData(this);

        // ── Strip blank optional entries so backend never sees empty placeholders ──

        // 1. Keywords: only send the ones the user actually filled in
        formData.delete('keywords[]');
        [...form.querySelectorAll('input[name="keywords[]"]')]
            .map(el => el.value.trim())
            .filter(Boolean)
            .forEach(val => formData.append('keywords[]', val));

        // 2. Co-author rows: drop any row where every field is blank
        form.querySelectorAll('.author-block').forEach(block => {
            const inputs = block.querySelectorAll('input');
            const allBlank = [...inputs].every(inp => !inp.value.trim());
            if (allBlank) {
                inputs.forEach(inp => formData.delete(inp.name));
            }
        });

        // 3. Reviewer rows: drop any row where every field is blank
        form.querySelectorAll('.reviewer-block').forEach(block => {
            const inputs = block.querySelectorAll('input');
            const allBlank = [...inputs].every(inp => !inp.value.trim());
            if (allBlank) {
                inputs.forEach(inp => formData.delete(inp.name));
            }
        });

        const res = await fetch('/api/submit-article', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData,
        });
        const json = await res.json();

        if (res.ok && json.status) {
            document.getElementById('rjf-success-overlay').style.display = 'flex';
            this.reset();
            form.querySelector('input[name="submission_date"]').value = new Date().toISOString().split('T')[0];
            if (abstractCount) abstractCount.textContent = '0';

            document.getElementById('rjf-success-ok-btn').onclick = function() {
                document.getElementById('rjf-success-overlay').style.display = 'none';

                const loginPopup = document.getElementById('loginPopup');
                if (loginPopup) {
                    loginPopup.style.display = 'flex';
                } else {
                    window.location.href = '/login';
                }
            };
        } else {
            if (json.errors) {
                const msgs = Object.values(json.errors).flat().join('<br>');
                showError(msgs);
            } else {
                showError(json.message ?? 'Something went wrong. Please try again.');
            }
        }
    } catch (err) {
        showError('Network error. Please check your connection and try again.');
        console.error(err);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Submit';
    }
});
    })();
</script>