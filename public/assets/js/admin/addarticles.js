document.addEventListener("DOMContentLoaded", function () {
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    let keywords = [];
    let coAuthorCount = 0;
    let reviewerCount = 0;

    function showToast(msg, isError = true) {
        const el = document.getElementById("aaToast");
        document.getElementById("aaToastMsg").textContent = msg;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(isError ? "bg-danger" : "bg-success");
        bootstrap.Toast.getOrCreateInstance(el, {
            delay: 4000,
            autohide: true,
        }).show();
    }

    function clearErrors() {
        document
            .querySelectorAll(".validation-error-addarticle")
            .forEach((el) => {
                el.style.display = "none";
                el.textContent = "";
            });
    }

    function showErrors(errors) {
        clearErrors();
        Object.keys(errors).forEach((key) => {
            // Laravel returns keys like "co_authors.0.name" — map to a base field if no exact match
            let el = document.querySelector(`[data-error-for="${key}"]`);
            if (!el) {
                const base = key.split(".")[0];
                el = document.querySelector(`[data-error-for="${base}"]`);
            }
            if (el) {
                el.textContent = errors[key][0];
                el.style.display = "block";
            }
        });
    }

    // ── Date restriction: only present or past dates allowed ───────
    // NOTE: assumes the date field has id="submission_date". If your
    // blade template uses a different id, update the selector below.
    const dateInput = document.getElementById("submission_date");
    if (dateInput) {
        const todayStr = new Date().toISOString().split("T")[0]; // YYYY-MM-DD
        dateInput.max = todayStr; // blocks future dates in the native picker
    }

    // ── Load journals ─────────────────────────────────────────────
    fetch("/api/submit-article/journals")
        .then((res) => res.json())
        .then((json) => {
            const select = document.getElementById("journal_id");
            const list = json.data || [];
            select.innerHTML =
                '<option value="">Select a journal…</option>' +
                list
                    .map((j) => `<option value="${j.id}">${j.title}</option>`)
                    .join("");
        })
        .catch(() => {
            document.getElementById("journal_id").innerHTML =
                '<option value="">Failed to load journals</option>';
        });

    // ── Keywords ──────────────────────────────────────────────────
    const keywordInput = document.getElementById("keywordInput");
    const keywordTags = document.getElementById("keywordTags");

    function renderKeywords() {
        keywordTags.innerHTML = keywords
            .map(
                (k, i) => `
            <span class="content_show" style="position: relative;">
                ${k}
                <button type="button" data-index="${i}" style="position:absolute;right: 20px;">&times;</button>
            </span>
        `,
            )
            .join("");

        keywordTags.querySelectorAll("button").forEach((btn) => {
            btn.addEventListener("click", () => {
                keywords.splice(parseInt(btn.dataset.index), 1);
                renderKeywords();
            });
        });
    }

    keywordInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            const val = keywordInput.value.trim();
            if (val && keywords.length < 8 && !keywords.includes(val)) {
                keywords.push(val);
                renderKeywords();
            }
            keywordInput.value = "";
        }
    });

    // ── Dynamic Co-Authors ────────────────────────────────────────
    document.getElementById("addCoAuthorBtn").addEventListener("click", () => {
        if (coAuthorCount >= 10) {
            showToast("Maximum 10 co-authors allowed.");
            return;
        }
        const idx = coAuthorCount++;
        const wrap = document.getElementById("coAuthorsWrap");
        const block = document.createElement("div");
        block.className = "co-author-card";
        block.innerHTML = `
            <button type="button" class="co-author-card-remove" data-remove>&times;</button>

            <div class="content_partitions"> 

            <!-- Name of Co Author -->
            <div class="partitions_inner">
                <label>Name of Co Author</label>
                <input type="text" class="content_show" name="co_authors[${idx}][name]">
            </div>

            <!-- Email Address -->
            <div class="partitions_inner">
                <label>Email Address</label>
                <input type="email" class="content_show" name="co_authors[${idx}][email]">
            </div>

            <!-- Affiliating Institute -->
            <div class="partitions_inner mar_part">
                <label>Affiliating Institute</label>
                <input type="text" class="content_show" name="co_authors[${idx}][affiliation]">
            </div>

            <!-- ORCID ID -->
            <div class="partitions_inner mar_part">
                <label>ORCID ID</label>
                <input type="text" class="content_show" name="co_authors[${idx}][orcid_id]" placeholder="0000-0000-0000-0000">
            </div>

            </div>

        `;
        block
            .querySelector("[data-remove]")
            .addEventListener("click", () => block.remove());
        wrap.appendChild(block);
    });

    // ── Dynamic Reviewers ─────────────────────────────────────────
    document.getElementById("addReviewerBtn").addEventListener("click", () => {
        if (reviewerCount >= 5) {
            showToast("Maximum 5 reviewers allowed.");
            return;
        }
        const idx = reviewerCount++;
        const wrap = document.getElementById("reviewersWrap");
        const block = document.createElement("div");
        block.className = "co-author-card";
        block.innerHTML = `
            <button type="button" class="co-author-card-remove" data-remove>&times;</button>

            <div class="content_partitions">

                <!-- Name of Recommended Reviewer -->
                <div class="partitions_inner">
                    <label>Name of Reviewer</label>
                    <input type="text" class="content_show" name="reviewers[${idx}][name]">
                </div>

                <!-- Email Address -->
                <div class="partitions_inner">
                    <label>Email Address</label>
                    <input type="email" class="content_show" name="reviewers[${idx}][email]">
                </div>

                <!-- Affiliating Institute -->
                <div class="partitions_inner mar_part">
                    <label>Affiliating Institute</label>
                    <input type="text" class="content_show" name="reviewers[${idx}][institution]">
                </div>

                <!-- Area of Expertise -->
                <div class="partitions_inner mar_part">
                    <label>Area of Expertise</label>
                    <input type="text" class="content_show" name="reviewers[${idx}][area_of_expertise]">
                </div>

            </div>

        `;
        block
            .querySelector("[data-remove]")
            .addEventListener("click", () => block.remove());
        wrap.appendChild(block);
    });

    // ── Submit ────────────────────────────────────────────────────
    document.getElementById("aaForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        clearErrors();

        // Backstop check in case the native max is bypassed (DOM tampering,
        // browsers with weak date-input support, etc.)
        if (dateInput && dateInput.value) {
            const todayStr = new Date().toISOString().split("T")[0];
            if (dateInput.value > todayStr) {
                showToast("Date cannot be in the future.");
                dateInput.focus();
                return;
            }
        }

        const submitBtn = document.getElementById("aaSubmitBtn");
        submitBtn.disabled = true;
        submitBtn.textContent = "Submitting…";

        const form = document.getElementById("aaForm");
        const formData = new FormData(form);

        // Keywords aren't a native input — append manually
        keywords.forEach((k) => formData.append("keywords[]", k));

        // terms_accepted checkbox — FormData only includes it if checked;
        // the backend validation requires "accepted" so this is fine as-is.

        try {
            const res = await fetch("/api/submit-article", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
                body: formData,
            });
            const json = await res.json();

            if (res.status === 422) {
                showErrors(json.errors || {});
                showToast("Please fix the errors below and try again.");
                return;
            }

            if (!res.ok || !json.status) {
                throw new Error(json.message || "Submission failed.");
            }

            showToast("Article submitted successfully!", false);
            setTimeout(() => {
                window.location.href = "/admin/all-article-lists";
            }, 1200);
        } catch (err) {
            showToast(err.message || "Something went wrong.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Submit Article";
        }
    });
});