document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/submit-articles";
    const JOURNALS_API = "/api/admin/journals?page=1&per_page=100";
    const ISSUES_API = "/api/admin/submit-articles-issues";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });
    const id = document.getElementById("saEditPage").dataset.id;
    let currentStage = null;
    let journals = [];
    let keywordTags = [];
    let coAuthors = [];
    let reviewersList = [];

    const saRejectModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saRejectModal"),
    );
    const saForwardModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saForwardModal"),
    );
    const saConfirmModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saConfirmModal"),
    );

    const esc = (s) =>
        (s ?? "")
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    const fmtDate = (d) => {
        if (!d) return "—";
        const dt = new Date(d);
        return isNaN(dt)
            ? d
            : dt.toLocaleDateString("en-IN", {
                  day: "2-digit",
                  month: "short",
                  year: "numeric",
              });
    };

    function showToast(type, title, msg) {
        const el = document.getElementById("saToast");
        document.getElementById("saToastTitle").textContent = title;
        const msgEl = document.getElementById("saToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        const iconEl = document.getElementById("saToastIcon");
        iconEl.className =
            type === "success"
                ? "bi bi-check-circle"
                : "bi bi-exclamation-circle";
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        bootstrap.Toast.getOrCreateInstance(el, {
            delay: 4000,
            autohide: true,
        }).show();
    }

    /* ── Generic styled confirm dialog (used for Approve) ──────────────── */
    const ICONS = {
        approve: `<i class="bi bi-check-lg"></i>`,
        warn: `<i class="bi bi-exclamation-triangle"></i>`,
    };

    let confirmResolver = null;

    function showConfirm({
        title,
        desc,
        okLabel = "Confirm",
        variant = "warn",
    }) {
        document.getElementById("saConfirmTitle").textContent = title;
        document.getElementById("saConfirmDesc").textContent = desc;

        const iconEl = document.getElementById("saConfirmIcon");
        const okBtn = document.getElementById("saConfirmOkBtn");

        okBtn.textContent = okLabel;
        okBtn.className =
            "btn text-white " +
            (variant === "approve" ? "btn-success" : "btn-danger");

        saConfirmModal.show();

        return new Promise((resolve) => {
            confirmResolver = resolve;
        });
    }

    document.getElementById("saConfirmOkBtn").addEventListener("click", () => {
        saConfirmModal.hide();
        if (confirmResolver) {
            confirmResolver(true);
            confirmResolver = null;
        }
    });
    document
        .getElementById("saConfirmCancelBtn")
        .addEventListener("click", () => {
            saConfirmModal.hide();
            if (confirmResolver) {
                confirmResolver(false);
                confirmResolver = null;
            }
        });
    document
        .getElementById("saConfirmModal")
        .addEventListener("hidden.bs.modal", () => {
            if (confirmResolver) {
                confirmResolver(false);
                confirmResolver = null;
            }
        });

    function fileLink(url, label) {
        if (!url)
            return '<span class="text-muted" style="font-size:12px;font-style:italic;">Not provided</span>';
        return `<a href="${url}" target="_blank" rel="noopener" class="sa-file-link">
            <i class="bi bi-file-earmark-arrow-down"></i>${label}</a>`;
    }

    const declLabels = {
        original: "Original Work",
        not_under_review: "Not Under Review Elsewhere",
        all_approved: "Approved by All Authors",
        ethical_approval: "Ethical Approval Obtained",
        data_accurate: "Data is Accurate",
    };

    async function loadJournals() {
        try {
            const res = await fetch(JOURNALS_API, {
                headers: authHeaders(),
            });
            const json = await res.json();
            journals = json.data?.data ?? [];
        } catch (e) {
            journals = [];
        }
    }

    // ── Editor-only Volume/Issue override list ─────────────────────
    async function loadIssuesForJournal(journalId, selectedId) {
        const select = document.getElementById("edit_issue_id");
        if (!select) return;

        if (!journalId) {
            select.innerHTML =
                '<option value="">Select a journal first</option>';
            return;
        }

        select.innerHTML = '<option value="">Loading…</option>';
        try {
            const res = await fetch(`${ISSUES_API}?journal_id=${journalId}`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Failed to load issues.");
            const list = json.data || [];
            select.innerHTML =
                '<option value="">Auto (use current issue)</option>' +
                list
                    .map(
                        (
                            i,
                        ) => `<option value="${i.id}" ${String(selectedId) === String(i.id) ? "selected" : ""}>
                    Vol ${esc(i.volume?.volume ?? "—")} (${esc(i.year)}) — Issue ${esc(i.issue)}${i.is_current ? " · Current" : ""}
                </option>`,
                    )
                    .join("");
        } catch (e) {
            select.innerHTML =
                '<option value="">Failed to load issues</option>';
        }
    }

    async function load() {
        try {
            await loadJournals();
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Failed to load submission.");
            currentStage = json.data.review?.current_stage || "submitted";
            keywordTags = Array.isArray(json.data.keywords)
                ? [...json.data.keywords]
                : [];
            coAuthors = (json.data.co_authors || []).map((c) => ({
                ...c,
            }));
            reviewersList = (json.data.reviewers || []).map((r) => ({
                ...r,
            }));
            render(json.data);
            renderActions(json.data);
        } catch (e) {
            document.getElementById("saEditBody").innerHTML =
                `<p class="text-danger mb-0">${esc(e.message)}</p>`;
        }
    }

    /* ── Workflow action buttons: Approve / Reject / Forward to Reviewer ──
     * Rendered only when the corresponding permission flag from the API
     * is true (see SubmitArticleController::attachPermissionFlags):
     *   can_approve, can_reject, can_forward.
     */
    function renderActions(r) {
        const buttons = [
            r.can_approve
                ? ` 
                    
                <section class="term_con">

                <div class="button_d"> <button type="button" class="green_d" id="saApproveBtn"> Approve </button> </div>`
                : "",
            r.can_reject
                ? `
                <div class="button_d"> <button type="button" class="red_d" id="saRejectBtn"> Reject </button> </div> `
                : "",
            r.can_forward
                ? `
                <div class="button_d"><button type="button" id="saForwardBtn"> Send to Reviewer </button> </div>  

                </section> `
                : "",
        ]
            .filter(Boolean)
            .join("");

        document.getElementById("saActionsBar").innerHTML = buttons;

        if (r.can_approve) {
            document
                .getElementById("saApproveBtn")
                .addEventListener("click", doApprove);
        }
        if (r.can_reject) {
            document
                .getElementById("saRejectBtn")
                .addEventListener("click", openReject);
        }
        if (r.can_forward) {
            document
                .getElementById("saForwardBtn")
                .addEventListener("click", openForward);
        }
    }

    /* ── Approve ─────────────────────────────────────────────────────── */
    async function doApprove() {
        const ok = await showConfirm({
            title: "Approve this submission?",
            desc: "This manuscript will move forward so it can be forwarded to a reviewer.",
            okLabel: "Approve",
            variant: "approve",
        });
        if (!ok) return;

        try {
            const res = await fetch(`${API_BASE}/${id}/approve`, {
                method: "POST",
                headers: {
                    ...authHeaders(),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({}),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Approve failed.");
            showToast("success", "Approved", json.message);
            load();
        } catch (e) {
            showToast("error", "Approve failed", e.message);
        }
    }

    /* ── Reject ──────────────────────────────────────────────────────── */
    function openReject() {
        document.getElementById("saRejectRemarks").value = "";
        saRejectModal.show();
    }

    document
        .getElementById("saRejectConfirmBtn")
        .addEventListener("click", async () => {
            const remarks = document.getElementById("saRejectRemarks").value;
            if (!remarks.trim()) {
                showToast("error", "Reject failed", "A reason is required.");
                return;
            }
            try {
                const res = await fetch(`${API_BASE}/${id}/reject`, {
                    method: "POST",
                    headers: {
                        ...authHeaders(),
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        remarks,
                    }),
                });
                const json = await res.json();
                if (!res.ok || !json.status)
                    throw new Error(json.message || "Reject failed.");
                showToast("success", "Rejected", json.message);
                saRejectModal.hide();
                load();
            } catch (e) {
                showToast("error", "Reject failed", e.message);
            }
        });

    /* ── Forward to Reviewer ─────────────────────────────────────────── */
    async function openForward() {
        document.getElementById("saForwardRemarks").value = "";
        const select = document.getElementById("saForwardReviewer");
        select.innerHTML = '<option value="">Loading…</option>';
        saForwardModal.show();

        try {
            const res = await fetch("/api/admin/reviewers", {
                headers: authHeaders(),
            });
            const json = await res.json();
            const list = json.data || [];
            select.innerHTML = list.length
                ? list
                      .map(
                          (u) =>
                              `<option value="${u.id}">${esc(u.name)} (${esc(u.email)})</option>`,
                      )
                      .join("")
                : '<option value="">No reviewers found</option>';
        } catch (e) {
            select.innerHTML =
                '<option value="">Failed to load reviewers</option>';
        }
    }

    document
        .getElementById("saForwardConfirmBtn")
        .addEventListener("click", async () => {
            const reviewerId =
                document.getElementById("saForwardReviewer").value;
            const remarks = document.getElementById("saForwardRemarks").value;
            if (!reviewerId) {
                showToast(
                    "error",
                    "Forward failed",
                    "Please select a person to forward to.",
                );
                return;
            }
            try {
                const res = await fetch(
                    `${API_BASE}/${id}/forward-to-reviewer`,
                    {
                        method: "POST",
                        headers: {
                            ...authHeaders(),
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            reviewer_id: reviewerId,
                            remarks,
                        }),
                    },
                );
                const json = await res.json();
                if (!res.ok || !json.status)
                    throw new Error(json.message || "Forward failed.");
                showToast("success", "Forwarded", json.message);
                saForwardModal.hide();
                load();
            } catch (e) {
                showToast("error", "Forward failed", e.message);
            }
        });

    // ── Keyword tag-input ─────────────────────
    function renderKeywordTags() {
        const wrap = document.getElementById("edit_keywords_wrap");
        if (!wrap) return;

        const chips = keywordTags
            .map(
                (kw, idx) => `
                <div class="content_show" data-idx="${idx}" style="position: relative;gap: 10px;">${esc(kw)} <button type="button" class="sa-tag-remove" data-remove="${idx}" style="position:absolute;right: 20px;">&times;</button></div>
        `,
            )
            .join("");

        wrap.innerHTML = `
            ${chips}
            <input type="text" class="content_show" id="edit_keywords_text" placeholder="Add Keyword" placeholder="${keywordTags.length ? "" : "Add keyword"}">
        `;

        wrap.querySelectorAll("[data-remove]").forEach((btn) => {
            btn.addEventListener("click", () => {
                keywordTags.splice(parseInt(btn.dataset.remove, 10), 1);
                renderKeywordTags();
            });
        });

        const textInput = document.getElementById("edit_keywords_text");
        textInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === ",") {
                e.preventDefault();
                const val = textInput.value.trim().replace(/,$/, "");
                if (val && !keywordTags.includes(val)) {
                    keywordTags.push(val);
                    renderKeywordTags();
                    document.getElementById("edit_keywords_text").focus();
                } else {
                    textInput.value = "";
                }
            } else if (
                e.key === "Backspace" &&
                textInput.value === "" &&
                keywordTags.length
            ) {
                keywordTags.pop();
                renderKeywordTags();
            }
        });

        textInput.addEventListener("blur", () => {
            const val = textInput.value.trim();
            if (val && !keywordTags.includes(val)) {
                keywordTags.push(val);
                renderKeywordTags();
            }
        });

        wrap.addEventListener("click", () => {
            const ti = document.getElementById("edit_keywords_text");
            if (ti) ti.focus();
        });
    }

    // ── Co-authors dynamic rows ─────────────────────
    function renderCoAuthors() {
        const wrap = document.getElementById("edit_co_authors_wrap");
        if (!wrap) return;

        wrap.innerHTML =
            coAuthors
                .map(
                    (c, idx) => `
            <div class="sa-dyn-row" data-idx="${idx}" style="position:relative;">
                <button type="button" class="sa-dyn-remove" data-remove-coauthor="${idx}" title="Remove" style="width: 30px;height: 30px;background: red;border-radius: 50%;position: absolute;right: 0;"><i class="fa-solid fa-xmark" style="color:white;"></i></button>

                <div class="content_partitions">

                    <!-- Name of Co Author -->
                    <div class="partitions_inner">
                        <label>Name of Co Author</label>
                        <input type="text" class="content_show sa-coauthor-name" value="${esc(c.name)}">
                    </div>

                    <!-- Email Address -->
                    <div class="partitions_inner">
                        <label>Email Address</label>
                        <input type="email" class="content_show sa-coauthor-email" value="${esc(c.email)}">
                    </div>

                    <!-- Affiliating Institute -->
                    <div class="partitions_inner mar_part">
                        <label>Affiliating Institute</label>
                         <input type="text" class="content_show sa-coauthor-affiliation" value="${esc(c.affiliation)}">
                    </div>

                    <!-- ORCID ID -->
                    <div class="partitions_inner mar_part">
                        <label>ORCID ID</label>
                        <input type="text" class="content_show sa-coauthor-orcid" value="${esc(c.orcid_id)}">
                    </div>

                </div>

            </div>
        `,
                )
                .join("") || '<p class="mb-0">No co-authors added yet.</p>';

        wrap.querySelectorAll("[data-remove-coauthor]").forEach((btn) => {
            btn.addEventListener("click", () => {
                coAuthors.splice(parseInt(btn.dataset.removeCoauthor, 10), 1);
                renderCoAuthors();
            });
        });
    }

    // ── Reviewers dynamic rows ─────────────────────
    function renderReviewers() {
        const wrap = document.getElementById("edit_reviewers_wrap");
        if (!wrap) return;

        wrap.innerHTML =
            reviewersList
                .map(
                    (r, idx) => `


            <div class="sa-dyn-row" data-idx="${idx}" style="position:relative;">
                <button type="button" class="sa-dyn-remove" data-remove-reviewer="${idx}" title="Remove" style="width: 30px;height: 30px;background: red;border-radius: 50%;position: absolute;right: 0;"><i class="fa-solid fa-xmark" style="color:white;"></i></button>

                    <div class="content_partitions">

                        <!-- Name of Recommended Reviewer -->
                        <div class="partitions_inner">
                            <label>Name of Reviewer</label>
                            <input type="text" class="content_show sa-reviewer-name" value="${esc(r.name)}">
                        </div>

                        <!-- Email Address -->
                        <div class="partitions_inner">
                            <label>Email Address</label>
                            <input type="email" class="content_show sa-reviewer-email" value="${esc(r.email)}">
                        </div>

                        <!-- Affiliating Institute -->
                        <div class="partitions_inner mar_part">
                            <label>Affiliating Institute</label>
                            <input type="text" class="content_show sa-reviewer-institution" value="${esc(r.institution)}">
                        </div>

                        <!-- Area of Expertise -->
                        <div class="partitions_inner mar_part">
                            <label>Area of Expertise</label>
                            <input type="text" class="content_show sa-reviewer-expertise" value="${esc(r.area_of_expertise)}">
                        </div>

                    </div>

            </div>
        `,
                )
                .join("") || '<p class="mb-0">No reviewers added yet.</p>';

        wrap.querySelectorAll("[data-remove-reviewer]").forEach((btn) => {
            btn.addEventListener("click", () => {
                reviewersList.splice(
                    parseInt(btn.dataset.removeReviewer, 10),
                    1,
                );
                renderReviewers();
            });
        });
    }

    function render(r) {
        document.getElementById("saEditSubtitle").textContent = `#${r.id}`;
        const v = (s) => esc(s ?? "");

        const revisionBanner =
            currentStage === "with_author"
                ? `

                ${
                    r.review?.reviewer_remarks
                        ? `
                            <div class="sa-remarks-box reviewer mb-3">
                                <label>Reviewer's Remarks — Correction Needed</label>
                                <div>${v(r.review.reviewer_remarks)}</div>
                            </div>`
                        : ""
                }

                ${
                    r.review?.editor_remarks
                        ? `
                            <div class="sa-remarks-box editor mb-3">
                                <label>Editor's Note</label>
                                <div>${v(r.review.editor_remarks)}</div>
                            </div>`
                        : ""
                }

                `
                : "";

        const declChecks = Object.keys(declLabels)
            .map((key) => {
                const checked = (r.declarations || []).includes(key)
                    ? "checked"
                    : "";
                return `
                <div class="form-check form-check-inline">
                    <input class="form-check-input sa-decl-check" style="padding:10px;margin-right: 10px;" type="checkbox" value="${key}" id="decl_${key}" ${checked}>
                    <label class="form-check-label" style="font-size:13px;margin:0px;" for="decl_${key}">${declLabels[key]}</label>
                </div>`;
            })
            .join("");

        const journalOptions = journals
            .map(
                (j) =>
                    `<option value="${j.id}" ${r.journal_id == j.id ? "selected" : ""}>${esc(j.title)}</option>`,
            )
            .join("");

        // Editor-only Volume/Issue override section. Backend flag
        // `can_edit_issue` (from attachPermissionFlags) gates this — regular
        // authors never see or submit this field.
        const issueOverrideSection = r.can_edit_issue
            ? `

                <div class="inner_fp mt-4">
                    <div class="ssid">Volume &amp; Issue</div>
                        <div class="content_container">
                            <div class="content_inner">
                                <select class="content_show" id="edit_issue_id">
                                <option value="">Loading…</option>
                            </select>
                            <div class="form-text" style="font-size:11px;">
                                Leave as "Auto" to let approval/publish assign the current issue automatically, or pick one to override manually.
                            </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            `
            : "";

        document.getElementById("saEditBody").innerHTML = `
            ${revisionBanner}
            <form id="saEditForm">

                <div class="inner_fp">

                    <div class="ssid">Author &amp; Journal</div>

                    <div class="content_container">

                        <div class="content_inner"> 
                        
                            <div class="content_partitions"> 
                                
                                <div class="partitions_inner">
                                    <label>Journal</label>
                                    <select class="content_show" id="edit_journal_id">
                                        <option value="">Select journal...</option>
                                        ${journalOptions}
                                    </select>
                                </div>

                                <div class="partitions_inner">
                                    <label>Full Name</label>
                                    <input type="text" class="content_show" id="edit_full_name" value="${v(r.full_name)}">
                                </div>

                                <div class="partitions_inner">
                                    <label>Mobile</label>
                                    <input type="text" class="content_show" id="edit_mobile_no" value="${v(r.mobile_no)}">
                                </div>

                                <div class="partitions_inner">
                                    <label>Email</label>
                                    <input type="email" class="content_show" id="edit_email" value="${v(r.email)}">
                                </div>

                                <div class="partitions_inner">
                                    <label>ORCID ID</label>
                                    <input type="text" class="content_show" id="edit_orcid_id" value="${v(r.orcid_id)}">
                                </div>

                                <div class="partitions_inner">
                                    <label>Institute</label>
                                    <input type="text" class="content_show" id="edit_affiliating_institute" value="${v(r.affiliating_institute)}">
                                </div>

                                <div class="partitions_inner">
                                    <label>Department</label>
                                    <input type="text" class="content_show" id="edit_department" value="${v(r.department)}">
                                </div>

                                <div class="partitions_inner">
                                    <label>Institute Address</label>
                                    <input type="text" class="content_show" id="edit_affiliating_institute_address" value="${v(r.affiliating_institute_address)}">
                                </div>

                            </div>
                    
                        </div>

                    </div>

                </div>

                ${issueOverrideSection}

                <div class="inner_fp mt-4"> 

                    <div class="ssid">Manuscript &amp; Abstract</div>

                    <div class="content_container">

                        <div class="content_inner">
                            <div class="heading_p">Manuscript Title</div>
                            <input type="text" class="content_show" id="edit_manuscript_title" value="${v(r.manuscript_title)}">
                        </div>

                        <div class="content_inner">
                            <div class="heading_p">Abstract</div>
                            <textarea class="content_show" id="edit_abstract_summary" rows="6">${v(r.abstract_summary)}</textarea>
                        </div>
                        
                        <div class="content_inner">

                            <div class="heading_p">Keywords</div>
                            
                            <div class="content_partitions" id="edit_keywords_wrap">
                                
                            </div>
                                    
                        </div>
                                    
                        <div class="content_inner">
                            <div class="heading_p">References</div>
                            <textarea class="content_show" id="edit_references" rows="4" placeholder="Paste references here...">${v(r.references)}</textarea>
                        </div>

                    </div>

                </div>

                <div class="inner_fp mt-4">
                    <div class="ssid">Co-Authors</div>
                        <div class="content_container">
                            <div class="content_inner">
                                <div id="edit_co_authors_wrap" class="mb-3"></div>
                                <button type="button" class="edit-btn sa-dyn-add-btn" id="addCoAuthorBtn"> Add Co-Author</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inner_fp mt-4">
                    <div class="ssid">Reviewers</div>
                        <div class="content_container">
                            <div class="content_inner">
                                <div id="edit_reviewers_wrap" class="mb-3"></div>
                                <button type="button" class="edit-btn sa-dyn-add-btn" id="addReviewerBtn"> Add Reviewers</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inner_fp mt-4">

                    <div class="ssid">Corresponding Author</div>

                        <div class="content_container">

                            <div class="content_inner">

                                 <div class="content_partitions"> 

                                <!-- Name of Corresponding Author -->
                                <div class="partitions_inner">
                                    <label>Name of Corresponding Author</label>
                                    <input type="text" class="content_show" id="edit_author_signature" value="${v(r.author_signature)}">
                                </div>

                                <!-- Signature -->
                                <div class="partitions_inner">
                                    <label>Signature</label>
                                    <div class="d-flex justify-content-center align-items-center gap-5">
                                    
                                        <input type="file" class="content_show" id="edit_signature_file" accept=".jpg,.jpeg,.png">
                                        
                                        <div>
                                            ${
                                                r.signature_img_url
                                                    ? `<img src="${r.signature_img_url}" alt="Current signature" class="signature-preview" style="width: 60px;">`
                                                    : `<p>No signature image uploaded yet.</p>`
                                            }
                                        </div>
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="partitions_inner">
                                    <label>Date</label>
                                    <input type="text" class="content_show" value="${fmtDate(r.submission_date)}" disabled>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


                <div class="inner_fp mt-4"> 

                    <div class="ssid">Article Files</div>

                    <div class="content_container">

                        <!-- Download Full Article Paper Doc-->
                        <div class="content_inner">
                            <div class="heading_p">Download Full Article Paper Doc</div>
                            <div class="paper_dowmload">
                                <input type="file" class="content_show" id="edit_abstract_file" accept=".pdf,.doc,.docx">
                                <div class="button_d"><button> ${fileLink(r.abstract_file_url, "Download Doc")} </button></div>
                            </div>
                        </div>

                        <!-- Download Full Article Paper PDF-->
                        <div class="content_inner">
                            <div class="heading_p">Download Full Article Paper PDF</div>
                            <div class="paper_dowmload">
                                <input type="file" class="content_show" id="edit_signed_manuscript_pdf" accept=".pdf">
                                <div class="button_d"><button> ${fileLink(r.signed_manuscript_pdf_url, "Download PDF")} </button></div>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="inner_fp mt-4"> 
                    <div class="ssid">Declaration</div>
                    <div class="content_container"> 
                        <div class="content_inner">
                            ${declChecks}
                        </div>
                    </div>
                </div>


                <!-- Button -->
                <section class="term_con">
                    <div id="saActionsBar"></div>
                    <div class="button_d"><button type="button"><a href="/admin/submit-articles">Back</button></div>
                    <div class="button_d"><button type="submit" id="saEditSaveBtn">${currentStage === "with_author" ? "Save & Resubmit" : "Save Changes"}</button></div>
                </section>

            </form>
        `;

        renderKeywordTags();
        renderCoAuthors();
        renderReviewers();

        // Populate the editor-only Volume/Issue selector, scoped to the
        // submission's current journal, with its current issue pre-selected.
        if (r.can_edit_issue) {
            loadIssuesForJournal(r.journal_id, r.issue_id);

            document
                .getElementById("edit_journal_id")
                .addEventListener("change", (e) => {
                    // Switching journals invalidates any previously-selected issue,
                    // since issues are scoped to a single journal.
                    loadIssuesForJournal(e.target.value, null);
                });
        }

        document
            .getElementById("addCoAuthorBtn")
            .addEventListener("click", () => {
                coAuthors.push({
                    name: "",
                    email: "",
                    affiliation: "",
                    orcid_id: "",
                });
                renderCoAuthors();
            });

        document
            .getElementById("addReviewerBtn")
            .addEventListener("click", () => {
                reviewersList.push({
                    name: "",
                    email: "",
                    institution: "",
                    area_of_expertise: "",
                });
                renderReviewers();
            });

        document
            .getElementById("saEditForm")
            .addEventListener("submit", function (e) {
                e.preventDefault();
                save();
            });
    }

    function collectCoAuthorsFromDOM() {
        const rows = document.querySelectorAll(
            "#edit_co_authors_wrap .sa-dyn-row",
        );
        return Array.from(rows)
            .map((row) => ({
                name: row.querySelector(".sa-coauthor-name").value,
                email: row.querySelector(".sa-coauthor-email").value,
                affiliation: row.querySelector(".sa-coauthor-affiliation")
                    .value,
                orcid_id: row.querySelector(".sa-coauthor-orcid").value,
            }))
            .filter((c) => c.name.trim() || c.email.trim());
    }

    function collectReviewersFromDOM() {
        const rows = document.querySelectorAll(
            "#edit_reviewers_wrap .sa-dyn-row",
        );
        return Array.from(rows)
            .map((row) => ({
                name: row.querySelector(".sa-reviewer-name").value,
                email: row.querySelector(".sa-reviewer-email").value,
                institution: row.querySelector(".sa-reviewer-institution")
                    .value,
                area_of_expertise: row.querySelector(".sa-reviewer-expertise")
                    .value,
            }))
            .filter((r) => r.name.trim() || r.email.trim());
    }

    async function save() {
        const pendingInput = document.getElementById("edit_keywords_text");
        if (pendingInput && pendingInput.value.trim()) {
            const val = pendingInput.value.trim();
            if (!keywordTags.includes(val)) keywordTags.push(val);
        }

        const declarations = Array.from(
            document.querySelectorAll(".sa-decl-check:checked"),
        ).map((cb) => cb.value);
        const finalCoAuthors = collectCoAuthorsFromDOM();
        const finalReviewers = collectReviewersFromDOM();

        // Build multipart FormData since files may be attached
        const fd = new FormData();
        fd.append("_method", "PUT"); // Laravel method spoofing for file uploads

        fd.append(
            "journal_id",
            document.getElementById("edit_journal_id").value,
        );
        fd.append("full_name", document.getElementById("edit_full_name").value);
        fd.append("mobile_no", document.getElementById("edit_mobile_no").value);
        fd.append("email", document.getElementById("edit_email").value);
        fd.append("orcid_id", document.getElementById("edit_orcid_id").value);
        fd.append(
            "affiliating_institute",
            document.getElementById("edit_affiliating_institute").value,
        );
        fd.append(
            "department",
            document.getElementById("edit_department").value,
        );
        fd.append(
            "affiliating_institute_address",
            document.getElementById("edit_affiliating_institute_address").value,
        );
        fd.append(
            "manuscript_title",
            document.getElementById("edit_manuscript_title").value,
        );
        fd.append(
            "abstract_summary",
            document.getElementById("edit_abstract_summary").value,
        );
        fd.append(
            "references",
            document.getElementById("edit_references").value,
        );
        fd.append(
            "author_signature",
            document.getElementById("edit_author_signature").value,
        );

        // Editor-only Volume/Issue override. Only present in the DOM for
        // users with can_edit_issue, so this is a no-op for regular authors.
        // Empty string means "Auto" — the backend leaves auto-assignment to
        // approve()/publish() when this is blank.
        const issueSelect = document.getElementById("edit_issue_id");
        if (issueSelect) {
            fd.append("issue_id", issueSelect.value);
        }

        keywordTags.forEach((kw, i) => fd.append(`keywords[${i}]`, kw));
        declarations.forEach((d, i) => fd.append(`declarations[${i}]`, d));

        finalCoAuthors.forEach((c, i) => {
            fd.append(`co_authors[${i}][name]`, c.name);
            fd.append(`co_authors[${i}][email]`, c.email);
            fd.append(`co_authors[${i}][affiliation]`, c.affiliation);
            fd.append(`co_authors[${i}][orcid_id]`, c.orcid_id || "");
        });
        // Ensure the key exists even if the list is empty, so backend knows to sync/clear it
        if (finalCoAuthors.length === 0) fd.append("co_authors", "");

        finalReviewers.forEach((r, i) => {
            fd.append(`reviewers[${i}][name]`, r.name);
            fd.append(`reviewers[${i}][email]`, r.email);
            fd.append(`reviewers[${i}][institution]`, r.institution);
            fd.append(
                `reviewers[${i}][area_of_expertise]`,
                r.area_of_expertise,
            );
        });
        if (finalReviewers.length === 0) fd.append("reviewers", "");

        const manuscriptFile = document.getElementById(
            "edit_signed_manuscript_pdf",
        ).files[0];
        const abstractFile =
            document.getElementById("edit_abstract_file").files[0];
        const signatureFile = document.getElementById("edit_signature_file")
            .files[0];
        if (manuscriptFile) fd.append("signed_manuscript_pdf", manuscriptFile);
        if (abstractFile) fd.append("abstract_file", abstractFile);
        if (signatureFile) fd.append("signature_file", signatureFile);

        const saveBtn = document.getElementById("saEditSaveBtn");
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.textContent = "Saving…";

        try {
            // NOTE: method is POST (with _method=PUT spoofing above) — do NOT
            // set Content-Type manually; the browser sets the multipart
            // boundary automatically when the body is a FormData object.
            const res = await fetch(`${API_BASE}/${id}`, {
                method: "POST",
                headers: authHeaders(),
                body: fd,
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Update failed.");

            if (currentStage === "with_author") {
                const resubmitRes = await fetch(`${API_BASE}/${id}/resubmit`, {
                    method: "POST",
                    headers: {
                        ...authHeaders(),
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({}),
                });
                const resubmitJson = await resubmitRes.json();
                if (!resubmitRes.ok || !resubmitJson.status)
                    throw new Error(resubmitJson.message || "Resubmit failed.");
                showToast("success", "Resubmitted", resubmitJson.message);
            } else {
                showToast("success", "Updated", json.message);
            }

            setTimeout(() => {
                window.location.href = "/admin/submit-articles";
            }, 600);
        } catch (e) {
            showToast("error", "Save failed", e.message);
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    }

    load();
});
