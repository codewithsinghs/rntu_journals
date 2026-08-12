document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/prp";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    const loadingEl = document.getElementById("glLoading");
    const formContainerEl = document.getElementById("glFormContainer");

    /* ── CKEditor fields ────────────────────────────────────────── */
    const editors = {};
    const CK_FIELDS = [
        { id: "author_description", required: true },
    ];

    const TOOLBAR = [
        "undo", "redo", "|", "heading", "|", "fontFamily", "fontSize", "fontColor",
        "fontBackgroundColor", "|", "bold", "italic", "underline", "strikethrough", "|",
        "alignment", "|", "bulletedList", "numberedList", "outdent", "indent", "|",
        "link", "blockQuote", "insertTable", "|", "imageUpload", "mediaEmbed", "|",
        "code", "codeBlock", "horizontalLine",
    ];

    async function initEditors() {
        for (const { id } of CK_FIELDS) {
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
    // Only fields that actually exist as plain inputs in the blade file.
    const PLAIN_FIELDS = [
        "author_heading",
    ];

    function fillForm(r) {
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

    /* ── Load the single PRP record (if one exists) ───────────── */
    async function loadRecord() {
        try {
            const res = await fetch(API_BASE, { headers: authHeaders() });
            const json = await res.json();

            if (json.status && json.data) {
                fillForm(json.data);
            }
            // No record yet → form stays empty, glMethod stays "POST"
        } catch (e) {
            showToast("error", "Load failed", e.message);
        } finally {
            loadingEl.classList.add("d-none");
            formContainerEl.classList.remove("d-none");
        }
    }

    /* ── Save (create or update) ───────────────────────────────── */
    document.getElementById("glSaveBtn").addEventListener("click", async () => {
        clearErrors();
        syncEditors();

        let hasError = false;

        // Required plain fields
        PLAIN_FIELDS.forEach((f) => {
            const el = document.getElementById(f);
            if (el && el.hasAttribute("required") && !el.value.trim()) {
                el.classList.add("is-invalid");
                const errEl = document.getElementById(`err_${f}`);
                if (errEl) errEl.textContent = "This field is required.";
                hasError = true;
            }
        });

        // Required CKEditor fields
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

            showToast("success", method === "PUT" ? "Updated!" : "Created!", json.message ?? "");

            if (json.data?.id) {
                document.getElementById("glId").value = json.data.id;
                document.getElementById("glMethod").value = "PUT";
            }
        } catch (err) {
            showToast("error", "Request failed", err.message);
        } finally {
            spinner.classList.add("d-none");
            btnText.textContent = document.getElementById("glMethod").value === "PUT" ? "Update" : "Save";
        }
    });

    /* ── Init ───────────────────────────────────────────────────── */
    initEditors().then(loadRecord);
});