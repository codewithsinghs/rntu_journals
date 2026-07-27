document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/submit-articles";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    let currentPage = 1;
    let currentForwardId = null;
    let currentReviewDecisionId = null;
    let currentFinalDecisionId = null;
    let currentRevisionId = null;
    let currentRejectId = null;
    let searchTimer = null;

    let canViewReviewDates = false;

    const saForwardModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saForwardModal"),
    );
    const saReviewDecisionModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saReviewDecisionModal"),
    );
    const saFinalDecisionModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saFinalDecisionModal"),
    );
    const saForwardRevisionModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saForwardRevisionModal"),
    );
    const saRejectModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saRejectModal"),
    );
    const saConfirmModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saConfirmModal"),
    );

    function showToast(type, title, msg) {
        const el = document.getElementById("saToast");
        document.getElementById("saToastTitle").textContent = title;
        const msgEl = document.getElementById("saToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        document.getElementById("saToastIcon").innerHTML =
            type === "success"
                ? `<i class="fa fa-check-circle" style="font-size:18px;"></i>`
                : `<i class="fa fa-exclamation-circle" style="font-size:18px;"></i>`;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        bootstrap.Toast.getOrCreateInstance(el, {
            delay: 4000,
            autohide: true,
        }).show();
    }

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

    const STAGE_LABELS = {
        submitted: "Pending",
        editor_approved: "Approved",
        with_reviewer: "Under Verification",
        reviewer_approved: "Verified",
        reviewer_correction: "Correction Needed",
        reviewer_rejected: "Reject",
        with_author: "Pending",
        with_author_payment: "Awaiting Payment",
        rejected: "Reject",
        published: "Published",
    };

    function stageChip(article) {
        const stage = article.review?.current_stage || "submitted";
        const label = STAGE_LABELS[stage] || stage;
        const reviewerStages = [
            "with_reviewer",
            "reviewer_approved",
            "reviewer_correction",
            "reviewer_rejected",
        ];
        // const reviewerSuffix = (reviewerStages.includes(stage) && article.reviewer_name) ?
        //     ` <span class="sa-stage-reviewer">(${esc(article.reviewer_name)})</span>` :
        //     '';

        // return `<span class="sa-stage-chip ${esc(stage)}">${esc(label)}</span>${reviewerSuffix}`;
        return `<span class="sa-stage-chip ${esc(stage)}">${esc(label)}</span>`;
    }

    /* ── Generic styled confirm dialog (replaces window.confirm) ──── */
    const ICONS = {
        approve: `<i class="fa fa-check"></i>`,
        warn: `<i class="fa fa-exclamation-triangle"></i>`,
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

    async function loadList(page = 1) {
        currentPage = page;
        document.getElementById("saLoading").classList.remove("d-none");
        document.getElementById("saEmpty").classList.add("d-none");
        document.getElementById("saTableWrap").classList.add("d-none");

        const params = new URLSearchParams({
            page,
            per_page: 15,
        });
        const q = document.getElementById("saSearch").value.trim();
        if (q) params.set("q", q);

        try {
            const res = await fetch(`${API_BASE}?${params.toString()}`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            document.getElementById("saLoading").classList.add("d-none");

            canViewReviewDates = !!(json.meta && json.meta.show_review_dates);
            document
                .getElementById("saForwardedTh")
                .classList.toggle("d-none", !canViewReviewDates);

            document
                .getElementById("saReviewerNameTh")
                .classList.toggle("d-none", !canViewReviewDates);
            document
                .getElementById("saReviewerApprovedTh")
                .classList.toggle("d-none", !canViewReviewDates);

            const paginator = json.data;
            const rows = paginator?.data ?? [];

            if (!rows.length) {
                document.getElementById("saEmpty").classList.remove("d-none");
                return;
            }

            document.getElementById("saTableWrap").classList.remove("d-none");
            document.getElementById("saTableBody").innerHTML = rows
                .map((r) => {
                    const buttons = [
                        // Show — always available, redirects to the read-only detail page
                        `
                                                <button type="button" class="sa-action-btn show" title="View" data-action="show" data-id="${r.uuid}">
                                                    <i class="fa fa-eye"></i>
                                                </button>`,
                        // Edit — owner while submitted/with_author, or full access any time
                        r.can_edit
                            ? `
                                                <button type="button" class="sa-action-btn edit" title="Edit" data-action="edit" data-id="${r.uuid}">
                                                    <i class="fa fa-pencil-alt"></i>
                                                </button>`
                            : "",
                        // Approve — editor, stage "submitted"
                        r.can_approve
                            ? `
                                                <button type="button" class="sa-action-btn approve" title="Approve" data-action="approve" data-id="${r.uuid}">
                                                    <i class="fa fa-check"></i>
                                                </button>`
                            : "",
                        // Reject — editor, stage "submitted"
                        r.can_reject
                            ? `
                                                <button type="button" class="sa-action-btn reject" title="Reject" data-action="reject" data-id="${r.uuid}">
                                                    <i class="fa fa-times"></i>
                                                </button>`
                            : "",
                        // Forward to Reviewer — editor, stage "editor_approved"
                        r.can_forward
                            ? `
                                                <button type="button" class="sa-action-btn forward" title="Forward to Reviewer" data-action="forward" data-id="${r.uuid}">
                                                    <i class="fa fa-paper-plane"></i>
                                                </button>`
                            : "",
                        // Submit Review — reviewer's own 3-way decision, stage "with_reviewer"
                        r.can_review_decide
                            ? `
                                                <button type="button" class="sa-action-btn review-decide" title="Submit Review" data-action="review-decide" data-id="${r.uuid}">
                                                    <i class="fa fa-clipboard-check"></i>
                                                </button>`
                            : "",
                        // Editor Final Decision — stage "reviewer_approved" or "reviewer_rejected"
                        r.can_editor_final_decide
                            ? `
                                                <button type="button" class="sa-action-btn final-decide" title="Final Decision" data-action="final-decide" data-id="${r.uuid}">
                                                    <i class="fa fa-check-circle"></i>
                                                </button>`
                            : "",
                        // Forward to Author (Revision) — stage "reviewer_correction"
                        r.can_forward_to_author_revision
                            ? `
                                                <button type="button" class="sa-action-btn forward-author" title="Send Back to Author (Revision)" data-action="forward-revision" data-id="${r.uuid}">
                                                    <i class="fa fa-undo"></i>
                                                </button>`
                            : "",
                        // Publish — stage "with_author_payment"
                        r.can_publish
                            ? `
                                                <button type="button" class="sa-action-btn publish" title="Publish" data-action="publish" data-id="${r.uuid}">
                                                    <i class="fa fa-arrow-up"></i>
                                                </button>`
                            : "",
                    ]
                        .filter(Boolean)
                        .join("");

                    const reviewDateCells = canViewReviewDates
                        ? `
                                                <td>${fmtDate(r.review?.forwarded_to_reviewer_date)}</td>
                                                <td>${fmtDate(r.review?.reviewer_approval_date)}</td>`
                        : "";

                    return `
                                        <tr data-id="${r.uuid}">
                                            <td>${esc(r.full_name)}</td>
                                            <td>${esc(r.email)}</td>
                                            <td>${r.journal ? `<span class="edit-btn">${esc(r.journal.title)}</span>` : "—"}</td>
                                            <td>${esc(r.manuscript_title)}</td>
                                            <td>${stageChip(r)}</td>
                                            <td>${fmtDate(r.submission_date)}</td>
                                            <td>${r.reviewer_name ? esc(r.reviewer_name) : "—"}</td>
                                            ${reviewDateCells}
                                            <td>
                                                <div class="sa-actions">
                                                    ${buttons}
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                })
                .join("");

            // Row click also redirects to the detail page (ignored when clicking an action button)
            document.querySelectorAll("#saTableBody tr").forEach((tr) => {
                tr.addEventListener("click", (e) => {
                    if (e.target.closest("[data-action]")) return;
                    goToShow(tr.dataset.id);
                });
            });

            // Action buttons
            document
                .querySelectorAll("#saTableBody [data-action]")
                .forEach((btn) => {
                    btn.addEventListener("click", (e) => {
                        e.stopPropagation();
                        const id = btn.dataset.id;
                        const action = btn.dataset.action;
                        if (action === "show") goToShow(id);
                        if (action === "edit") goToEdit(id);
                        if (action === "approve") doApprove(id);
                        if (action === "reject") openReject(id);
                        if (action === "forward") openForward(id);
                        if (action === "review-decide") openReviewDecision(id);
                        if (action === "final-decide") openFinalDecision(id);
                        if (action === "forward-revision")
                            openForwardRevision(id);
                        if (action === "publish") doPublish(id);
                    });
                });

            document.getElementById("saPageInfo").textContent =
                `Showing ${paginator.from ?? 0}–${paginator.to ?? 0} of ${paginator.total ?? 0}`;
            document.getElementById("saPrevBtn").disabled =
                !paginator.prev_page_url;
            document.getElementById("saNextBtn").disabled =
                !paginator.next_page_url;
        } catch (e) {
            document.getElementById("saLoading").classList.add("d-none");
            showToast("error", "Load failed", e.message);
        }
    }

    document
        .getElementById("saPrevBtn")
        .addEventListener("click", () => loadList(currentPage - 1));
    document
        .getElementById("saNextBtn")
        .addEventListener("click", () => loadList(currentPage + 1));

    document.getElementById("saSearch").addEventListener("input", () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadList(1), 400);
    });

    function goToShow(id) {
        window.location.href = `/admin/submit-articles/${id}`;
    }

    function goToEdit(id) {
        window.location.href = `/admin/submit-articles/${id}/edit`;
    }

    /* ── Approve (editor, initial decision) ────────────────────── */
    async function doApprove(id) {
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
            loadList(currentPage);
        } catch (e) {
            showToast("error", "Approve failed", e.message);
        }
    }

    /* ── Reject (editor, initial decision) ─────────────────────── */
    function openReject(id) {
        currentRejectId = id;
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
                const res = await fetch(
                    `${API_BASE}/${currentRejectId}/reject`,
                    {
                        method: "POST",
                        headers: {
                            ...authHeaders(),
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            remarks,
                        }),
                    },
                );
                const json = await res.json();
                if (!res.ok || !json.status)
                    throw new Error(json.message || "Reject failed.");
                showToast("success", "Rejected", json.message);
                saRejectModal.hide();
                loadList(currentPage);
            } catch (e) {
                showToast("error", "Reject failed", e.message);
            }
        });

    /* ── Forward to Reviewer ────────────────────────────────────── */
    async function openForward(id) {
        currentForwardId = id;
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
                    `${API_BASE}/${currentForwardId}/forward-to-reviewer`,
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
                loadList(currentPage);
            } catch (e) {
                showToast("error", "Forward failed", e.message);
            }
        });

    /* ── Reviewer's own 3-way decision ─────────────────────────── */
    function openReviewDecision(id) {
        currentReviewDecisionId = id;
        document.getElementById("saReviewDecisionRemarks").value = "";
        document.getElementById("saReviewDecisionApproved").checked = true;
        saReviewDecisionModal.show();
    }

    document
        .getElementById("saReviewDecisionConfirmBtn")
        .addEventListener("click", async () => {
            const decision = document.querySelector(
                'input[name="saReviewDecision"]:checked',
            )?.value;
            const remarks = document.getElementById(
                "saReviewDecisionRemarks",
            ).value;

            if (!remarks.trim()) {
                showToast(
                    "error",
                    "Submit failed",
                    "Please add remarks for your decision.",
                );
                return;
            }

            try {
                const res = await fetch(
                    `${API_BASE}/${currentReviewDecisionId}/review-decision`,
                    {
                        method: "POST",
                        headers: {
                            ...authHeaders(),
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            decision,
                            remarks,
                        }),
                    },
                );
                const json = await res.json();
                if (!res.ok || !json.status)
                    throw new Error(
                        json.message || "Submitting review failed.",
                    );
                showToast("success", "Review submitted", json.message);
                saReviewDecisionModal.hide();
                loadList(currentPage);
            } catch (e) {
                showToast("error", "Submit failed", e.message);
            }
        });

    /* ── Editor's Final Decision (after reviewer approved/rejected) ── */
    async function openFinalDecision(id) {
        currentFinalDecisionId = id;
        document.getElementById("saFinalDecisionRemarks").value = "";
        document.getElementById("saFinalDecisionApprove").checked = true;
        document.getElementById("saFinalReviewerRemarks").textContent =
            "Loading…";
        saFinalDecisionModal.show();

        try {
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Failed to load submission.");
            document.getElementById("saFinalReviewerRemarks").textContent =
                json.data.review?.reviewer_remarks ||
                "No remarks provided by reviewer.";
        } catch (e) {
            document.getElementById("saFinalReviewerRemarks").textContent =
                "Failed to load reviewer remarks.";
        }
    }

    document
        .getElementById("saFinalDecisionConfirmBtn")
        .addEventListener("click", async () => {
            const decision = document.querySelector(
                'input[name="saFinalDecision"]:checked',
            )?.value;
            const remarks = document.getElementById(
                "saFinalDecisionRemarks",
            ).value;

            if (!remarks.trim()) {
                showToast(
                    "error",
                    "Submit failed",
                    "Please add remarks for the author.",
                );
                return;
            }

            try {
                const res = await fetch(
                    `${API_BASE}/${currentFinalDecisionId}/editor-final-decision`,
                    {
                        method: "POST",
                        headers: {
                            ...authHeaders(),
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            decision,
                            remarks,
                        }),
                    },
                );
                const json = await res.json();
                if (!res.ok || !json.status)
                    throw new Error(json.message || "Submit failed.");
                showToast(
                    "success",
                    decision === "approve"
                        ? "Author notified for payment"
                        : "Rejected",
                    json.message,
                );
                saFinalDecisionModal.hide();
                loadList(currentPage);
            } catch (e) {
                showToast("error", "Submit failed", e.message);
            }
        });

    /* ── Correction Needed -> Forward back to same Author ──────── */
    async function openForwardRevision(id) {
        currentRevisionId = id;
        document.getElementById("saRevisionReviewerRemarks").textContent =
            "Loading…";
        document.getElementById("saRevisionEditorRemarks").value = "";
        saForwardRevisionModal.show();

        try {
            const res = await fetch(`${API_BASE}/${id}`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Failed to load submission.");
            document.getElementById("saRevisionReviewerRemarks").textContent =
                json.data.review?.reviewer_remarks ||
                "No remarks provided by reviewer.";
        } catch (e) {
            document.getElementById("saRevisionReviewerRemarks").textContent =
                "Failed to load reviewer remarks.";
        }
    }

    document
        .getElementById("saForwardRevisionConfirmBtn")
        .addEventListener("click", async () => {
            const remarks = document.getElementById(
                "saRevisionEditorRemarks",
            ).value;
            if (!remarks.trim()) {
                showToast(
                    "error",
                    "Send failed",
                    "Please add a note for the author.",
                );
                return;
            }
            try {
                const res = await fetch(
                    `${API_BASE}/${currentRevisionId}/forward-to-author-revision`,
                    {
                        method: "POST",
                        headers: {
                            ...authHeaders(),
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            remarks,
                        }),
                    },
                );
                const json = await res.json();
                if (!res.ok || !json.status)
                    throw new Error(json.message || "Send failed.");
                showToast("success", "Sent to author", json.message);
                saForwardRevisionModal.hide();
                loadList(currentPage);
            } catch (e) {
                showToast("error", "Send failed", e.message);
            }
        });

    /* ── Publish ────────────────────────────────────────────────── */
    async function doPublish(id) {
        const ok = await showConfirm({
            title: "Publish this submission?",
            desc: "This marks the manuscript as published and completes the workflow.",
            okLabel: "Publish",
            variant: "approve",
        });
        if (!ok) return;

        try {
            const res = await fetch(`${API_BASE}/${id}/publish`, {
                method: "POST",
                headers: {
                    ...authHeaders(),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({}),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Publish failed.");
            showToast("success", "Published", json.message);
            loadList(currentPage);
        } catch (e) {
            showToast("error", "Publish failed", e.message);
        }
    }

    loadList(1);
});
