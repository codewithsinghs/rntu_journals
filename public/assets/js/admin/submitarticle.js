document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/submit-articles";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    const truncate = (s, len = 15) => {
        if (!s) return "—";
        const str = s.toString();
        return str.length > len ? str.slice(0, len) + "…" : str;
    };

    let currentPage = 1;
    let currentForwardId = null;
    let currentFinalDecisionId = null;
    let currentRevisionId = null;
    let currentRejectId = null;
    let currentWaiverId = null;
    let searchTimer = null;

    let canViewReviewDates = false;

    const saForwardModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("saForwardModal"),
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

    /* type: success | warning | error */
    function showToast(type, title, msg) {
        const el = document.getElementById("saToast");
        document.getElementById("saToastTitle").textContent = title;
        const msgEl = document.getElementById("saToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";

        const icon =
            type === "success" ? "fa-check-circle" : "fa-exclamation-circle";
        document.getElementById("saToastIcon").innerHTML =
            `<i class="fa ${icon}" style="font-size:18px;"></i>`;

        el.classList.remove("bg-success", "bg-danger", "bg-warning");
        el.classList.add(
            type === "success"
                ? "bg-success"
                : type === "warning"
                  ? "bg-warning"
                  : "bg-danger",
        );
        bootstrap.Toast.getOrCreateInstance(el, {
            delay: 4000,
            autohide: true,
        }).show();
    }

    window.saShowToast = showToast;

    const esc = (s) =>
        (s ?? "")
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");

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

        const hiddenBadge = article.is_hidden
            ? ` <span class="sa-stage-chip is_hidden" title="Hidden from the author's own list">Hidden</span>`
            : "";

        const liveBadge =
            article.is_published && !article.is_hidden
                ? ` <span class="sa-stage-chip is_live" title="Visible on the public site">Live</span>`
                : "";

        return `<span class="sa-stage-chip ${esc(stage)}">${esc(label)}</span>${hiddenBadge}${liveBadge}`;
    }

    /* ── Generic styled confirm dialog (replaces window.confirm) ──── */
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

    function actionDropdown(r) {
        const items = [
            {
                action: "show",
                label: "View Article",
                show: true,
            },
            {
                action: "edit",
                label: "Edit",
                show: r.can_edit,
            },
            {
                action: "pay",
                label: "Pay Now",
                show: r.can_pay,
            },
            {
                action: "receipt",
                label: "View Receipt",
                show: !!r.latest_payment_order_id,
                extraAttrs: `data-order="${esc(r.latest_payment_order_id)}"`,
            },
            {
                // Editor-only: fee concession / full waiver for an approved article
                action: "waive",
                label: "Waiver",
                show: r.can_waive_approved,
            },
            {
                action: "forward",
                label: "Forward to Reviewer",
                show: r.can_forward,
            },
            {
                action: "forward-revision",
                label: "Send Back to Author",
                show: r.can_forward_to_author_revision,
            },
            {
                action: "publish",
                label: "Publish",
                show: r.can_publish,
            },
            {
                action: "toggle-hide",
                label: r.is_hidden ? "Unhide" : "Hide",
                show: r.can_hide,
                extraAttrs: `data-hidden="${r.is_hidden ? "1" : "0"}"`,
            },
            {
                action: "delete",
                label: "Delete",
                show: r.can_delete,
                danger: true,
            },
        ];

        const menuItems = items
            .filter((i) => i.show)
            .map(
                (i) => `
                    <li>
                        <a class="dropdown-item sa-dropdown-item${i.danger ? " text-danger" : ""}"
                           href="#" data-action="${i.action}" data-id="${esc(r.uuid)}" ${i.extraAttrs || ""}>
                            ${i.label}
                        </a>
                    </li>`,
            )
            .join("");

        return `
            <div class="dropdown sa-action-dropdown">
                <button class="btn btn-sm sa-action-toggle dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Action
                </button>
                <ul class="dropdown-menu dropdown-menu-end sa-dropdown-menu">
                    ${menuItems}
                </ul>
            </div>`;
    }

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
                    // Reviewer name + review date cells are only rendered when
                    // their <th> headers are visible, so columns stay aligned.
                    const reviewerCells = canViewReviewDates
                        ? `
                            <td>${r.reviewer_name ? esc(r.reviewer_name) : "—"}</td>
                            <td>${fmtDate(r.review?.forwarded_to_reviewer_date)}</td>
                            <td>${fmtDate(r.review?.reviewer_approval_date)}</td>`
                        : "";

                    // data-action="pay" -> opens the payment modal (handled below)
                    const payBtn = r.can_pay
                        ? `<a class="btn btn-success btn-sm me-1" href="#" data-action="pay" data-id="${esc(r.uuid)}">Pay Now</a>`
                        : "";

                    return `
                        <tr data-id="${esc(r.uuid)}" class="${r.is_hidden ? "sa-row-hidden" : ""}">
                            <td><strong>${esc(r.article_uid) || "—"}</strong></td>
                            <td>${esc(r.full_name)}</td>
                            <td>${esc(r.email)}</td>
                            <td>${r.journal ? `<span class="edit-btn">${esc(truncate(r.journal.title, 15))}</span>` : "—"}</td>
                            <td>${stageChip(r)}</td>
                            <td>${fmtDate(r.submission_date)}</td>
                            ${reviewerCells}
                            <td>
                                ${payBtn}
                                ${actionDropdown(r)}
                            </td>
                        </tr>
                    `;
                })
                .join("");

            document.querySelectorAll("#saTableBody tr").forEach((tr) => {
                tr.addEventListener("click", (e) => {
                    if (e.target.closest(".sa-action-dropdown")) return;
                    if (e.target.closest("a.btn")) return; // let Pay Now button work
                    goToShow(tr.dataset.id);
                });
            });

            // Dropdown menu item + Pay Now button clicks
            document
                .querySelectorAll("#saTableBody [data-action]")
                .forEach((btn) => {
                    btn.addEventListener("click", (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const id = btn.dataset.id;
                        const action = btn.dataset.action;
                        if (action === "show") goToShow(id);
                        if (action === "edit") goToEdit(id);
                        if (action === "pay") ArticlePayment.open(id);
                        if (action === "receipt")
                            ArticlePayment.showReceipt(btn.dataset.order);
                        if (action === "waive") openWaiver(id);
                        if (action === "approve") doApprove(id);
                        if (action === "reject") openReject(id);
                        if (action === "forward") openForward(id);
                        if (action === "final-decide") openFinalDecision(id);
                        if (action === "forward-revision")
                            openForwardRevision(id);
                        if (action === "publish") doPublish(id);
                        if (action === "toggle-hide")
                            doToggleHide(id, btn.dataset.hidden === "1");
                        if (action === "delete") doDelete(id);
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
            desc: "The author will be able to pay once this is approved.",
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

    /* ── Waiver (editor) for an already approved article ───────────
       Fee concession (author pays a reduced amount) or full fee waiver
       (published directly). Modal is built dynamically so no blade
       changes are required. ──────────────────────────────────────── */
    let saWaiverModal = null;

    function ensureWaiverModal() {
        if (document.getElementById("saWaiverModal")) {
            saWaiverModal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById("saWaiverModal"),
            );
            return;
        }

        const wrapper = document.createElement("div");
        wrapper.innerHTML = `
            <div class="modal fade" id="saWaiverModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom py-3">
                            
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="saWaiverType" id="saWaiverConcession" value="concession">
                                    <label class="form-check-label" for="saWaiverConcession">
                                        Fee Concession <span class="text-muted">(author pays a reduced amount)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="saWaiverType" id="saWaiverFull" value="full_waiver">
                                    <label class="form-check-label" for="saWaiverFull">
                                        Full Fee Waiver <span class="text-muted">(publish directly, no payment)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3" id="saWaiverAmountBox" style="display:none;">
                                <label class="form-label" for="saWaiverAmount" style="font-size:13px;font-weight:600;">
                                    Amount payable by the author (₹)
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" min="0.01" step="0.01" class="form-control" id="saWaiverAmount" placeholder="e.g. 1500">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" id="saWaiverReasonLabel" style="font-size:13px;font-weight:600;">Reason (required)</label>
                                <textarea class="form-control form-control-sm" id="saWaiverReason" rows="3" placeholder="Reason…"></textarea>
                            </div>

                            <p class="mb-0 text-muted" id="saWaiverHint" style="font-size:13px;">
                                Choose Fee Concession or Full Fee Waiver.
                            </p>
                        </div>
                        <div class="modal-footer border-top py-3">
                            <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success btn-sm px-4 text-white" id="saWaiverConfirmBtn">Apply Waiver</button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(wrapper.firstElementChild);

        saWaiverModal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("saWaiverModal"),
        );

        const refreshHint = () => {
            const type = document.querySelector(
                'input[name="saWaiverType"]:checked',
            )?.value;

            document.getElementById("saWaiverAmountBox").style.display =
                type === "concession" ? "block" : "none";

            document.getElementById("saWaiverReasonLabel").textContent =
                type === "full_waiver"
                    ? "Reason for full fee waiver (required)"
                    : type === "concession"
                      ? "Reason for fee concession (required)"
                      : "Reason (required)";

            document.getElementById("saWaiverHint").textContent =
                type === "full_waiver"
                    ? "The article will be published immediately. No payment will be requested."
                    : type === "concession"
                      ? "The author will see your reason and the amount they have to pay, with a Pay Now option."
                      : "Choose Fee Concession or Full Fee Waiver.";
        };

        document
            .querySelectorAll('input[name="saWaiverType"]')
            .forEach((r) => r.addEventListener("change", refreshHint));

        document
            .getElementById("saWaiverConfirmBtn")
            .addEventListener("click", submitWaiver);
    }

    function openWaiver(id) {
        currentWaiverId = id;
        ensureWaiverModal();
        document
            .querySelectorAll('input[name="saWaiverType"]')
            .forEach((r) => (r.checked = false));
        document.getElementById("saWaiverAmountBox").style.display = "none";
        document.getElementById("saWaiverAmount").value = "";
        document.getElementById("saWaiverReason").value = "";
        document.getElementById("saWaiverReasonLabel").textContent =
            "Reason (required)";
        document.getElementById("saWaiverHint").textContent =
            "Choose Fee Concession or Full Fee Waiver.";
        saWaiverModal.show();
    }

    async function submitWaiver() {
        const waiverType = document.querySelector(
            'input[name="saWaiverType"]:checked',
        )?.value;
        const amount = parseFloat(
            document.getElementById("saWaiverAmount").value,
        );
        const reason = document.getElementById("saWaiverReason").value.trim();

        if (!waiverType) {
            showToast(
                "error",
                "Waiver failed",
                "Choose Fee Concession or Full Fee Waiver.",
            );
            return;
        }
        if (waiverType === "concession" && !(amount > 0)) {
            showToast(
                "error",
                "Waiver failed",
                "Enter the amount payable by the author (greater than 0).",
            );
            return;
        }
        if (!reason) {
            showToast(
                "error",
                "Waiver failed",
                "A reason is required for the concession / waiver.",
            );
            return;
        }

        const payload = {
            waiver_type: waiverType,
            waiver_reason: reason,
            ...(waiverType === "concession" ? { waiver_amount: amount } : {}),
        };

        try {
            const res = await fetch(`${API_BASE}/${currentWaiverId}/waive`, {
                method: "POST",
                headers: {
                    ...authHeaders(),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Waiver failed.");
            showToast("success", "Waiver applied", json.message);
            saWaiverModal.hide();
            loadList(currentPage);
        } catch (e) {
            showToast("error", "Waiver failed", e.message);
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
            const currentUserId = window.APP_CONFIG?.CURRENT_USER_ID;
            const list = (json.data || []).filter(
                (u) => String(u.id) !== String(currentUserId),
            );

            select.innerHTML = list.length
                ? list
                      .map(
                          (u) =>
                              `<option value="${esc(u.id)}">${esc(u.name)} (${esc(u.email)})</option>`,
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
                    decision === "approve" ? "Approved" : "Rejected",
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

    /* ── Hide / Unhide — editor / admin / superadmin only ───────── */
    async function doToggleHide(id, isCurrentlyHidden) {
        const ok = await showConfirm({
            title: isCurrentlyHidden
                ? "Unhide this submission?"
                : "Hide this submission?",
            desc: isCurrentlyHidden
                ? "It will reappear in the author's submissions list, and — if it was published — become visible on the public site again."
                : "It will disappear from the author's submissions list and, if it was published, be pulled from the public site immediately. Editors and admins can still see and manage it here.",
            okLabel: isCurrentlyHidden ? "Unhide" : "Hide",
            variant: isCurrentlyHidden ? "approve" : "warn",
        });
        if (!ok) return;

        try {
            const res = await fetch(`${API_BASE}/${id}/toggle-hide`, {
                method: "POST",
                headers: {
                    ...authHeaders(),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({}),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Action failed.");
            showToast(
                "success",
                json.data?.is_hidden ? "Hidden" : "Unhidden",
                json.message,
            );
            loadList(currentPage);
        } catch (e) {
            showToast("error", "Action failed", e.message);
        }
    }

    /* ── Delete — editor / admin / superadmin only ──────────────── */
    async function doDelete(id) {
        const ok = await showConfirm({
            title: "Delete this submission?",
            desc: "This will remove the submission from the list. This action cannot be undone from here.",
            okLabel: "Delete",
            variant: "warn",
        });
        if (!ok) return;

        try {
            const res = await fetch(`${API_BASE}/${id}`, {
                method: "DELETE",
                headers: {
                    ...authHeaders(),
                    "Content-Type": "application/json",
                },
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Delete failed.");
            showToast("success", "Deleted", json.message);
            loadList(currentPage);
        } catch (e) {
            showToast("error", "Delete failed", e.message);
        }
    }

    loadList(1);
});