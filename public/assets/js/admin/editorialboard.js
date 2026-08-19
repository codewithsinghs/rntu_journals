document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = window.APP_CONFIG.API_BASE;
    const JOURNALS_API = window.APP_CONFIG.JOURNALS_API;
    const ROLES_API = window.APP_CONFIG.ROLES_API;

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

    const ebModalEl = document.getElementById("ebModal");
    const ebDeleteModalEl = document.getElementById("ebDeleteModal");
    let deleteTargetId = null;
    let cachedMembers = [];
    let cachedJournals = [];
    let cachedRoles = []; // roles currently loaded for the selected journal
    let rolesRequestToken = 0; // guards against out-of-order responses

    const ROLE_ORDER = [
        "Editor-in-Chief",
        "Managing Editor",
        "Executive Editor",
        "Advisory Board",
        "Editors",
        "Associate Editors",
        "Members",
    ];

    /* ── Toast (built in JS, no Blade markup required) ────────────── */
    function ensureToastEl() {
        let el = document.getElementById("ebToast");
        if (el) return el;

        let container = document.getElementById("ebToastContainer");
        if (!container) {
            container = document.createElement("div");
            container.id = "ebToastContainer";
            container.className =
                "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
        }

        el = document.createElement("div");
        el.id = "ebToast";
        el.className = "toast align-items-center border-0 text-white";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-start gap-2">
                    <span id="ebToastIcon" class="flex-shrink-0"></span>
                    <div>
                        <div id="ebToastTitle" class="fw-semibold"></div>
                        <div id="ebToastMsg" class="small"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div style="height:3px;background:rgba(255,255,255,.25);">
                <div id="ebToastBar" style="height:100%;background:rgba(255,255,255,.8);width:100%;"></div>
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
        document.getElementById("ebToastTitle").textContent = title;
        const msgEl = document.getElementById("ebToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        document.getElementById("ebToastIcon").innerHTML =
            type === "success"
                ? `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
                : `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        const bar = document.getElementById("ebToastBar");
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
            .querySelectorAll('[id^="err_"]')
            .forEach((el) => (el.textContent = ""));
        document
            .querySelectorAll(".is-invalid")
            .forEach((el) => el.classList.remove("is-invalid"));
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([field, msgs]) => {
            const msg = Array.isArray(msgs) ? msgs[0] : msgs;
            const err = document.getElementById(`err_${field}`);
            if (err) err.textContent = msg;
            document.getElementById(field)?.classList.add("is-invalid");
        });
    }

    /* ── Form helpers ───────────────────────────────────────────── */
    // NOTE: "role" is intentionally excluded from this list — its <select>
    // is populated asynchronously (scoped to the chosen journal), so it is
    // filled in separately once its options exist. See fillForm() / __ebEdit().
    const TEXT_FIELDS = [
        "journal_id",
        "name",
        "designation",
        "department",
        "institute",
        "university_or_org",
        "city",
        "email",
        "orcid_url",
        "scopus_url",
        "web_of_science_url",
        "sequence",
    ];

    /* ── Roles: journal-scoped, loaded from editorial_board_roles ─── */
    function setRoleSelectState(state, options) {
        const select = document.getElementById("role");
        if (state === "loading") {
            select.disabled = true;
            select.innerHTML = `<option value="">Loading roles…</option>`;
        } else if (state === "empty") {
            select.disabled = true;
            select.innerHTML = `<option value="">No roles configured for this journal</option>`;
        } else if (state === "prompt") {
            select.disabled = true;
            select.innerHTML = `<option value="">Select a journal first</option>`;
        } else if (state === "error") {
            select.disabled = false;
            select.innerHTML = `<option value="">Failed to load roles</option>`;
        } else if (state === "ready") {
            select.disabled = false;
            select.innerHTML =
                `<option value="">Select role</option>` +
                (options || [])
                    .map(
                        (r) =>
                            `<option value="${r.role}">${r.role}</option>`,
                    )
                    .join("");
        }
    }

    /**
     * Loads the roles configured for a given journal (or site-wide roles
     * when journalId is empty/null) from the editorial_board_roles table
     * and repopulates the #role select.
     *
     * Returns the loaded roles array. If a newer call is made before this
     * one resolves, its result is discarded (rolesRequestToken guard) so
     * fast journal switching can't leave a stale role list behind.
     */
    async function loadRolesForJournal(journalId) {
        const myToken = ++rolesRequestToken;
        cachedRoles = [];
        setRoleSelectState("loading");

        try {
            const url = journalId
                ? `${ROLES_API}?journal_id=${journalId}`
                : `${ROLES_API}?journal_id=`;
            const res = await apiFetch(url);
            const json = await res.json();

            if (myToken !== rolesRequestToken) return cachedRoles; // stale

            if (!res.ok) {
                setRoleSelectState("error");
                return [];
            }

            // Support either a flat array (json.data) or a paginated
            // shape (json.data.data), same pattern as loadJournals().
            const roles = json.data?.data ?? json.data ?? [];
            cachedRoles = roles;

            if (!roles.length) {
                setRoleSelectState("empty");
            } else {
                setRoleSelectState("ready", roles);
            }

            return roles;
        } catch (e) {
            if (myToken !== rolesRequestToken) return cachedRoles; // stale
            console.error("Failed to load roles list:", e.message);
            setRoleSelectState("error");
            return [];
        }
    }

    document
        .getElementById("journal_id")
        .addEventListener("change", (e) => {
            loadRolesForJournal(e.target.value);
        });

    function resetForm() {
        document.getElementById("ebForm").reset();
        document.getElementById("ebId").value = "";
        document.getElementById("ebMethod").value = "POST";
        document.getElementById("is_active").checked = true;
        document.getElementById("sequence").value = 0;
        document.getElementById("journal_id").value = "";
        document.getElementById("ebImagePreview").classList.add("d-none");
        document.getElementById("ebImagePreview").src = "";
        setRoleSelectState("prompt");
        clearErrors();
    }

    function fillForm(m) {
        TEXT_FIELDS.forEach((f) => {
            const el = document.getElementById(f);
            if (el) el.value = m[f] ?? "";
        });
        document.getElementById("is_active").checked = !!Number(m.is_active);
        document.getElementById("ebId").value = m.id;
        document.getElementById("ebMethod").value = "PUT";

        if (m.profile_image) {
            const img = document.getElementById("ebImagePreview");
            img.src = `/storage/${m.profile_image}`;
            img.classList.remove("d-none");
        }
    }

    /* ── Load journals for dropdowns ───────────────────────────── */
    async function loadJournals() {
        try {
            const res = await apiFetch(JOURNALS_API);
            const json = await res.json();
            cachedJournals = json.data?.data ?? [];

            const formSelect = document.getElementById("journal_id");
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
        .addEventListener("change", () => loadMembers());

    /* ── Open Add modal ─────────────────────────────────────────── */
    document.getElementById("openEbModal").addEventListener("click", () => {
        resetForm();
        document.getElementById("ebModalTitle").textContent = "Add Member";
        document.getElementById("ebSaveBtnText").textContent = "Save";
        bootstrap.Modal.getOrCreateInstance(ebModalEl).show();
    });

    /* ── Image preview on select ───────────────────────────────── */
    document
        .getElementById("profile_image")
        .addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const img = document.getElementById("ebImagePreview");
            img.src = URL.createObjectURL(file);
            img.classList.remove("d-none");
        });

    /* ── Save (form submit, not button click) ──────────────────── */
    document
        .getElementById("ebForm")
        .addEventListener("submit", async (e) => {
            e.preventDefault();
            clearErrors();

            const id = document.getElementById("ebId").value;
            const method = document.getElementById("ebMethod").value;
            const spinner = document.getElementById("ebSaveSpinner");
            const btnText = document.getElementById("ebSaveBtnText");

            spinner.classList.remove("d-none");
            btnText.textContent = method === "PUT" ? "Updating…" : "Saving…";

            const formData = new FormData(document.getElementById("ebForm"));
            formData.set(
                "is_active",
                document.getElementById("is_active").checked ? "1" : "0",
            );
            if (method === "PUT") formData.append("_method", "PUT");

            const url = method === "PUT" ? `${API_BASE}/${id}` : API_BASE;

            try {
                const res = await apiFetch(url, {
                    method: "POST",
                    body: formData,
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

                bootstrap.Modal.getOrCreateInstance(ebModalEl).hide();
                showToast(
                    "success",
                    method === "PUT" ? "Updated!" : "Created!",
                    json.message ?? "",
                );
                loadMembers();
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
        bootstrap.Modal.getOrCreateInstance(ebDeleteModalEl).show();
    }

    document
        .getElementById("ebConfirmDeleteBtn")
        .addEventListener("click", async () => {
            if (!deleteTargetId) return;
            const spinner = document.getElementById("ebDeleteSpinner");
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
                        json.message ?? "Failed to delete member.",
                    );
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(ebDeleteModalEl).hide();
                showToast("success", "Deleted!", json.message ?? "");
                loadMembers();
            } catch (err) {
                showToast("error", "Request failed", err.message);
            } finally {
                spinner.classList.add("d-none");
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
            loadMembers();
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
    const initials = (name) =>
        (name || "?")
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((w) => w[0])
            .join("")
            .toUpperCase();

    function journalCell(m) {
        if (m.journal && m.journal.title) {
            return `<span class="edit-btn" title="${esc(m.journal.title)}">${esc(m.journal.title)}</span>`;
        }
        return `<span class="eb-muted-cell">Site-wide</span>`;
    }
function renderRows(members) {
    return members
        .map(
            (m) => `
            <tr>
                <td></td>
                <td>${journalCell(m)}</td>
                <td><span class="green-btn" title="${esc(m.role)}">${esc(m.role)}</span></td>
                <td class="eb-name-cell" title="${esc(m.name)}">${esc(m.name)}</td>
                <td>${m.sequence ?? 0}</td>
                <td>
                    <span class="${Number(m.is_active) ? "green-btn" : "delete-btn"}" style="cursor:pointer" onclick="window.__ebToggle(${m.id})">
                        ${Number(m.is_active) ? "Active" : "Inactive"}
                    </span>
                </td>
                <td>
                    <div class="d-flex">
                        <button class="edit-btn" onclick="window.__ebEdit(${m.id})">Edit</button>
                        <button class="delete-btn" onclick="window.__ebDelete(${m.id})">Delete</button>
                    </div>
                </td>
            </tr>`,
        )
        .join("");
}

    function renderPage(members) {
        document.getElementById("ebLoading").classList.add("d-none");

        if (!members.length) {
            document.getElementById("ebEmpty").classList.remove("d-none");
            document.getElementById("ebTableWrap").classList.add("d-none");
            return;
        }

        document.getElementById("ebEmpty").classList.add("d-none");
        document.getElementById("ebTableWrap").classList.remove("d-none");

        // sort by role order, then sequence, then name
        const roleRank = (r) => {
            const i = ROLE_ORDER.indexOf(r);
            return i === -1 ? ROLE_ORDER.length : i;
        };
        const sorted = [...members].sort((a, b) => {
            const rr = roleRank(a.role) - roleRank(b.role);
            if (rr !== 0) return rr;
            const seqDiff = (a.sequence ?? 0) - (b.sequence ?? 0);
            if (seqDiff !== 0) return seqDiff;
            return (a.name || "").localeCompare(b.name || "");
        });

        document.getElementById("ebTbody").innerHTML = renderRows(sorted);
    }

    /* ── Load (adminIndex — includes inactive) ─────────────────── */
    async function loadMembers() {
        document.getElementById("ebLoading").classList.remove("d-none");
        document.getElementById("ebEmpty").classList.add("d-none");
        document.getElementById("ebTableWrap").classList.add("d-none");

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
                document.getElementById("ebLoading").classList.add("d-none");
                return;
            }

            const json = await res.json();
            cachedMembers = json.data || [];
            renderPage(cachedMembers);
        } catch (e) {
            document.getElementById("ebLoading").classList.add("d-none");
            showToast("error", "Load failed", e.message);
        }
    }

    /* ── Global handlers (used by inline onclick) ─────────────────── */
    window.__ebEdit = async (id) => {
        const member = cachedMembers.find((m) => m.id === id);
        if (!member) return;

        resetForm();
        fillForm(member);
        document.getElementById("ebModalTitle").textContent = "Edit Member";
        document.getElementById("ebSaveBtnText").textContent = "Update";
        bootstrap.Modal.getOrCreateInstance(ebModalEl).show();

        const roles = await loadRolesForJournal(member.journal_id || "");
        const roleSelect = document.getElementById("role");
        const hasRole = roles.some((r) => r.role === member.role);

        if (!hasRole && member.role) {
            const opt = document.createElement("option");
            opt.value = member.role;
            opt.textContent = `${member.role} (not in current list)`;
            roleSelect.appendChild(opt);
            roleSelect.disabled = false;
        }

        roleSelect.value = member.role ?? "";
    };

    window.__ebDelete = (id) => askDelete(id);
    window.__ebToggle = (id) => toggleStatus(id);

    loadJournals();
    loadMembers();
});