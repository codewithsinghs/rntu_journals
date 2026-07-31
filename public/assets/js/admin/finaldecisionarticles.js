document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/submit-articles";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });
    const id = document.getElementById("saFinalPage").dataset.id;

    function showToast(type, title, msg) {
        const el = document.getElementById("saToast");
        document.getElementById("saToastTitle").textContent = title;
        const msgEl = document.getElementById("saToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        document.getElementById("saToastIcon").innerHTML =
            type === "success"
                ? `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
                : `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        bootstrap.Toast.getOrCreateInstance(el, {
            delay: 4000,
            autohide: true,
        }).show();
    }

    async function loadReviewerRemarks() {
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
                    `${API_BASE}/${id}/editor-final-decision`,
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
                setTimeout(() => {
                    window.location.href = "/admin/submit-articles";
                }, 600);
            } catch (e) {
                showToast("error", "Submit failed", e.message);
            }
        });

    loadReviewerRemarks();
});
