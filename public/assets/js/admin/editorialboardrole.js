document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = window.APP_CONFIG.API_BASE;
    const JOURNALS_API = window.APP_CONFIG.JOURNALS_API;

    const authHeaders = () => ({
        Accept: "application/json",
    });

    async function apiFetch(url, options = {}) {
        return fetch(url, {
            ...options,
            credentials: "include",
            headers: {
                ...authHeaders(),
                ...(options.headers || {}),
            },
        });
    }

    const ebrModalEl = document.getElementById("ebrModal");
    const ebrDeleteModalEl = document.getElementById("ebrDeleteModal");
    let deleteTargetId = null;
    let cachedRoles = [];
    let cachedJournals = [];

    /* ── Toast (built in JS, no Blade markup required) ────────────── */
    function ensureToastEl() {
        let el = document.getElementById("ebrToast");
        if (el) return el;

        let container = document.getElementById("ebrToastContainer");
        if (!container) {
            container = document.createElement("div");
            container.id = "ebrToastContainer";
            container.className =
                "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
        }

        el = document.createElement("div");
        el.id = "ebrToast";
        el.className = "toast align-items-center border-0 text-white";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-start gap-2">
                    <span id="ebrToastIcon" class="flex-shrink-0"></span>
                    <div>
                        <div id="ebrToastTitle" class="fw-semibold"></div>
                        <div id="ebrToastMsg" class="small"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div style="height:3px;background:rgba(255,255,255,.25);">
                <div id="ebrToastBar" style="height:100%;background:rgba(255,255,255,.8);width:100%;"></div>
            </div>
        `;
        container.appendChild(el);
        return el;
    }

    // Build the toast element up front, before anything that might need it
    ensureToastEl();

    /* ── Toast ──────────────────────────────────────────────────── */
    function showToast(type, title, msg) {
        const el = ensureToastEl();
        document.getElementById("ebrToastTitle").textContent = title;
        const msgEl = document.getElementById("ebrToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        document.getElementById("ebrToastIcon").innerHTML =
            type === "success"
                ? `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
                : `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        const bar = document.getElementById("ebrToastBar");
        bar.style.transition = "none";
        bar.style.width = "100%";
        requestAnimationFrame(() =>
            requestAnimationFrame(() => {
                bar.style.transition = "width 4s linear";
                bar.style.width = "0%";
            }),
        );
        bootstrap.Toast.getOrCreateInstance(el, {
            delay: 4000,
            autohide: true,
        }).show();
    }

    /* ── Errors ─────────────────────────────────────────────────── */
    function clearErrors() {
        document
            .querySelectorAll('[id^="err_ebr_"]')
            .forEach((el) => (el.textContent = ""));
        document
            .querySelectorAll(".is-invalid")
            .forEach((el) => el.classList.remove("is-invalid"));
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([field, msgs]) => {
            const msg = Array.isArray(msgs) ? msgs[0] : msgs;
            const err = document.getElementById(`err_ebr_${field}`);
            if (err) err.textContent = msg;
            document.getElementById(`ebr_${field}`)?.classList.add("is-invalid");
        });
    }

    /* ── Form helpers ───────────────────────────────────────────── */
    function resetForm() {
        document.getElementById("ebrForm").reset();
        document.getElementById("ebrId").value = "";
        document.getElementById("ebrMethod").value = "POST";
        document.getElementById("ebr_status").checked = true;
        document.getElementById("ebr_journal_id").value = "";
        document.getElementById("ebr_role").value = "";
        clearErrors();
    }

    function fillForm(r) {
        document.getElementById("ebr_journal_id").value = r.journal_id ?? "";
        document.getElementById("ebr_role").value = r.role ?? "";
        document.getElementById("ebr_status").checked = !!Number(r.status);
        document.getElementById("ebrId").value = r.id;
        document.getElementById("ebrMethod").value = "PUT";
    }

    /* ── Load journals for dropdowns ───────────────────────────── */
    async function loadJournals() {
        try {
            const res = await apiFetch(JOURNALS_API);
            const json = await res.json();
            // JournalsController::adminIndex() paginates, so the array
            // is nested at json.data.data (Laravel paginate() shape).
            cachedJournals = json.data?.data ?? [];

            const formSelect = document.getElementById("ebr_journal_id");
            const filterSelect = document.getElementById("journalFilter");

            cachedJournals.forEach((j) => {
                const opt1 = document.createElement("option");
                opt1.value = j.id;
                opt1.textContent = j.title;
                formSelect.appendChild(opt1);

                const opt2 = document.createElement("option");
                opt2.value = j.id;
                opt2.textContent = j.title;
                filterSelect.appendChild(opt2);
            });
        } catch (e) {
            console.error("Failed to load journals list:", e.message);
        }
    }

    document
        .getElementById("journalFilter")
        .addEventListener("change", () => loadRoles());

    /* ── Open Add modal ─────────────────────────────────────────── */
    document.getElementById("openEbrModal").addEventListener("click", () => {
        resetForm();
        document.getElementById("ebrModalTitle").textContent = "Add Role";
        document.getElementById("ebrSaveBtnText").textContent = "Save";
        bootstrap.Modal.getOrCreateInstance(ebrModalEl).show();
    });

    /* ── Save (form submit, not button click) ──────────────────── */
    document
        .getElementById("ebrForm")
        .addEventListener("submit", async (e) => {
            e.preventDefault();
            clearErrors();

            const id = document.getElementById("ebrId").value;
            const method = document.getElementById("ebrMethod").value;
            const spinner = document.getElementById("ebrSaveSpinner");
            const btnText = document.getElementById("ebrSaveBtnText");

            spinner.classList.remove("d-none");
            btnText.textContent = method === "PUT" ? "Updating…" : "Saving…";

            const payload = {
                journal_id: document.getElementById("ebr_journal_id").value || null,
                role: document.getElementById("ebr_role").value.trim(),
                status: document.getElementById("ebr_status").checked ? 1 : 0,
            };

            const url = method === "PUT" ? `${API_BASE}/${id}` : API_BASE;

            try {
                const res = await apiFetch(url, {
                    method,
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();

                if (!res.ok) {
                    if (res.status === 422 && json.errors) {
                        showErrors(json.errors);
                        showToast(
                            "error",
                            "Validation failed",
                            "Please fix the highlighted fields.",
                        );
                    } else {
                        showToast(
                            "error",
                            "Error",
                            json.message ?? "Something went wrong.",
                        );
                    }
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(ebrModalEl).hide();
                showToast(
                    "success",
                    method === "PUT" ? "Updated!" : "Created!",
                    json.message ?? "",
                );
                loadRoles();
            } catch (err) {
                showToast("error", "Request failed", err.message);
            } finally {
                spinner.classList.add("d-none");
                btnText.textContent = method === "PUT" ? "Update" : "Save";
            }
        });

    /* ── Delete flow ────────────────────────────────────────────── */
    function askDelete(id) {
        deleteTargetId = id;
        bootstrap.Modal.getOrCreateInstance(ebrDeleteModalEl).show();
    }

    document
        .getElementById("ebrConfirmDeleteBtn")
        .addEventListener("click", async () => {
            if (!deleteTargetId) return;
            const spinner = document.getElementById("ebrDeleteSpinner");
            spinner.classList.remove("d-none");

            try {
                const res = await apiFetch(`${API_BASE}/${deleteTargetId}`, {
                    method: "DELETE",
                });
                const json = await res.json();

                if (!res.ok) {
                    showToast(
                        "error",
                        "Error",
                        json.message ?? "Failed to delete role.",
                    );
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(ebrDeleteModalEl).hide();
                showToast("success", "Deleted!", json.message ?? "");
                loadRoles();
            } catch (err) {
                showToast("error", "Request failed", err.message);
            } finally {
                deleteTargetId = null;
            }
        });

    /* ── Toggle status ──────────────────────────────────────────── */
    async function toggleStatus(id) {
        try {
            const res = await apiFetch(`${API_BASE}/${id}/toggle`, {
                method: "PATCH",
            });
            const json = await res.json();

            if (!res.ok) {
                showToast(
                    "error",
                    "Error",
                    json.message ?? "Failed to update status.",
                );
                return;
            }

            showToast("success", "Status updated", "");
            loadRoles();
        } catch (err) {
            showToast("error", "Request failed", err.message);
        }
    }

    /* ── Render ─────────────────────────────────────────────────── */
    const esc = (s) =>
        s
            ? String(s)
                  .replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
            : "";

    function journalCell(r) {
        if (r.journal && r.journal.title) {
            return `<span class="edit-btn" title="${esc(r.journal.title)}">${esc(r.journal.title)}</span>`;
        }
        return `<span class="eb-muted-cell">Site-wide</span>`;
    }

    function renderRows(roles) {
        return roles
            .map(
                (r) => `
                <tr>
                    <td>${journalCell(r)}</td>
                    <td class="eb-name-cell" title="${esc(r.role)}">${esc(r.role)}</td>
                    <td>
                        <span class="${Number(r.status) ? "green-btn" : "delete-btn"}" style="cursor:pointer" onclick="window.__ebrToggle(${r.id})">
                            ${Number(r.status) ? "Active" : "Inactive"}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex">
                            <button class="edit-btn" onclick="window.__ebrEdit(${r.id})">Edit</button>
                            <button class="delete-btn" onclick="window.__ebrDelete(${r.id})">Delete</button>
                        </div>
                    </td>
                </tr>`,
            )
            .join("");
    }

    function renderPage(roles) {
        document.getElementById("ebrLoading").classList.add("d-none");

        if (!roles.length) {
            document.getElementById("ebrEmpty").classList.remove("d-none");
            document.getElementById("ebrTableWrap").classList.add("d-none");
            return;
        }

        document.getElementById("ebrEmpty").classList.add("d-none");
        document.getElementById("ebrTableWrap").classList.remove("d-none");

        const sorted = [...roles].sort((a, b) =>
            (a.role || "").localeCompare(b.role || ""),
        );

        document.getElementById("ebrTbody").innerHTML = renderRows(sorted);
    }

    /* ── Load (adminIndex — includes inactive) ─────────────────── */
    async function loadRoles() {
        document.getElementById("ebrLoading").classList.remove("d-none");
        document.getElementById("ebrEmpty").classList.add("d-none");
        document.getElementById("ebrTableWrap").classList.add("d-none");

        const journalId = document.getElementById("journalFilter").value;
        const url = journalId
            ? `${API_BASE}?journal_id=${journalId}`
            : API_BASE;

        try {
            const res = await apiFetch(url);

            if (res.status === 401) {
                showToast(
                    "error",
                    "Session expired",
                    "Please log in again.",
                );
                document.getElementById("ebrLoading").classList.add("d-none");
                return;
            }

            const json = await res.json();
            cachedRoles = json.data || [];
            renderPage(cachedRoles);
        } catch (e) {
            document.getElementById("ebrLoading").classList.add("d-none");
            showToast("error", "Load failed", e.message);
        }
    }

    /* ── Global handlers (used by inline onclick) ─────────────────── */
    window.__ebrEdit = (id) => {
        const role = cachedRoles.find((r) => r.id === id);
        if (!role) return;
        resetForm();
        fillForm(role);
        document.getElementById("ebrModalTitle").textContent = "Edit Role";
        document.getElementById("ebrSaveBtnText").textContent = "Update";
        bootstrap.Modal.getOrCreateInstance(ebrModalEl).show();
    };

    window.__ebrDelete = (id) => askDelete(id);
    window.__ebrToggle = (id) => toggleStatus(id);

    loadJournals();
    loadRoles();
});