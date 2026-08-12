document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/submit-articles";
    const TOKEN = localStorage.getItem("jwt_token") || "";

    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    const id = document.getElementById("saShowPage").dataset.id;

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

    const fmtDateTime = (d) => {
        if (!d) return "—";

        const dt = new Date(d);

        return isNaN(dt)
            ? d
            : dt.toLocaleString("en-IN", {
                  day: "2-digit",
                  month: "short",
                  year: "numeric",
                  hour: "2-digit",
                  minute: "2-digit",
              });
    };

    const STAGE_LABELS = {
        submitted: "Submitted",
        editor_approved: "Editor Approved",
        with_reviewer: "With Reviewer",
        reviewer_approved: "Reviewer Approved",
        reviewer_correction: "Correction Needed",
        reviewer_rejected: "Reviewer Rejected",
        with_author: "With Author",
        with_author_payment: "Awaiting Payment",
        rejected: "Rejected",
        published: "Published",
    };

    const declLabels = {
        original: "This work is original and has not been published elsewhere.",
        not_under_review: "This manuscript is not under review elsewhere.",
        all_approved: "All co-authors have approved this submission.",
        ethical_approval: "Required ethical approvals have been obtained.",
        data_accurate:
            "All data presented is accurate to the best of my knowledge.",
    };

    function stageChip(stage) {
        if (!stage) return '<span class="text-muted">—</span>';

        return `<span class="${esc(stage)}">
            ${esc(STAGE_LABELS[stage] || stage)}
            </span>`;
    }

    function statusPill(status) {
        if (!status) return '<span class="text-muted">—</span>';

        return `
                ${esc(status.replace(/_/g, " "))}`;
    }

    function fileLink(url, label) {
        if (!url) return '<span class="text-muted">Not provided</span>';

        return `
                <a href="${url}" target="_blank">
                    ${label}
                </a>`;
    }

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

    let confirmResolver = null;

    function showConfirm({
        title,
        desc,
        okLabel = "Confirm",
        variant = "warn",
    }) {
        document.getElementById("saConfirmTitle").textContent = title;
        document.getElementById("saConfirmDesc").textContent = desc;

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


    function renderActions(r) {
        const buttons = [
            r.can_approve
                ? `<div class="button_d"> <button type="button" class="green_d" id="saApproveBtn"> Approve </button> </div>`
                : "",
            r.can_reject
                ? `<div class="button_d"> <button type="button" class="red_d" id="saRejectBtn"> Reject </button> </div>`
                : "",
            r.can_forward
                ? `<div class="button_d"><button type="button" id="saForwardBtn"> Send to Reviewer </button> </div>`
                : "",
        ]
            .filter(Boolean)
            .join("");

        const bar = document.getElementById("saActionsBar");
        if (!bar) return;
        bar.innerHTML = buttons;

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
                    body: JSON.stringify({ remarks }),
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

    async function load() {
        try {
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders(),
            });

            const json = await res.json();

            if (!res.ok || !json.status)
                throw new Error(json.message || "Failed to load submission");
            render(json.data);
        } catch (e) {
            document.getElementById("saShowBody").innerHTML =
                `<p class="text-danger">${esc(e.message)}</p>`;
        }
    }

    function render(r) {
        document.getElementById("saShowSubtitle").textContent =
            `#${r.id} · ${r.manuscript_title ?? ""}`;

        const html = (s) => (s ? esc(s) : '<span class="text-muted">—</span>');

        const keywords =
            (r.keywords || [])
                .map((k) => `<span class="sa-decl-chip">${esc(k)}</span>`)
                .join("") || html(null);

        const declRows = Object.keys(declLabels)
            .map((key) => {
                const checked = (r.declarations || []).includes(key)
                    ? "checked"
                    : "";
                return `
                <div class="sa-decl-row" style="display:flex;align-items:center;gap:10px;padding:10px 0;">
                    <input type="checkbox" disabled ${checked} style="width:18px;height:18px;">
                    <label style="margin:0;color:#1e5fbf;font-size:14px;">${esc(declLabels[key])}</label>
                </div>`;
            })
            .join("");

        const termsRow = `
                <div class="sa-decl-row" style="display:flex;align-items:center;gap:10px;padding:10px 0;">
                    <input type="checkbox" disabled ${r.terms_accepted ? "checked" : ""} style="width:18px;height:18px;">
                    <label style="margin:0;color:#1e5fbf;font-size:14px;">I accept the terms and instructions.</label>
                </div>`;

        const declarations = declRows + termsRow;

        const coAuthorsRows = (r.co_authors || []).length
            ? r.co_authors
                  .map(
                      (c) => `
                    <tr>
                        <td>${esc(c.name)}</td>
                        <td>${esc(c.email)}</td>
                        <td>${esc(c.affiliation)}</td>
                        <td>${esc(c.orcid_id || "—")}</td>
                    </tr>
                    `,
                  )
                  .join("")
            : `<tr>
                        <td colspan="4" class="text-muted text-center">
                        No co-authors added
                        </td>
                    </tr>`;

        const reviewersRows = (r.reviewers || []).length
            ? r.reviewers
                  .map(
                      (rv) => `
                    <tr>
                        <td>${esc(rv.name)}</td>
                        <td>${esc(rv.email)}</td>
                        <td>${esc(rv.institution)}</td>
                        <td>${esc(rv.area_of_expertise)}</td>
                    </tr>
                    `,
                  )
                  .join("")
            : `<tr>
                        <td colspan="4" class="text-muted text-center">
                        No reviewers added
                        </td>
                    </tr>`;

        const review = r.review || {};

        document.getElementById("saShowBody").innerHTML = `
            <form id="saShowForm" onsubmit="return false;">

                <div class="inner_fp">

                    <div class="ssid">Workflow Status</div>

                    <div class="content_container">

                        <div class="content_inner"> 
                        
                            <div class="content_partitions"> 
                                
                                <div class="partitions_inner">
                                    <label>Current Stage</label>
                                    <div class="content_show">${stageChip(review.current_stage)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Editor Status</label>
                                    <div class="content_show">${statusPill(review.editor_status)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Reviewer</label>
                                    <div class="content_show">${statusPill(review.reviewer_status)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Final Status</label>
                                   <div class="content_show">${statusPill(review.final_status)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Submitted On</label>
                                   <div class="content_show">${fmtDate(r.submission_date)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Created At</label>
                                    <div class="content_show">${fmtDateTime(r.created_at)}</div>
                                </div>

                            </div>
                    
                        </div>

                    </div>

                    </div>

                    <div class="inner_fp mt-4">

                        <div class="ssid">Author Details</div>

                        <div class="content_container">

                            <div class="content_inner"> 
                            
                                <div class="content_partitions"> 
                                    
                                    <div class="partitions_inner">
                                        <label>Name</label>
                                        <div class="content_show">${html(r.full_name)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Email</label>
                                        <div class="content_show">${html(r.email)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Mobile</label>
                                        <div class="content_show">${html(r.mobile_no)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Institute</label>
                                    <div class="content_show">${html(r.affiliating_institute)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Department</label>
                                    <div class="content_show">${html(r.department)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>ORCID</label>
                                        <div class="content_show">${html(r.orcid_id)}</div>
                                    </div>

                                </div>
                        
                            </div>

                        </div>

                        </div>


                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Manuscript &amp; Abstract</div>

                            <div class="content_container">

                                <div class="content_inner">
                                    <div class="heading_p">Manuscript Title</div>
                                    <div class="content_show">${html(r.manuscript_title)}</div>
                                </div>

                                <div class="content_inner">
                                    <div class="heading_p">Abstract</div>
                                    <div class="content_show">${html(r.abstract_summary)}</div>
                                </div>
                                
                                <div class="content_inner">

                                    <div class="heading_p">Keywords</div>
                                    
                                    <div class="content_partitions">
                                        
                                        <div class="partitions_inner">
                                            <div class="content_show">${keywords}</div>
                                        </div>
                                            
                                    </div>
                                            
                                </div>
                                            
                                <div class="content_inner">
                                    <div class="heading_p">References</div>
                                    <div class="content_show">${html(r.references)}</div>
                                </div>

                            </div>

                        </div>



                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Co Author Details</div>

                            <div class="content_container">

                                <div class="table-container" style="margin-top: 70px;">
                                <table class="status-table">
                                    <thead>
                                    <tr>
                                        <th>Name of Co Author</th>
                                        <th>Email Address</th>
                                        <th>Affiliating Institute</th>
                                        <th>ORCID ID</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        ${coAuthorsRows}
                                    </tbody>
                                </table>
                                </div>

                            </div>

                        </div>


                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Reviewer Details</div>

                            <div class="content_container">

                                <div class="table-container" style="margin-top: 70px;">
                                <table class="status-table">
                                    <thead>
                                    <tr>
                                        <th>Name of Reviewer</th>
                                        <th>Email Address</th>
                                        <th>Affiliating Institute</th>
                                        <th>Area of Expertise</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        ${reviewersRows}
                                    </tbody>
                                </table>
                                </div>

                            </div>

                        </div>


                        <div class="inner_fp mt-4">

                            <div class="ssid">Corresponding Author Signature</div>

                            <div class="content_container">

                                <div class="content_inner"> 
                                
                                    <div class="content_partitions"> 
                                        
                                        <div class="partitions_inner">
                                            <label>Name of Corresponding Author</label>
                                            <div class="content_show">${html(r.author_signature)}</div>
                                        </div>

                                        <div class="partitions_inner">
                                            <label>Signature</label>
                                            <div class="content_show" style="background:green;">${fileLink(r.signature_img_url, "Click View to Image")}</div>
                                        </div>

                                        <div class="partitions_inner">
                                            <label>Submission Date</label>
                                            <div class="content_show">${fmtDate(r.submission_date)}</div>
                                        </div>

                                        <div class="partitions_inner">
                                            <label>Terms Accepted</label>
                                            <div class="content_show">${r.terms_accepted ? "Yes" : "—"}</div>
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
                                    <div class="heading_p">Full Article Paper Doc</div>
                                    <div class="paper_dowmload">
                                        <div class="content_show">Download Full Article Paper Doc</div>
                                        <div class="button_d"><button style="color:#fff;"> ${fileLink(r.abstract_file_url, "Download Doc")} </button></div>
                                    </div>
                                </div>

                                <!-- Download Full Article Paper PDF-->
                                <div class="content_inner">
                                    <div class="heading_p">Full Article Paper PDF</div>
                                    <div class="paper_dowmload">
                                        <div class="content_show">Download Full Article Paper PDF</div>
                                        <div class="button_d"><button style="color:#fff;"> ${fileLink(r.signed_manuscript_pdf_url, "Download PDF")} </button></div>
                                    </div>
                                </div>

                            </div>

                        </div>


                        
                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Declarations</div>

                            <div class="content_container">
                                <div class="content_inner">
                                    ${declarations}
                                </div>

                            </div>

                        </div>

                        <!-- Workflow Actions -->
                        <section class="term_con">
                            <div id="saActionsBar"></div>
                            <div class="button_d"><button type="button"><a href="/admin/submit-articles">Back</button></div>
                        </section>

            </form>
                        `;

        renderActions(r);
    }

    load();
});