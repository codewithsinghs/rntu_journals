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

    const fmtMoney = (n) =>
        new Intl.NumberFormat("en-IN", {
            style: "currency",
            currency: "INR",
        }).format(Number(n) || 0);

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

    /* ── Reviewer decision modal (Approved / Correction Needed / Reject) ──
       Built dynamically so no blade changes are required. Remarks are
       mandatory for "Correction Needed" and "Reject", optional for
       "Approved". ─────────────────────────────────────────────────── */

    const REVIEW_DECISIONS = {
        approved: {
            label: "Approved",
            remarksRequired: false,
            okLabel: "Submit Approval",
            variant: "approve",
        },
        correction_needed: {
            label: "Correction Needed",
            remarksRequired: true,
            okLabel: "Send Correction Request",
            variant: "warn",
        },
        rejected: {
            label: "Reject",
            remarksRequired: true,
            okLabel: "Submit Rejection",
            variant: "warn",
        },
    };

    let currentReviewDecision = null;
    let saReviewDecisionModal = null;

    function ensureReviewDecisionModal() {
        if (document.getElementById("saReviewDecisionModal")) {
            saReviewDecisionModal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById("saReviewDecisionModal"),
            );
            return;
        }

        const wrapper = document.createElement("div");
        wrapper.innerHTML = `
            <div class="modal fade" id="saReviewDecisionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-semibold mb-0" id="saReviewDecisionTitle"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label" style="font-size:13px;font-weight:600;" id="saReviewDecisionRemarksLabel">
                                    Remarks
                                </label>
                                <textarea class="form-control form-control-sm" id="saReviewDecisionRemarks" rows="4"
                                    placeholder="Notes on your review…"></textarea>
                                <div id="saReviewDecisionRemarksHint" class="text-danger" style="font-size:12px;margin-top:4px;display:none;">
                                    Remarks are required for this decision.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3">
                            <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary btn-sm px-4" id="saReviewDecisionConfirmBtn">Submit</button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(wrapper.firstElementChild);

        saReviewDecisionModal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("saReviewDecisionModal"),
        );

        document
            .getElementById("saReviewDecisionConfirmBtn")
            .addEventListener("click", submitReviewDecision);
    }

    function openReviewDecision(decisionKey) {
        ensureReviewDecisionModal();

        const cfg = REVIEW_DECISIONS[decisionKey];
        if (!cfg) return;

        currentReviewDecision = decisionKey;

        document.getElementById("saReviewDecisionRemarksLabel").textContent =
            cfg.remarksRequired ? "Remarks (required)" : "Remarks (optional)";
        document.getElementById("saReviewDecisionRemarks").value = "";
        document.getElementById("saReviewDecisionRemarksHint").style.display =
            "none";

        const okBtn = document.getElementById("saReviewDecisionConfirmBtn");
        okBtn.textContent = cfg.okLabel;
        okBtn.className =
            "btn btn-sm px-4 text-white " +
            (cfg.variant === "approve" ? "btn-success" : "btn-danger");

        saReviewDecisionModal.show();
    }

    async function submitReviewDecision() {
        const cfg = REVIEW_DECISIONS[currentReviewDecision];
        if (!cfg) return;

        const remarks = document
            .getElementById("saReviewDecisionRemarks")
            .value.trim();

        if (cfg.remarksRequired && !remarks) {
            document.getElementById(
                "saReviewDecisionRemarksHint",
            ).style.display = "block";
            showToast(
                "error",
                "Submit failed",
                "Please add remarks for this decision.",
            );
            return;
        }

        try {
            const res = await fetch(`${API_BASE}/${id}/review-decision`, {
                method: "POST",
                headers: {
                    ...authHeaders(),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    decision: currentReviewDecision,
                    remarks,
                }),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Submitting review failed.");
            showToast("success", "Review submitted", json.message);
            saReviewDecisionModal.hide();
            load();
        } catch (e) {
            showToast("error", "Submit failed", e.message);
        }
    }

    /* ── Action buttons ──
       Only ONE Approve and ONE Reject button are ever rendered.
       - Reviewer (can_review_decide): Approve, Correction Needed, Reject
         -> all go through the review-decision modal.
       - Editor: Approve, Reject, Send to Reviewer
         -> Approve opens the payment-waiver modal when the editor has the
            "waive article payment" permission (r.can_waive).
       Correction Needed is reviewer-only. ─────────────────────────── */
    function renderActions(r) {
        const bar = document.getElementById("saActionsBar");
        if (!bar) return;

        // If the user is the assigned reviewer, the reviewer flow wins
        const isReviewer = !!r.can_review_decide;

        const showApprove = isReviewer || !!r.can_approve;
        const showReject = isReviewer || !!r.can_reject;
        const showCorrection = isReviewer; // reviewer only
        const showForward = !isReviewer && !!r.can_forward;
        const showPay = !isReviewer && !!r.can_pay; // author only

        bar.innerHTML = [
            showApprove
                ? `<div class="button_d"><button type="button" class="green_d" id="saApproveBtn">Approve</button></div>`
                : "",
            showCorrection
                ? `<div class="button_d"><button type="button" class="orange_d" id="saCorrectionBtn">Correction Needed</button></div>`
                : "",
            showReject
                ? `<div class="button_d"><button type="button" class="red_d" id="saRejectBtn">Reject</button></div>`
                : "",
            showForward
                ? `<div class="button_d"><button type="button" id="saForwardBtn">Send to Reviewer</button></div>`
                : "",
            showPay
                ? `<div class="button_d"><button type="button" class="green_d" id="saPayBtn">Pay Now</button></div>`
                : "",
        ]
            .filter(Boolean)
            .join("");

        if (showApprove) {
            document
                .getElementById("saApproveBtn")
                .addEventListener("click", () =>
                    isReviewer
                        ? openReviewDecision("approved")
                        : doApprove(!!r.can_waive),
                );
        }
        if (showCorrection) {
            document
                .getElementById("saCorrectionBtn")
                .addEventListener("click", () =>
                    openReviewDecision("correction_needed"),
                );
        }
        if (showReject) {
            document
                .getElementById("saRejectBtn")
                .addEventListener("click", () =>
                    isReviewer ? openReviewDecision("rejected") : openReject(),
                );
        }
        if (showForward) {
            document
                .getElementById("saForwardBtn")
                .addEventListener("click", openForward);
        }
        if (showPay) {
            document
                .getElementById("saPayBtn")
                .addEventListener("click", () => startPayment(r));
        }
    }

    /* ── Pay Now (author) ──
       If the editor granted a fee concession (amount > 0), the author first
       sees a modal stating exactly how much they have to pay. ─────────── */

    // TODO: point this at your existing payment page / route.
    const PAYMENT_URL = (uuid) => `/author/submit-articles/${uuid}/pay`;

    function showPayableAmountModal({ amount, reason }) {
        return new Promise((resolve) => {
            let modalEl = document.getElementById("saPayableModal");
            if (modalEl) modalEl.remove();

            const wrapper = document.createElement("div");
            wrapper.innerHTML = `
                <div class="modal fade" id="saPayableModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-bottom py-3">
                                <h5 class="modal-title fw-semibold mb-0">Fee Concession Applied</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 text-center">
                                <p class="mb-1 text-muted" style="font-size:14px;">You have to pay</p>
                                <div class="fw-bold mb-3" style="font-size:32px;">${fmtMoney(amount)}</div>
                                <p class="mb-0 text-muted" style="font-size:13px;">
                                    The editor has granted you a concession on the publication fee.
                                </p>
                                ${
                                    reason
                                        ? `<p class="mt-3 mb-0" style="font-size:13px;"><strong>Reason:</strong> ${esc(reason)}</p>`
                                        : ""
                                }
                            </div>
                            <div class="modal-footer border-top py-3">
                                <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-success btn-sm px-4 text-white" id="saPayableProceedBtn">Proceed to Payment</button>
                            </div>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(wrapper.firstElementChild);

            modalEl = document.getElementById("saPayableModal");
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

            let settled = false;
            const done = (val) => {
                if (settled) return;
                settled = true;
                resolve(val);
            };

            document
                .getElementById("saPayableProceedBtn")
                .addEventListener("click", () => {
                    done(true);
                    modal.hide();
                });
            modalEl.addEventListener("hidden.bs.modal", () => done(false));

            modal.show();
        });
    }

    async function startPayment(r) {
        const amount = Number(r.waiver?.payable_amount) || 0;

        // Concession with an amount > 0 → show the amount first
        if (r.is_waiver && r.waiver?.partial_payment && amount > 0) {
            const proceed = await showPayableAmountModal({
                amount,
                reason: r.waiver.reason,
            });
            if (!proceed) return;
        }

        window.location.href = PAYMENT_URL(id);
    }

    /* ── Approve (editor) with optional fee concession / full waiver ──
       Modal is built dynamically so no blade changes are required. ───── */
    let saApproveModal = null;

    function ensureApproveModal() {
        if (document.getElementById("saApproveModal")) {
            saApproveModal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById("saApproveModal"),
            );
            return;
        }

        const wrapper = document.createElement("div");
        wrapper.innerHTML = `
            <div class="modal fade" id="saApproveModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom py-3">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="saWaiverCheck">
                                <label class="form-check-label" for="saWaiverCheck" style="font-weight:600;">
                                    Grant fee concession / waiver
                                </label>
                            </div>

                            <div id="saWaiverBox" style="display:none;">
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
                                    <textarea class="form-control form-control-sm" id="saWaiverReason" rows="3"
                                        placeholder="Reason…"></textarea>
                                </div>
                            </div>

                            <p class="mb-0 text-muted" id="saApproveHint" style="font-size:13px;">
                                The author will be able to pay once this is approved.
                            </p>
                        </div>
                        <div class="modal-footer border-top py-3">
                            <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success btn-sm px-4 text-white" id="saApproveConfirmBtn">Approve</button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(wrapper.firstElementChild);

        saApproveModal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("saApproveModal"),
        );

        const check = document.getElementById("saWaiverCheck");
        const box = document.getElementById("saWaiverBox");
        const amountBox = document.getElementById("saWaiverAmountBox");
        const hint = document.getElementById("saApproveHint");

        const refreshHint = () => {
            const type = document.querySelector(
                'input[name="saWaiverType"]:checked',
            )?.value;

            amountBox.style.display = type === "concession" ? "block" : "none";

            document.getElementById("saWaiverReasonLabel").textContent =
                type === "full_waiver"
                    ? "Reason for full fee waiver (required)"
                    : type === "concession"
                      ? "Reason for fee concession (required)"
                      : "Reason (required)";

            hint.textContent = !check.checked
                ? "The author will be able to pay once this is approved."
                : type === "full_waiver"
                  ? "The article will be published immediately. No payment will be requested."
                  : type === "concession"
                    ? "The author will see your reason and the amount they have to pay, with a Pay Now option."
                    : "Choose Fee Concession or Full Fee Waiver.";
        };

        check.addEventListener("change", () => {
            box.style.display = check.checked ? "block" : "none";
            refreshHint();
        });

        document
            .querySelectorAll('input[name="saWaiverType"]')
            .forEach((r) => r.addEventListener("change", refreshHint));

        document
            .getElementById("saApproveConfirmBtn")
            .addEventListener("click", submitApprove);
    }

    function openApproveModal() {
        ensureApproveModal();
        document.getElementById("saWaiverCheck").checked = false;
        document.getElementById("saWaiverBox").style.display = "none";
        document.getElementById("saWaiverAmountBox").style.display = "none";
        document
            .querySelectorAll('input[name="saWaiverType"]')
            .forEach((r) => (r.checked = false));
        document.getElementById("saWaiverAmount").value = "";
        document.getElementById("saWaiverReason").value = "";
        document.getElementById("saWaiverReasonLabel").textContent =
            "Reason (required)";
        document.getElementById("saApproveHint").textContent =
            "The author will be able to pay once this is approved.";
        saApproveModal.show();
    }

    async function submitApprove() {
        const isWaiver = document.getElementById("saWaiverCheck").checked;
        const waiverType = document.querySelector(
            'input[name="saWaiverType"]:checked',
        )?.value;
        const amount = parseFloat(
            document.getElementById("saWaiverAmount").value,
        );
        const reason = document.getElementById("saWaiverReason").value.trim();

        if (isWaiver && !waiverType) {
            showToast(
                "error",
                "Approve failed",
                "Choose Fee Concession or Full Fee Waiver.",
            );
            return;
        }
        if (isWaiver && waiverType === "concession" && !(amount > 0)) {
            showToast(
                "error",
                "Approve failed",
                "Enter the amount payable by the author (greater than 0).",
            );
            return;
        }
        if (isWaiver && !reason) {
            showToast(
                "error",
                "Approve failed",
                "A reason is required for the concession / waiver.",
            );
            return;
        }

        const payload = isWaiver
            ? {
                  is_waiver: 1,
                  waiver_type: waiverType,
                  waiver_reason: reason,
                  ...(waiverType === "concession"
                      ? { waiver_amount: amount }
                      : {}),
              }
            : {};

        await sendApprove(payload, true);
    }

    async function sendApprove(payload, closeModal = false) {
        try {
            const res = await fetch(`${API_BASE}/${id}/approve`, {
                method: "POST",
                headers: {
                    ...authHeaders(),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok || !json.status)
                throw new Error(json.message || "Approve failed.");
            showToast("success", "Approved", json.message);
            if (closeModal && saApproveModal) saApproveModal.hide();
            load();
        } catch (e) {
            showToast("error", "Approve failed", e.message);
        }
    }

    async function doApprove(canWaive = false) {
        // Editors with the waive permission get the waiver modal
        if (canWaive) return openApproveModal();

        const ok = await showConfirm({
            title: "Approve this submission?",
            desc: "This manuscript will move forward so it can be forwarded to a reviewer.",
            okLabel: "Approve",
            variant: "approve",
        });
        if (!ok) return;

        await sendApprove({});
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

        // Fee concession / waiver info (visible to the author and the editor)
        const payable = Number(r.waiver?.payable_amount) || 0;
        const waiverBlock =
            r.is_waiver && r.waiver
                ? `
                                <div class="partitions_inner">
                                    <label>Fee Status</label>
                                    <div class="content_show">${r.waiver.full_payment ? "Full Fee Waiver" : "Fee Concession"}</div>
                                </div>

                                ${
                                    !r.waiver.full_payment && payable > 0
                                        ? `<div class="partitions_inner">
                                    <label>Amount Payable</label>
                                    <div class="content_show">${fmtMoney(payable)}</div>
                                </div>`
                                        : ""
                                }

                                <div class="partitions_inner">
                                    <label>${r.waiver.full_payment ? "Waiver Reason" : "Concession Reason"}</label>
                                    <div class="content_show">${html(r.waiver.reason)}</div>
                                </div>`
                : "";

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

                                ${waiverBlock}

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
                            <div class="button_d"><button type="button"><a href="/admin/submit-articles">Back</a></button></div>
                        </section>

            </form>
                        `;

        renderActions(r);
    }

    load();
});