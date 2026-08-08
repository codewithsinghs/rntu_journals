document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/guidelines";
    const JOURNALS_API = "/api/admin/journals";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    let currentPage = 1;
    let perPage = 10;
    let searchTerm = "";
    let deleteTargetId = null;
    let cachedJournals = [];

    const guidelineModalEl = document.getElementById("GuidelineModal");
    const deleteModalEl = document.getElementById("glDeleteModal");

    /* ── CKEditor fields ────────────────────────────────────────── */
    const editors = {};
    const CK_FIELDS = [
        { id: "author_description", required: true },
        { id: "process_description", required: true },
        { id: "manuscript_description", required: true },
        { id: "formatting_description", required: true },
        { id: "layout_description", required: true },
        { id: "acknowlegdement_description", required: true },
    ];

    const TOOLBAR = [
        "undo", "redo", "|", "heading", "|", "fontFamily", "fontSize", "fontColor",
        "fontBackgroundColor", "|", "bold", "italic", "underline", "strikethrough", "|",
        "alignment", "|", "bulletedList", "numberedList", "outdent", "indent", "|",
        "link", "blockQuote", "insertTable", "|", "imageUpload", "mediaEmbed", "|",
        "code", "codeBlock", "horizontalLine",
    ];

    // CKEditor cannot mount into a hidden (display:none) element — the
    // modal starts hidden, so editors are only created once, on the
    // FIRST time the modal is shown, then reused/cleared on every
    // subsequent open (add or edit).
    let editorsReady = false;

    async function initEditors() {
        for (const { id } of CK_FIELDS) {
            if (editors[id]) {
                await editors[id].destroy();
                delete editors[id];
            }

            editors[id] = await CKEDITOR.ClassicEditor.create(
                document.getElementById(`ck_${id}`),
                {
                    licenseKey: "GPL",
                    removePlugins: [
                        "CKBox", "CKFinder", "EasyImage",
                        "RealTimeCollaborativeComments", "RealTimeCollaborativeTrackChanges",
                        "RealTimeCollaborativeRevisionHistory", "PresenceList", "Comments",
                        "TrackChanges", "TrackChangesData", "RevisionHistory", "Pagination",
                        "WProofreader", "MathType", "SlashCommand", "Template",
                        "DocumentOutline", "FormatPainter", "TableOfContents",
                        "PasteFromOfficeEnhanced", "AIAssistant", "MultiLevelList", "CaseChange",
                    ],
                    toolbar: { items: TOOLBAR },
                    alignment: { options: ["left", "center", "right", "justify"] },
                    fontSize: { options: [8, 10, 12, 14, "default", 18, 24, 32, 48] },
                    table: {
                        contentToolbar: [
                            "tableColumn", "tableRow", "mergeTableCells",
                            "tableProperties", "tableCellProperties",
                        ],
                    },
                    placeholder: "Enter content…",
                },
            );

            editors[id].model.document.on("change:data", () => {
                document.getElementById(id).value = editors[id].getData();
            });
        }
    }

    /* ── Toast ──────────────────────────────────────────────────── */
    function showToast(type, title, msg) {
        const el = document.getElementById("glToast");
        if (!el) return;
        document.getElementById("glToastTitle").textContent = title;
        const msgEl = document.getElementById("glToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        document.getElementById("glToastIcon").innerHTML =
            type === "success"
                ? `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
                : `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        const bar = document.getElementById("glToastBar");
        bar.style.transition = "none";
        bar.style.width = "100%";
        requestAnimationFrame(() =>
            requestAnimationFrame(() => {
                bar.style.transition = "width 4s linear";
                bar.style.width = "0%";
            }),
        );
        bootstrap.Toast.getOrCreateInstance(el, { delay: 4000, autohide: true }).show();
    }

    /* ── Errors ─────────────────────────────────────────────────── */
    function clearErrors() {
        document.querySelectorAll('[id^="err_"]').forEach((el) => (el.textContent = ""));
        document.querySelectorAll(".is-invalid").forEach((el) => el.classList.remove("is-invalid"));
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([field, msgs]) => {
            const msg = Array.isArray(msgs) ? msgs[0] : msgs;
            const err = document.getElementById(`err_${field}`);
            if (err) err.textContent = msg;
            if (CK_FIELDS.some((f) => f.id === field)) {
                document.getElementById(`ck_${field}`)?.classList.add("is-invalid");
            } else {
                document.getElementById(field)?.classList.add("is-invalid");
            }
        });
    }

    /* ── Form helpers ───────────────────────────────────────────── */
    const PLAIN_FIELDS = [
        "author_badge", "author_heading",
        "process_badge", "process_heading",
        "manuscript_badge", "manuscript_heading",
        "formatting_badge1", "formatting_badge2", "formatting_heading",
        "layout_badge1", "layout_heading",
        "acknowlegdement_badge1", "acknowlegdement_heading",
    ];

    function resetForm() {
        document.getElementById("glForm").reset();
        document.getElementById("glId").value = "";
        document.getElementById("glMethod").value = "POST";
        document.getElementById("journal_id").value = "";
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id]) editors[id].setData("");
            const ta = document.getElementById(id);
            if (ta) ta.value = "";
        });
        clearErrors();
    }

    function fillForm(r) {
        document.getElementById("journal_id").value = r.journal_id ?? "";
        PLAIN_FIELDS.forEach((f) => {
            const el = document.getElementById(f);
            if (el) el.value = r[f] ?? "";
        });
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id]) editors[id].setData(r[id] ?? "");
            const ta = document.getElementById(id);
            if (ta) ta.value = r[id] ?? "";
        });
        document.getElementById("glId").value = r.id;
        document.getElementById("glMethod").value = "PUT";
    }

    function syncEditors() {
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id]) document.getElementById(id).value = editors[id].getData();
        });
    }

    /* ── Load journals for the select dropdown ────────────────────── */
    async function loadJournals() {
        try {
            const res = await fetch(JOURNALS_API, { headers: authHeaders() });
            const json = await res.json();
            // JournalsController::adminIndex() paginates → nested at json.data.data
            cachedJournals = json.data?.data ?? [];

            const select = document.getElementById("journal_id");
            select.innerHTML = '<option value="">Select journal…</option>';
            cachedJournals.forEach((j) => {
                const opt = document.createElement("option");
                opt.value = j.id;
                opt.textContent = j.title;
                select.appendChild(opt);
            });
        } catch (e) {
            console.error("Failed to load journals:", e.message);
        }
    }

    function journalTitle(id) {
        const j = cachedJournals.find((j) => j.id == id);
        return j ? j.title : "—";
    }

    /* ── Open Add modal ─────────────────────────────────────────── */
    document.getElementById("openAddBtn").addEventListener("click", () => {
        resetForm();
        document.getElementById("glModalTitle").textContent = "Add Guideline";
        document.getElementById("glSaveBtnText").textContent = "Save";
        bootstrap.Modal.getOrCreateInstance(guidelineModalEl).show();
    });

    guidelineModalEl.addEventListener("shown.bs.modal", async () => {
        if (!editorsReady) {
            await initEditors();
            editorsReady = true;
        }
        // Re-apply whatever the form currently holds (covers the edit
        // case, where fillForm() may have run before editors existed).
        CK_FIELDS.forEach(({ id }) => {
            const ta = document.getElementById(id);
            if (editors[id] && ta) editors[id].setData(ta.value || "");
        });
    });

    /* ── Edit ───────────────────────────────────────────────────── */
    async function editGuideline(id) {
        try {
            const res = await fetch(`${API_BASE}/${id}`, { headers: authHeaders() });
            const json = await res.json();
            if (!json.status) {
                showToast("error", "Error", json.message ?? "Failed to load record.");
                return;
            }
            resetForm();
            fillForm(json.data);
            document.getElementById("glModalTitle").textContent = "Edit Guideline";
            document.getElementById("glSaveBtnText").textContent = "Update";
            bootstrap.Modal.getOrCreateInstance(guidelineModalEl).show();
        } catch (e) {
            showToast("error", "Load failed", e.message);
        }
    }

    /* ── Save (create or update) ───────────────────────────────── */
    document.getElementById("glSaveBtn").addEventListener("click", async () => {
        clearErrors();
        syncEditors();

        let hasError = false;

        if (!document.getElementById("journal_id").value) {
            document.getElementById("journal_id").classList.add("is-invalid");
            document.getElementById("err_journal_id").textContent = "Please select a journal.";
            hasError = true;
        }

        CK_FIELDS.forEach((f) => {
            if (f.required) {
                const val = editors[f.id] ? editors[f.id].getData() : "";
                if (!val.trim() || val === "<p>&nbsp;</p>") {
                    document.getElementById(`ck_${f.id}`)?.classList.add("is-invalid");
                    const errEl = document.getElementById(`err_${f.id}`);
                    if (errEl) errEl.textContent = "This field is required.";
                    hasError = true;
                }
            }
        });
        if (hasError) return;

        const id = document.getElementById("glId").value;
        const method = document.getElementById("glMethod").value;
        const spinner = document.getElementById("glSaveSpinner");
        const btnText = document.getElementById("glSaveBtnText");

        spinner.classList.remove("d-none");
        btnText.textContent = method === "PUT" ? "Updating…" : "Saving…";

        const formData = new FormData(document.getElementById("glForm"));
        if (method === "PUT") formData.append("_method", "PUT");

        const url = method === "PUT" ? `${API_BASE}/${id}` : API_BASE;

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: { Authorization: `Bearer ${TOKEN}`, Accept: "application/json" },
                body: formData,
            });
            const json = await res.json();

            if (!res.ok) {
                if (res.status === 422 && json.errors) {
                    showErrors(json.errors);
                    showToast("error", "Validation failed", "Please fix the highlighted fields.");
                } else {
                    showToast("error", "Error", json.message ?? "Something went wrong.");
                }
                return;
            }

            bootstrap.Modal.getOrCreateInstance(guidelineModalEl).hide();
            showToast("success", method === "PUT" ? "Updated!" : "Created!", json.message ?? "");
            loadTable();
        } catch (err) {
            showToast("error", "Request failed", err.message);
        } finally {
            spinner.classList.add("d-none");
            btnText.textContent = method === "PUT" ? "Update" : "Save";
        }
    });

    /* ── Delete flow ────────────────────────────────────────────── */
    function askDelete(id, journalName) {
        deleteTargetId = id;
        document.getElementById("deleteGuidelineJournal").textContent = journalName;
        bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
    }

    document.getElementById("glConfirmDeleteBtn").addEventListener("click", async () => {
        if (!deleteTargetId) return;
        const spinner = document.getElementById("glDeleteSpinner");
        spinner.classList.remove("d-none");

        try {
            const res = await fetch(`${API_BASE}/${deleteTargetId}`, {
                method: "DELETE",
                headers: authHeaders(),
            });
            const json = await res.json();

            if (!res.ok) {
                showToast("error", "Error", json.message ?? "Failed to delete guideline.");
                return;
            }

            bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide();
            showToast("success", "Deleted!", json.message ?? "");
            loadTable();
        } catch (err) {
            showToast("error", "Request failed", err.message);
        } finally {
            spinner.classList.add("d-none");
            deleteTargetId = null;
        }
    });

    /* ── Render table ───────────────────────────────────────────── */
    function renderRows(records) {
        return records
            .map((r) => {
                const jTitle = r.journal?.title ?? journalTitle(r.journal_id);
                const created = r.created_at ? new Date(r.created_at).toLocaleDateString() : "—";
                return `
                <tr>
                    <td>${jTitle}</td>
                    <td>${r.author_heading ?? "—"}</td>
                    <td>${r.process_heading ?? "—"}</td>
                    <td>${r.manuscript_heading ?? "—"}</td>
                    <td>${created}</td>
                    <td>
                        <div class="d-flex">
                            <button class="edit-btn" onclick="window.__glEdit(${r.id})">Edit</button>
                            <button class="delete-btn" onclick="window.__glDelete(${r.id}, '${jTitle.replace(/'/g, "\\'")}')">Delete</button>
                        </div>
                    </td>
                </tr>`;
            })
            .join("");
    }

    function renderPagination(meta) {
        const pagination = document.getElementById("pagination");
        pagination.innerHTML = "";
        if (!meta || meta.last_page <= 1) return;

        for (let i = 1; i <= meta.last_page; i++) {
            const li = document.createElement("li");
            li.className = `page-item ${i === meta.current_page ? "active" : ""}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener("click", (e) => {
                e.preventDefault();
                currentPage = i;
                loadTable();
            });
            pagination.appendChild(li);
        }
    }

    /* ── Load table (paginated list) ───────────────────────────── */
    async function loadTable() {
        document.getElementById("tableLoading").style.display = "block";
        document.getElementById("tableWrap").style.display = "none";
        document.getElementById("tableEmpty").style.display = "none";

        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage,
        });
        if (searchTerm) params.set("q", searchTerm);

        try {
            const res = await fetch(`${API_BASE}?${params}`, { headers: authHeaders() });
            const json = await res.json();

            document.getElementById("tableLoading").style.display = "none";

            const records = json.data?.data ?? [];
            const meta = json.data;

            if (!records.length) {
                document.getElementById("tableEmpty").style.display = "block";
                return;
            }

            document.getElementById("guidelinesTableBody").innerHTML = renderRows(records);
            document.getElementById("tableWrap").style.display = "block";

            document.getElementById("entriesInfo").textContent =
                `Showing ${meta.from ?? 0} to ${meta.to ?? 0} of ${meta.total ?? 0} entries`;

            renderPagination(meta);
        } catch (e) {
            document.getElementById("tableLoading").style.display = "none";
            showToast("error", "Load failed", e.message);
        }
    }

    /* ── Search + per-page ─────────────────────────────────────── */
    let searchTimer = null;
    document.getElementById("searchInput").addEventListener("input", (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            searchTerm = e.target.value.trim();
            currentPage = 1;
            loadTable();
        }, 350);
    });

    document.getElementById("perPage").addEventListener("change", (e) => {
        perPage = parseInt(e.target.value, 10);
        currentPage = 1;
        loadTable();
    });

    /* ── Global handlers (used by inline onclick) ─────────────────── */
    window.__glEdit = (id) => editGuideline(id);
    window.__glDelete = (id, journalName) => askDelete(id, journalName);

    loadJournals().then(() => loadTable());
});