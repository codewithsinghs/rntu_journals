document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/pages";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    let currentPage = 1;
    let perPage = 10;
    let searchTerm = "";
    let deleteTargetId = null;

    const pageModalEl = document.getElementById("PageModal");
    const deleteModalEl = document.getElementById("pgDeleteModal");

    /* ── CKEditor field ─────────────────────────────────────────── */
    let editor = null;
    let editorReady = false;

    const TOOLBAR = [
        "undo", "redo", "|", "heading", "|", "fontFamily", "fontSize", "fontColor",
        "fontBackgroundColor", "|", "bold", "italic", "underline", "strikethrough", "|",
        "alignment", "|", "bulletedList", "numberedList", "outdent", "indent", "|",
        "link", "blockQuote", "insertTable", "|", "imageUpload", "mediaEmbed", "|",
        "code", "codeBlock", "horizontalLine",
    ];

    async function initEditor() {
        if (editor) {
            await editor.destroy();
            editor = null;
        }

        editor = await CKEDITOR.ClassicEditor.create(
            document.getElementById("ck_content"),
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
                placeholder: "Enter page content…",
            },
        );

        editor.model.document.on("change:data", () => {
            document.getElementById("content").value = editor.getData();
        });
    }

    /* ── Toast ──────────────────────────────────────────────────── */
    function showToast(type, title, msg) {
        const el = document.getElementById("pgToast");
        if (!el) return;
        document.getElementById("pgToastTitle").textContent = title;
        const msgEl = document.getElementById("pgToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        document.getElementById("pgToastIcon").innerHTML =
            type === "success"
                ? `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
                : `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        const bar = document.getElementById("pgToastBar");
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
            if (field === "content") {
                document.getElementById("ck_content")?.classList.add("is-invalid");
            } else {
                document.getElementById(field)?.classList.add("is-invalid");
            }
        });
    }

    /* ── Form helpers ───────────────────────────────────────────── */
    const PLAIN_FIELDS = ["title", "slug", "status", "meta_title", "meta_description"];

    function resetForm() {
        document.getElementById("pgForm").reset();
        document.getElementById("pgId").value = "";
        document.getElementById("pgMethod").value = "POST";
        document.getElementById("status").value = "draft";
        document.getElementById("is_homepage").checked = false;
        if (editor) editor.setData("");
        document.getElementById("content").value = "";
        clearErrors();
    }

    function fillForm(r) {
        PLAIN_FIELDS.forEach((f) => {
            const el = document.getElementById(f);
            if (el) el.value = r[f] ?? "";
        });
        document.getElementById("is_homepage").checked = !!r.is_homepage;
        if (editor) editor.setData(r.content ?? "");
        document.getElementById("content").value = r.content ?? "";
        document.getElementById("pgId").value = r.id;
        document.getElementById("pgMethod").value = "PUT";
    }

    function syncEditor() {
        if (editor) document.getElementById("content").value = editor.getData();
    }

    /* ── Open Add modal ─────────────────────────────────────────── */
    document.getElementById("openAddBtn").addEventListener("click", () => {
        resetForm();
        document.getElementById("pgModalTitle").textContent = "Add Page";
        document.getElementById("pgSaveBtnText").textContent = "Save";
        bootstrap.Modal.getOrCreateInstance(pageModalEl).show();
    });

    pageModalEl.addEventListener("shown.bs.modal", async () => {
        if (!editorReady) {
            await initEditor();
            editorReady = true;
        }
        const ta = document.getElementById("content");
        if (editor && ta) editor.setData(ta.value || "");
    });

    /* ── Edit ───────────────────────────────────────────────────── */
    async function editPage(id) {
        try {
            const res = await fetch(`${API_BASE}/${id}`, { headers: authHeaders() });
            const json = await res.json();
            if (!json.status) {
                showToast("error", "Error", json.message ?? "Failed to load record.");
                return;
            }
            resetForm();
            fillForm(json.data);
            document.getElementById("pgModalTitle").textContent = "Edit Page";
            document.getElementById("pgSaveBtnText").textContent = "Update";
            bootstrap.Modal.getOrCreateInstance(pageModalEl).show();
        } catch (e) {
            showToast("error", "Load failed", e.message);
        }
    }

    /* ── Save (create or update) ───────────────────────────────── */
    document.getElementById("pgSaveBtn").addEventListener("click", async () => {
        clearErrors();
        syncEditor();

        let hasError = false;

        if (!document.getElementById("title").value.trim()) {
            document.getElementById("title").classList.add("is-invalid");
            document.getElementById("err_title").textContent = "Title is required.";
            hasError = true;
        }

        const contentVal = editor ? editor.getData() : "";
        if (!contentVal.trim() || contentVal === "<p>&nbsp;</p>") {
            document.getElementById("ck_content")?.classList.add("is-invalid");
            document.getElementById("err_content").textContent = "Content is required.";
            hasError = true;
        }

        if (hasError) return;

        const id = document.getElementById("pgId").value;
        const method = document.getElementById("pgMethod").value;
        const spinner = document.getElementById("pgSaveSpinner");
        const btnText = document.getElementById("pgSaveBtnText");

        spinner.classList.remove("d-none");
        btnText.textContent = method === "PUT" ? "Updating…" : "Saving…";

        const formData = new FormData(document.getElementById("pgForm"));
        formData.set("is_homepage", document.getElementById("is_homepage").checked ? "1" : "0");
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

            bootstrap.Modal.getOrCreateInstance(pageModalEl).hide();
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
    function askDelete(id, title) {
        deleteTargetId = id;
        document.getElementById("deletePageTitle").textContent = title;
        bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
    }

    document.getElementById("pgConfirmDeleteBtn").addEventListener("click", async () => {
        if (!deleteTargetId) return;
        const spinner = document.getElementById("pgDeleteSpinner");
        spinner.classList.remove("d-none");

        try {
            const res = await fetch(`${API_BASE}/${deleteTargetId}`, {
                method: "DELETE",
                headers: authHeaders(),
            });
            const json = await res.json();

            if (!res.ok) {
                showToast("error", "Error", json.message ?? "Failed to delete page.");
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

    /* ── Toggle status ─────────────────────────────────────────── */
    async function toggleStatus(id) {
        try {
            const res = await fetch(`${API_BASE}/${id}/toggle`, {
                method: "PATCH",
                headers: authHeaders(),
            });
            const json = await res.json();
            if (!res.ok) {
                showToast("error", "Error", json.message ?? "Failed to toggle status.");
                return;
            }
            showToast("success", "Updated!", json.message ?? "");
            loadTable();
        } catch (e) {
            showToast("error", "Request failed", e.message);
        }
    }

    /* ── Render table ───────────────────────────────────────────── */
    function renderRows(records) {
        return records
            .map((r) => {
                const created = r.created_at ? new Date(r.created_at).toLocaleDateString() : "—";
                const statusBadge = r.status === "published"
                    ? `<span class="badge bg-success">Published</span>`
                    : `<span class="badge bg-secondary">Draft</span>`;
                return `
                <tr>
                    <td>${r.title ?? "—"}</td>
                    <td>${r.slug ?? "—"}</td>
                    <td>${r.is_homepage ? "Yes" : "—"}</td>
                    <td><a href="#" onclick="window.__pgToggle(${r.id}); return false;">${statusBadge}</a></td>
                    <td>${created}</td>
                    <td>
                        <div class="d-flex">
                            <button class="edit-btn" onclick="window.__pgEdit(${r.id})">Edit</button>
                            <button class="delete-btn" onclick="window.__pgDelete(${r.id}, '${(r.title ?? "").replace(/'/g, "\\'")}')">Delete</button>
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

            document.getElementById("pagesTableBody").innerHTML = renderRows(records);
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
    window.__pgEdit = (id) => editPage(id);
    window.__pgDelete = (id, title) => askDelete(id, title);
    window.__pgToggle = (id) => toggleStatus(id);

    loadTable();
});