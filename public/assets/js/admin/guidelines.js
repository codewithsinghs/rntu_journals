document.addEventListener("DOMContentLoaded", function () {
    const API_BASE = "/api/admin/guidelines";
    const TOKEN = localStorage.getItem("jwt_token") || "";
    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    /* ── CKEditor fields ────────────────────────────────────────── */
    const editors = {};
    const CK_FIELDS = [
<<<<<<< HEAD
        "author_description",
        "process_description",
        "manuscript_description",
        "formatting_description",
        "layout_description",
        "acknowlegdement_description",
=======
        {
            id: "author_description",
            required: true,
        },
        {
            id: "process_description",
            required: true,
        },
        {
            id: "manuscript_description",
            required: true,
        },
        {
            id: "formatting_description",
            required: true,
        },
        {
            id: "layout_description",
            required: true,
        },
        {
            id: "acknowlegdement_description",
            required: true,
        },
    ];

    const TOOLBAR = [
        "undo",
        "redo",
        "|",
        "heading",
        "|",
        "fontFamily",
        "fontSize",
        "fontColor",
        "fontBackgroundColor",
        "|",
        "bold",
        "italic",
        "underline",
        "strikethrough",
        "|",
        "alignment",
        "|",
        "bulletedList",
        "numberedList",
        "outdent",
        "indent",
        "|",
        "link",
        "blockQuote",
        "insertTable",
        "|",
        "imageUpload",
        "mediaEmbed",
        "|",
        "code",
        "codeBlock",
        "horizontalLine",
>>>>>>> main
    ];

    /*
     * KEY FIX: CKEditor CANNOT mount into hidden (display:none) elements.
     * We initialise editors only once — on the first 'shown.bs.modal' event,
     * then reuse them on subsequent opens.
     */
    let editorsReady = false;
    let pendingFill = null;

    async function initEditors() {
<<<<<<< HEAD
        for (const field of CK_FIELDS) {
            if (editors[field]) {
                await editors[field].destroy();
                delete editors[field];
            }

            editors[field] = await CKEDITOR.ClassicEditor.create(
                document.getElementById(`ck_${field}`),
=======
        for (const { id } of CK_FIELDS) {
            if (editors[id]) {
                await editors[id].destroy();
                delete editors[id];
            }

            editors[id] = await CKEDITOR.ClassicEditor.create(
                document.getElementById(`ck_${id}`),
>>>>>>> main
                {
                    licenseKey: "GPL",
                    removePlugins: [
                        "CKBox",
                        "CKFinder",
                        "EasyImage",
                        "RealTimeCollaborativeComments",
                        "RealTimeCollaborativeTrackChanges",
                        "RealTimeCollaborativeRevisionHistory",
                        "PresenceList",
                        "Comments",
                        "TrackChanges",
                        "TrackChangesData",
                        "RevisionHistory",
                        "Pagination",
                        "WProofreader",
                        "MathType",
                        "SlashCommand",
                        "Template",
                        "DocumentOutline",
                        "FormatPainter",
                        "TableOfContents",
                        "PasteFromOfficeEnhanced",
                        "AIAssistant",
                        "MultiLevelList",
                        "CaseChange",
                    ],
                    toolbar: {
<<<<<<< HEAD
                        items: [
                            "heading",
                            "|",
                            "bold",
                            "italic",
                            "underline",
                            "|",
                            "bulletedList",
                            "numberedList",
                            "|",
                            "blockQuote",
                            "link",
                            "|",
                            "undo",
                            "redo",
=======
                        items: TOOLBAR,
                    },
                    alignment: {
                        options: ["left", "center", "right", "justify"],
                    },
                    fontSize: {
                        options: [8, 10, 12, 14, "default", 18, 24, 32, 48],
                    },
                    table: {
                        contentToolbar: [
                            "tableColumn",
                            "tableRow",
                            "mergeTableCells",
                            "tableProperties",
                            "tableCellProperties",
>>>>>>> main
                        ],
                    },
                    placeholder: "Enter content…",
                },
            );

<<<<<<< HEAD
            editors[field].model.document.on("change:data", () => {
                document.getElementById(field).value = editors[field].getData();
=======
            editors[id].model.document.on("change:data", () => {
                document.getElementById(id).value = editors[id].getData();
>>>>>>> main
            });
        }
    }

    /* ── Toast ──────────────────────────────────────────────────── */
    function showToast(type, title, msg) {
        const el = document.getElementById("glToast");
<<<<<<< HEAD
=======
        if (!el) {
            console.warn("Toast element #glToast not found in DOM");
            return;
        }
>>>>>>> main
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
        document
            .querySelectorAll(".gl-ck-wrap.is-invalid")
            .forEach((el) => el.classList.remove("is-invalid"));
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([field, msgs]) => {
            const msg = Array.isArray(msgs) ? msgs[0] : msgs;
            const err = document.getElementById(`err_${field}`);
            if (err) err.textContent = msg;
<<<<<<< HEAD
            if (CK_FIELDS.includes(field)) {
=======
            if (CK_FIELDS.some((f) => f.id === field)) {
>>>>>>> main
                document
                    .getElementById(`ck_${field}`)
                    ?.classList.add("is-invalid");
            } else {
                document.getElementById(field)?.classList.add("is-invalid");
            }
        });
    }

    /* ── Form helpers ───────────────────────────────────────────── */
<<<<<<< HEAD
    const TEXT_FIELDS = [
=======
    const PLAIN_FIELDS = [
>>>>>>> main
        "author_badge",
        "author_heading",
        "process_badge",
        "process_heading",
        "manuscript_badge",
        "manuscript_heading",
        "formatting_badge1",
        "formatting_badge2",
        "formatting_heading",
        "layout_badge1",
        "layout_heading",
        "acknowlegdement_badge1",
        "acknowlegdement_heading",
    ];

    function resetForm() {
        document.getElementById("glForm").reset();
        document.getElementById("glId").value = "";
        document.getElementById("glMethod").value = "POST";
<<<<<<< HEAD
        CK_FIELDS.forEach((f) => {
            if (editors[f]) editors[f].setData("");
            document.getElementById(f).value = "";
=======
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id]) editors[id].setData("");
            const ta = document.getElementById(id);
            if (ta) ta.value = "";
>>>>>>> main
        });
        clearErrors();
    }

    function fillForm(r) {
<<<<<<< HEAD
        TEXT_FIELDS.forEach((f) => {
            const el = document.getElementById(f);
            if (el) el.value = r[f] ?? "";
        });
        CK_FIELDS.forEach((f) => {
            if (editors[f]) editors[f].setData(r[f] ?? "");
            document.getElementById(f).value = r[f] ?? "";
=======
        PLAIN_FIELDS.forEach((f) => {
            const el = document.getElementById(f);
            if (el) el.value = r[f] ?? "";
        });
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id]) editors[id].setData(r[id] ?? "");
            const ta = document.getElementById(id);
            if (ta) ta.value = r[id] ?? "";
>>>>>>> main
        });
        document.getElementById("glId").value = r.id;
        document.getElementById("glMethod").value = "PUT";
    }

    function syncEditors() {
<<<<<<< HEAD
        CK_FIELDS.forEach((f) => {
            if (editors[f])
                document.getElementById(f).value = editors[f].getData();
=======
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id])
                document.getElementById(id).value = editors[id].getData();
>>>>>>> main
        });
    }

    document.getElementById("glSaveBtn").addEventListener("click", async () => {
        clearErrors();
        syncEditors();

        let hasError = false;
        CK_FIELDS.forEach((f) => {
<<<<<<< HEAD
            const val = editors[f] ? editors[f].getData() : "";
            if (!val.trim() || val === "<p>&nbsp;</p>") {
                document.getElementById(`ck_${f}`)?.classList.add("is-invalid");
                const errEl = document.getElementById(`err_${f}`);
                if (errEl) errEl.textContent = "This field is required.";
                hasError = true;
=======
            if (f.required) {
                const val = editors[f.id] ? editors[f.id].getData() : "";
                if (!val.trim() || val === "<p>&nbsp;</p>") {
                    document
                        .getElementById(`ck_${f.id}`)
                        ?.classList.add("is-invalid");
                    const errEl = document.getElementById(`err_${f.id}`);
                    if (errEl) errEl.textContent = "This field is required.";
                    hasError = true;
                }
>>>>>>> main
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
                headers: {
                    Authorization: `Bearer ${TOKEN}`,
                    Accept: "application/json",
                },
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

            showToast(
                "success",
                method === "PUT" ? "Updated!" : "Created!",
                json.message ?? "",
            );
            loadRecord();
        } catch (err) {
            showToast("error", "Request failed", err.message);
        } finally {
            spinner.classList.add("d-none");
            btnText.textContent = method === "PUT" ? "Update" : "Save";
        }
    });

    async function loadRecord() {
        document.getElementById("glLoading").classList.remove("d-none");
        document.getElementById("glFormContainer").classList.add("d-none");
        try {
            const res = await fetch(API_BASE, {
                headers: authHeaders(),
            });
            const json = await res.json();
            const raw = json.data;
            let record = null;
            if (raw) {
                if (Array.isArray(raw)) record = raw[0] ?? null;
                else if (Array.isArray(raw.data)) record = raw.data[0] ?? null;
                else if (raw.id) record = raw;
            }

            if (!editorsReady) {
                await initEditors();
                editorsReady = true;
            }

            if (record && record.id) {
                fillForm(record);
                document.getElementById("glSaveBtnText").textContent = "Update";
            } else {
                resetForm();
                document.getElementById("glSaveBtnText").textContent = "Save";
            }

            document.getElementById("glLoading").classList.add("d-none");
            document
                .getElementById("glFormContainer")
                .classList.remove("d-none");
        } catch (e) {
            document.getElementById("glLoading").classList.add("d-none");
            showToast("error", "Load failed", e.message);
        }
    }

    loadRecord();
<<<<<<< HEAD
});
=======
});
>>>>>>> main
