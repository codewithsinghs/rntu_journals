document.addEventListener("DOMContentLoaded", function () {
<<<<<<< HEAD
    document.addEventListener(
        "keydown",
        function (e) {
            if (e.code === "Space") {
                console.log(
                    "SPACE seen. defaultPrevented so far:",
                    e.defaultPrevented,
                    "target:",
                    e.target.tagName,
                    e.target.id || e.target.className,
                );
            }
        },
        true,
    );

=======
>>>>>>> main
    const API_BASE = "/api/admin/home-content";
    const TOKEN = localStorage.getItem("jwt_token") || "";

    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    });

    const CK_FIELDS = [
        {
            id: "aim_and_scope_description",
            required: true,
        },
        {
            id: "scope_of_publication_description",
            required: true,
        },
        {
            id: "university_highlight_quote",
            required: false,
        },
        {
            id: "support_section_description",
            required: true,
        },
        {
            id: "latest_journal_description",
            required: true,
        },
        {
            id: "footer_about_description",
            required: true,
        },
    ];

    const editors = {};
    let editorsReady = false;
<<<<<<< HEAD
    let pendingFill = null;
    let cachedRecord = null;
    const formModalEl = document.getElementById("formModal");

    const TOOLBAR = [
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

    async function initEditors() {
        for (const { id } of CK_FIELDS) {
            if (editors[id]) {
                await editors[id].destroy();
                delete editors[id];
            }
<<<<<<< HEAD
=======

>>>>>>> main
            editors[id] = await CKEDITOR.ClassicEditor.create(
                document.getElementById(`ck_${id}`),
                {
                    licenseKey: "GPL",
<<<<<<< HEAD
=======

>>>>>>> main
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
<<<<<<< HEAD
                        "RestrictedEditingMode",
                        "RestrictedEditingModeEditing",
                        "RestrictedEditingModeUI",
                    ],
                    toolbar: {
                        items: TOOLBAR,
                    },
                    placeholder: "Enter content…",
                },
            );
=======
                    ],

                    toolbar: {
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
                        ],
                    },

                    placeholder: "Enter content…",
                },
            );

>>>>>>> main
            editors[id].model.document.on("change:data", () => {
                document.getElementById(id).value = editors[id].getData();
            });
        }
    }

<<<<<<< HEAD
    /*  Plain (non-CK) form fields */
=======
    /* ─────────────────────────────────────────────────────────────
               Plain (non-CK) text fields
            ───────────────────────────────────────────────────────────── */
>>>>>>> main
    const PLAIN_FIELDS = [
        "aim_and_scope_title_1",
        "aim_and_scope_title_2",
        "aim_and_scope_title_3",
        "why_rntu_title_1",
        "why_rntu_title_2",
        "why_rntu_years",
        "why_rntu_years_label",
        "why_rntu_articles",
        "why_rntu_articles_label",
        "why_rntu_journals",
        "why_rntu_journals_label",
        "why_rntu_readers",
        "why_rntu_readers_label",
        "why_rntu_access",
        "why_rntu_access_label",
        "support_section_heading",
        "support_articles_count",
        "support_short_heading",
        "latest_journal_title",
        "latest_journal_heading",
    ];

<<<<<<< HEAD
    /*  Toast */
    function showToast(type, title, msg) {
        const el = document.getElementById("ecToast");
        if (!el) return;
=======
    /* ─────────────────────────────────────────────────────────────
               Image preview helpers
            ───────────────────────────────────────────────────────────── */
    const IMAGE_FIELDS = [
        {
            input: "aim_section_image",
            preview: "currentImagePreview",
            thumb: "currentImageThumb",
        },
    ];

    function showImagePreview(previewId, thumbId, url) {
        if (url) {
            document.getElementById(thumbId).src = url;
            document.getElementById(previewId).classList.remove("d-none");
        } else {
            document.getElementById(previewId).classList.add("d-none");
            document.getElementById(thumbId).src = "";
        }
    }

    /* ─────────────────────────────────────────────────────────────
               Toast
            ───────────────────────────────────────────────────────────── */
    function ensureToastEl() {
        let el = document.getElementById("ecToast");
        if (el) return el;

        let container = document.getElementById("ecToastContainer");
        if (!container) {
            container = document.createElement("div");
            container.id = "ecToastContainer";
            container.className =
                "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
        }

        el = document.createElement("div");
        el.id = "ecToast";
        el.className = "toast align-items-center border-0 text-white";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-start gap-2">
                    <span id="ecToastIcon" class="flex-shrink-0"></span>
                    <div>
                        <div id="ecToastTitle" class="fw-semibold"></div>
                        <div id="ecToastMsg" class="small"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div style="height:3px;background:rgba(255,255,255,.25);">
                <div id="ecToastBarInner" style="height:100%;background:rgba(255,255,255,.8);width:100%;"></div>
            </div>
        `;
        container.appendChild(el);
        return el;
    }

    function showToast(type, title, msg) {
        const el = ensureToastEl();
>>>>>>> main
        document.getElementById("ecToastTitle").textContent = title;
        const msgEl = document.getElementById("ecToastMsg");
        msgEl.textContent = msg || "";
        msgEl.style.display = msg ? "block" : "none";
        document.getElementById("ecToastIcon").innerHTML =
            type === "success"
                ? `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
                : `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
        el.classList.remove("bg-success", "bg-danger");
        el.classList.add(type === "success" ? "bg-success" : "bg-danger");
        const bar = document.getElementById("ecToastBarInner");
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

<<<<<<< HEAD
    /* Error helpers */
=======
    /* ─────────────────────────────────────────────────────────────
               Error helpers
            ───────────────────────────────────────────────────────────── */
>>>>>>> main
    function clearErrors() {
        document.querySelectorAll('[id^="err_"]').forEach((el) => {
            el.textContent = "";
        });
        document
            .querySelectorAll(".is-invalid")
            .forEach((el) => el.classList.remove("is-invalid"));
        document
            .querySelectorAll(".ckeditor-wrapper.is-invalid")
            .forEach((el) => el.classList.remove("is-invalid"));
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([field, msgs]) => {
            const msg = Array.isArray(msgs) ? msgs[0] : msgs;
            const errEl = document.getElementById(`err_${field}`);
            if (errEl) errEl.textContent = msg;
<<<<<<< HEAD
            if (editors[field]) {
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

<<<<<<< HEAD
    /*  Form helpers */
=======
    /* ─────────────────────────────────────────────────────────────
               Form helpers
            ───────────────────────────────────────────────────────────── */
>>>>>>> main
    function resetForm() {
        document.getElementById("hbcForm").reset();
        document.getElementById("hbcId").value = "";
        document.getElementById("hbcMethod").value = "POST";
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id]) editors[id].setData("");
            const ta = document.getElementById(id);
            if (ta) ta.value = "";
        });
<<<<<<< HEAD
        document.getElementById("currentImagePreview").classList.add("d-none");
        document.getElementById("currentImageThumb").src = "";
=======
        IMAGE_FIELDS.forEach(({ preview, thumb }) =>
            showImagePreview(preview, thumb, null),
        );
>>>>>>> main
        clearErrors();
    }

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
        document.getElementById("hbcId").value = r.id;
        document.getElementById("hbcMethod").value = "PUT";
<<<<<<< HEAD
        const imgUrl = r.aim_section_image_url || r.aim_section_image || null;
        if (imgUrl) {
            document.getElementById("currentImageThumb").src = imgUrl;
            document
                .getElementById("currentImagePreview")
                .classList.remove("d-none");
        } else {
            document
                .getElementById("currentImagePreview")
                .classList.add("d-none");
        }
=======
        showImagePreview(
            "currentImagePreview",
            "currentImageThumb",
            r.aim_section_image_url || r.aim_section_image || null,
        );
>>>>>>> main
    }

    function syncEditors() {
        CK_FIELDS.forEach(({ id }) => {
            if (editors[id])
                document.getElementById(id).value = editors[id].getData();
        });
    }

<<<<<<< HEAD
    /* Render helpers */
    function esc(s) {
        if (!s) return "";
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function htmlOrDash(s) {
        return (
            s ||
            '<span style="color:#adb5bd;font-style:italic;font-size:12px;">—</span>'
        );
    }

=======
>>>>>>> main
    function extractRecord(json) {
        const raw = json.data;
        if (!raw) return null;
        if (Array.isArray(raw)) return raw[0] ?? null;
        if (Array.isArray(raw.data)) return raw.data[0] ?? null;
        if (raw.id) return raw;
        return null;
    }

<<<<<<< HEAD
    /* Load */

    document
        .getElementById("hbcSaveBtn")
        .addEventListener("click", async () => {
=======
    /* ─────────────────────────────────────────────────────────────
               Save / Update
            ───────────────────────────────────────────────────────────── */
    document
        .getElementById("hbcSaveBtn")
        .addEventListener("click", async (e) => {
            e.preventDefault();
>>>>>>> main
            clearErrors();
            syncEditors();

            let hasError = false;
            CK_FIELDS.forEach((f) => {
                if (f.required) {
                    const val = editors[f.id] ? editors[f.id].getData() : "";
                    if (!val.trim() || val === "<p>&nbsp;</p>") {
                        document
                            .getElementById(`ck_${f.id}`)
                            ?.classList.add("is-invalid");
                        const errEl = document.getElementById(`err_${f.id}`);
                        if (errEl)
                            errEl.textContent = "This field is required.";
                        hasError = true;
                    }
                }
            });
            if (hasError) return;

            const id = document.getElementById("hbcId").value;
            const method = document.getElementById("hbcMethod").value;
            const spinner = document.getElementById("hbcSaveSpinner");
            const btnText = document.getElementById("hbcSaveBtnText");

            spinner.classList.remove("d-none");
            btnText.textContent = method === "PUT" ? "Updating…" : "Saving…";

            const formData = new FormData(document.getElementById("hbcForm"));
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

<<<<<<< HEAD
=======
    /* ─────────────────────────────────────────────────────────────
               Load
            ───────────────────────────────────────────────────────────── */
>>>>>>> main
    async function loadRecord() {
        document.getElementById("hbcLoading").classList.remove("d-none");
        document.getElementById("hbcFormContainer").classList.add("d-none");
        try {
            const res = await fetch(API_BASE, {
                headers: authHeaders(),
            });
            const json = await res.json();
<<<<<<< HEAD
            const raw = json.data;
            let record = null;
            if (raw) {
                if (Array.isArray(raw)) record = raw[0] ?? null;
                else if (Array.isArray(raw.data)) record = raw.data[0] ?? null;
                else if (raw.id) record = raw;
            }
=======
            const record = extractRecord(json);
>>>>>>> main

            if (!editorsReady) {
                await initEditors();
                editorsReady = true;
            }

            if (record && record.id) {
                fillForm(record);
                document.getElementById("hbcSaveBtnText").textContent =
                    "Update";
            } else {
                resetForm();
                document.getElementById("hbcSaveBtnText").textContent = "Save";
            }

            document.getElementById("hbcLoading").classList.add("d-none");
            document
                .getElementById("hbcFormContainer")
                .classList.remove("d-none");
        } catch (e) {
            document.getElementById("hbcLoading").classList.add("d-none");
            showToast("error", "Load failed", e.message);
        }
    }

    loadRecord();
<<<<<<< HEAD
});
=======
});
>>>>>>> main
