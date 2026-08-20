const API = "/api/admin/journals";
const CSRF_TOKEN =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

let addJournalModal, deletePopupModal;
let isEditMode = false;
let pendingDeleteId = null;

let allJournals = [];
let filteredJournals = [];
let currentPage = 1;
let perPage = 10;

/* ─────────────────────────────────────────────────────────────
           CKEditor fields
        ───────────────────────────────────────────────────────────── */
const CK_FIELDS = [
    {
        id: "description",
        required: false,
    },
    {
        id: "aim_and_scope",
        required: false,
    },
];

const editors = {};
let editorsReady = false;

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
];

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

        editors[id].model.document.on("change:data", () => {
            document.getElementById(id).value = editors[id].getData();
        });
    }
}

function syncEditors() {
    CK_FIELDS.forEach(({ id }) => {
        if (editors[id])
            document.getElementById(id).value = editors[id].getData();
    });
}

// ── Boot ─────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", async function () {
    addJournalModal = new bootstrap.Modal(
        document.getElementById("AddJournal"),
    );
    deletePopupModal = new bootstrap.Modal(
        document.getElementById("delete_popup"),
    );

    perPage = parseInt(document.getElementById("perPage").value, 10);
    loadJournals();

    if (!editorsReady) {
        await initEditors();
        editorsReady = true;
    }

    document
        .getElementById("journalForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            saveJournal();
        });

    document
        .getElementById("addFieldBtn")
        .addEventListener("click", () => addFieldRow());

    document
        .getElementById("confirmDeleteBtn")
        .addEventListener("click", function () {
            if (pendingDeleteId === null) return;
            deletePopupModal.hide();
            executeDelete(pendingDeleteId);
            pendingDeleteId = null;
        });
});

// ── Toast ──────────────────────────────────────────────────────
function showToast(type, title, msg) {
    const el = document.getElementById("ecToast");
    if (!el) {
        console.warn("Toast element #ecToast not found in DOM");
        return;
    }
    document.getElementById("ecToastTitle").textContent = title;
    const msgEl = document.getElementById("ecToastMsg");
    msgEl.textContent = msg || "";
    msgEl.style.display = msg ? "block" : "none";
    document.getElementById("ecToastIcon").innerHTML =
        type === "success"
            ? `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
            : `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;

    el.classList.remove("bg-success", "bg-danger", "bg-warning", "text-dark");

    if (type === "success") el.classList.add("bg-success");
    else if (type === "warning") el.classList.add("bg-warning", "text-dark");
    else el.classList.add("bg-danger");
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

function showToastLegacy(message, type = "success") {
    const titles = {
        success: "Success",
        danger: "Error",
        warning: "Heads up",
    };
    showToast(
        type === "danger" ? "error" : type,
        titles[type] || "Notice",
        message,
    );
}

function formHeaders() {
    return {
        Accept: "application/json",
        "X-CSRF-TOKEN": CSRF_TOKEN,
    };
}

// ── Load journals ────────────────────────────────────────────
function loadJournals() {
    document.getElementById("tableLoading").style.display = "block";
    document.getElementById("tableEmpty").style.display = "none";
    document.getElementById("tableWrap").style.display = "none";

    fetch(API, {
        credentials: "include",
        headers: {
            Accept: "application/json",
        },
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            allJournals =
                data.data && data.data.data ? data.data.data : data.data || [];
            applyFilterAndRender();
            updateStats();
        })
        .catch((err) => {
            document.getElementById("tableLoading").style.display = "none";
            if (err.message === "Not authenticated") return;
            showToastLegacy("Failed to load journals.", "danger");
        });
}

function updateStats() {
    const total = allJournals.length;
    const active = allJournals.filter((j) => !!j.is_active).length;
    const inactive = total - active;

    document.getElementById("statTotal").textContent = `${total} Journals`;
    document.getElementById("statActive").textContent = `${active} Journals`;
    document.getElementById("statInactive").textContent =
        `${inactive} Journals`;
}

// ── Search + Pagination ──────────────────────────────────────
function onSearchInput() {
    currentPage = 1;
    applyFilterAndRender();
}

function onPerPageChange() {
    perPage = parseInt(document.getElementById("perPage").value, 10);
    currentPage = 1;
    applyFilterAndRender();
}

function applyFilterAndRender() {
    const query = document
        .getElementById("searchInput")
        .value.trim()
        .toLowerCase();
    filteredJournals = query
        ? allJournals.filter((j) =>
              (j.title || "").toLowerCase().includes(query),
          )
        : allJournals;

    document.getElementById("tableLoading").style.display = "none";
    renderTable();
    renderPagination();
}

function renderTable() {
    const tbody = document.getElementById("journalTableBody");

    if (!filteredJournals.length) {
        document.getElementById("tableEmpty").style.display = "block";
        document.getElementById("tableWrap").style.display = "none";
        document.getElementById("entriesInfo").textContent =
            "Showing 0 to 0 of 0 entries";
        tbody.innerHTML = "";
        return;
    }

    document.getElementById("tableEmpty").style.display = "none";
    document.getElementById("tableWrap").style.display = "block";

    const totalPages = Math.max(
        1,
        Math.ceil(filteredJournals.length / perPage),
    );
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * perPage;
    const pageItems = filteredJournals.slice(start, start + perPage);

    let rows = "";
    pageItems.forEach((j) => {
        const cover = j.cover_image
            ? `<img src="/images/${j.cover_image}" class="table-cover-journal-image" alt="">`
            : `<div class="table-cover-journal-image--empty">N/A</div>`;

        const issn = j.e_issn || j.p_issn || j.issn_online || "-";
        const volIssue =
            j.volume || j.issue
                ? `Vol ${j.volume ?? "-"} / Issue ${j.issue ?? "-"}`
                : "-";

        rows += `
                <tr>
                    <td>${cover}</td>
                    <td><strong>${escapeHtml(j.title || "")}</strong></td>
                    <td>${escapeHtml(j.abbreviation || "-")}</td>
                    <td>${escapeHtml(issn)}</td>
                    <td>${escapeHtml(volIssue)}</td>
                    <td>${j.sequence ?? 0}</td>
                    <td>
                        <button class="green-btn" style="color:${j.is_active ? "white" : "White"};"
                                onclick="toggleStatus(${j.id})">
                            ${j.is_active ? "Active" : "Inactive"}
                        </button>
                    </td>
                    <td>
                        <button class="edit-btn" onclick="openEditModal(${j.id})">Edit</button>
                        <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#delete_popup"
                                onclick="promptDelete(${j.id}, '${escAttr(j.title || "")}')">Delete</button>
                    </td>
                </tr>`;
    });

    tbody.innerHTML = rows;
    document.getElementById("entriesInfo").textContent =
        `Showing ${start + 1} to ${Math.min(start + perPage, filteredJournals.length)} of ${filteredJournals.length} entries`;
}

function renderPagination() {
    const totalPages = Math.max(
        1,
        Math.ceil(filteredJournals.length / perPage),
    );
    const ul = document.getElementById("pagination");
    let html = "";

    html += `<li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">Previous</a>
                 </li>`;
    for (let p = 1; p <= totalPages; p++) {
        html += `<li class="page-item ${p === currentPage ? "active" : ""}">
                        <a class="page-link" href="#" onclick="goToPage(${p}); return false;">${p}</a>
                     </li>`;
    }
    html += `<li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">Next</a>
                 </li>`;

    ul.innerHTML = html;
}

function goToPage(page) {
    const totalPages = Math.max(
        1,
        Math.ceil(filteredJournals.length / perPage),
    );
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
    renderPagination();
}

// ── Fields Covered repeater ──────────────────────────────────
function addFieldRow(value) {
    value = value || "";
    const container = document.getElementById("fieldsCoveredContainer");
    const row = document.createElement("div");
    row.className = "journal-fileds";
    row.innerHTML = `
            <input type="text" class="form-control field-covered-input" placeholder="e.g. Computer Science" value="${escAttr(value)}">
            <button type="button" class="edit-btn" onclick="this.closest('.journal-fileds').remove()">✕</button>`;
    container.appendChild(row);
}

function collectFieldsCovered() {
    return Array.from(document.querySelectorAll(".field-covered-input"))
        .map((i) => i.value.trim())
        .filter((v) => v.length > 0);
}

// ── Create / Edit ─────────────────────────────────────────────
function openCreateModal() {
    isEditMode = false;
    document.getElementById("journalForm").reset();
    document.getElementById("journal_id").value = "";
    document.getElementById("journalModalTitle").textContent = "Add Journal";
    document.getElementById("saveJournalBtnText").textContent =
        "Create Journal";
    document.getElementById("coverPreviewCurrent").style.display = "none";
    document.getElementById("is_active").value = "1";
    document.getElementById("sequence").value = "0";

    CK_FIELDS.forEach(({ id }) => {
        if (editors[id]) editors[id].setData("");
    });

    document.getElementById("fieldsCoveredContainer").innerHTML = "";
    addFieldRow();

    addJournalModal.show();
}

function openEditModal(id) {
    const journal = allJournals.find((j) => j.id === id);
    if (!journal) {
        showToastLegacy("Journal not found.", "danger");
        return;
    }

    isEditMode = true;
    document.getElementById("journal_id").value = journal.id;
    document.getElementById("journalModalTitle").textContent = "Edit Journal";
    document.getElementById("saveJournalBtnText").textContent =
        "Update Journal";

    const fields = [
        "title",
        "abbreviation",
        "badge",
        "e_issn",
        "p_issn",
        "issn_online",
        "volume",
        "issue",
        "latest_volume",
        "publication_language",
        "publishing_frequency",
        "publishing_months",
        "indexing_impact_factor",
        "time_to_first_decision",
        "time_to_review",
        "acceptance_to_publication",
        "article_template_url",
        "sequence",
        "aim_and_scope_title",
        "view_all_issues_label",
        "view_all_issues_link",
        "explore_journals_label",
        "explore_journals_link",
    ];

    fields.forEach((f) => {
        const el = document.getElementById(f);
        if (el) el.value = journal[f] ?? "";
    });

    document.getElementById("heading_1").value =
        journal.title_2 || journal.heading_1 || "";
    document.getElementById("is_active").value = journal.is_active ? "1" : "0";

    CK_FIELDS.forEach(({ id }) => {
        if (editors[id]) editors[id].setData(journal[id] ?? "");
    });

    document.getElementById("coverPreviewCurrent").style.display = "none";
    if (journal.cover_image) {
        document.getElementById("coverPreviewCurrent").src =
            `/images/${journal.cover_image}`;
        document.getElementById("coverPreviewCurrent").style.display = "block";
    }

    document.getElementById("fieldsCoveredContainer").innerHTML = "";
    const fc = journal.fields_covered || [];
    fc.length ? fc.forEach((f) => addFieldRow(f)) : addFieldRow();

    addJournalModal.show();
}

// ── Save ───────────────────────────────────────────────────────
function saveJournal() {
    syncEditors();

    const id = document.getElementById("journal_id").value;

    const formData = new FormData();
    formData.append("title", document.getElementById("title").value.trim());
    formData.append(
        "heading_1",
        document.getElementById("heading_1").value.trim(),
    );
    formData.append(
        "description",
        document.getElementById("description").value,
    );
    formData.append(
        "abbreviation",
        document.getElementById("abbreviation").value.trim(),
    );
    formData.append("badge", document.getElementById("badge").value.trim());
    formData.append("e_issn", document.getElementById("e_issn").value.trim());
    formData.append("p_issn", document.getElementById("p_issn").value.trim());
    formData.append(
        "issn_online",
        document.getElementById("issn_online").value.trim(),
    );
    formData.append("volume", document.getElementById("volume").value.trim());
    formData.append("issue", document.getElementById("issue").value.trim());
    formData.append(
        "latest_volume",
        document.getElementById("latest_volume").value.trim(),
    );
    formData.append(
        "publication_language",
        document.getElementById("publication_language").value.trim(),
    );
    formData.append(
        "publishing_frequency",
        document.getElementById("publishing_frequency").value.trim(),
    );
    formData.append(
        "publishing_months",
        document.getElementById("publishing_months").value.trim(),
    );
    formData.append(
        "indexing_impact_factor",
        document.getElementById("indexing_impact_factor").value.trim(),
    );
    formData.append(
        "time_to_first_decision",
        document.getElementById("time_to_first_decision").value.trim(),
    );
    formData.append(
        "time_to_review",
        document.getElementById("time_to_review").value.trim(),
    );
    formData.append(
        "acceptance_to_publication",
        document.getElementById("acceptance_to_publication").value.trim(),
    );
    formData.append(
        "article_template_url",
        document.getElementById("article_template_url").value.trim(),
    );
    formData.append(
        "aim_and_scope_title",
        document.getElementById("aim_and_scope_title").value.trim(),
    );
    formData.append(
        "aim_and_scope",
        document.getElementById("aim_and_scope").value,
    );
    formData.append(
        "view_all_issues_label",
        document.getElementById("view_all_issues_label").value.trim(),
    );
    formData.append(
        "view_all_issues_link",
        document.getElementById("view_all_issues_link").value.trim(),
    );
    formData.append(
        "explore_journals_label",
        document.getElementById("explore_journals_label").value.trim(),
    );
    formData.append(
        "explore_journals_link",
        document.getElementById("explore_journals_link").value.trim(),
    );
    formData.append("sequence", document.getElementById("sequence").value || 0);
    formData.append("is_active", document.getElementById("is_active").value);

    collectFieldsCovered().forEach((f) =>
        formData.append("fields_covered[]", f),
    );

    const fileInput = document.getElementById("cover_image");
    if (fileInput.files.length)
        formData.append("cover_image", fileInput.files[0]);

    let url = API;
    if (isEditMode) {
        url = `${API}/${id}`;
        formData.append("_method", "PUT");
    }

    const btn = document.getElementById("saveJournalBtn");
    const btnText = document.getElementById("saveJournalBtnText");
    btn.disabled = true;
    btnText.textContent = isEditMode ? "Updating…" : "Saving…";

    fetch(url, {
        method: "POST",
        credentials: "include",
        headers: formHeaders(),
        body: formData,
    })
        .then(handleAuthErrors)
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    showToastLegacy(
                        Object.values(data.errors)
                            .map((e) => e[0])
                            .join(" | "),
                        "danger",
                    );
                    throw new Error("Validation failed");
                }
                throw new Error(data.message || "Save failed");
            }
            return data;
        })
        .then((data) => {
            loadJournals();
            addJournalModal.hide();
            showToastLegacy(
                data.message ||
                    (isEditMode ? "Journal updated." : "Journal created."),
                "success",
            );
        })
        .catch((err) => {
            if (
                err.message !== "Validation failed" &&
                err.message !== "Not authenticated"
            ) {
                showToastLegacy(err.message, "danger");
            }
        })
        .finally(() => {
            btn.disabled = false;
            btnText.textContent = isEditMode
                ? "Update Journal"
                : "Create Journal";
        });
}

// ── Toggle status ────────────────────────────────────────────
function toggleStatus(id) {
    fetch(`${API}/${id}/toggle`, {
        method: "PATCH",
        credentials: "include",
        headers: formHeaders(),
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            loadJournals();
            showToastLegacy(data.message || "Status updated.", "success");
        })
        .catch((err) => {
            if (err.message !== "Not authenticated")
                showToastLegacy(err.message || "Failed.", "danger");
        });
}

// ── Delete ────────────────────────────────────────────────────
function promptDelete(id, title) {
    pendingDeleteId = id;
    document.getElementById("deleteJournalName").textContent = title;
    deletePopupModal.show();
}

function executeDelete(id) {
    fetch(`${API}/${id}`, {
        method: "DELETE",
        credentials: "include",
        headers: formHeaders(),
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            loadJournals();
            showToastLegacy(
                data.message || "The journal has been deleted.",
                "success",
            );
        })
        .catch((err) => {
            if (err.message !== "Not authenticated")
                showToastLegacy(err.message || "Delete failed.", "danger");
        });
}

// ── Auth errors ───────────────────────────────────────────────
function handleAuthErrors(res) {
    if (res.status === 401) {
        showToastLegacy("Session expired. Redirecting...", "warning");
        setTimeout(() => {
            window.location.href = "/login";
        }, 1200);
        throw new Error("Not authenticated");
    }
    if (res.status === 419) {
        showToastLegacy("CSRF expired. Reloading...", "warning");
        setTimeout(() => window.location.reload(), 1500);
        throw new Error("CSRF token expired");
    }
    return res;
}

// ── Helpers ───────────────────────────────────────────────────
function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str == null ? "" : str;
    return div.innerHTML;
}

function escAttr(s) {
    return String(s)
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}