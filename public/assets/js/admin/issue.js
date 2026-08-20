const API_BASE = "/api/admin/issues";
const JOURNALS_API = "/api/admin/journals?page=1&per_page=100";
const VOLUMES_API = "/api/admin/volumes?page=1&per_page=200";
const token = localStorage.getItem("token");
let currentPage = 1;
let allVolumes = [];
let deleteIssueModal;
let pendingDeleteId = null;
let pendingDeleteName = null;

const CURRENT_YEAR = new Date().getFullYear();

// Local YYYY-MM-DD for "today", used as the max on the date input.
// (Avoids the UTC-shift bug you get from new Date().toISOString().)
function todayStr() {
    const d = new Date();
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const dd = String(d.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
}
const TODAY_STR = todayStr();

function authHeaders() {
    return {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        "Content-Type": "application/json",
    };
}

// Locks Year and Published Date inputs so neither can be set to the
// future — Year caps at the current year, Published Date caps at today.
// Called whenever the modal is opened (create/edit).
function restrictDatesToPresentOrPast() {
    const yearInput = document.getElementById("year");
    if (yearInput) yearInput.setAttribute("max", CURRENT_YEAR);

    const publishedInput = document.getElementById("published_date");
    if (publishedInput) publishedInput.setAttribute("max", TODAY_STR);
}

// ── Lightweight toast (no HTML dependency) ──────────────────────
function showToast(message, type = "success") {
    let container = document.getElementById("issueToastContainer");
    if (!container) {
        container = document.createElement("div");
        container.id = "issueToastContainer";
        container.style.cssText =
            "position:fixed;top:20px;right:20px;z-index:2000;display:flex;flex-direction:column;gap:10px;";
        document.body.appendChild(container);
    }

    const colors = {
        success: { bg: "#16a34a", text: "#fff" },
        danger: { bg: "#dc2626", text: "#fff" },
    };
    const c = colors[type] || colors.success;

    const toast = document.createElement("div");
    toast.style.cssText = `
        background:${c.bg};color:${c.text};padding:12px 18px;
        border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.15);
        font-size:14px;min-width:260px;opacity:0;
        transform:translateX(20px);transition:opacity .25s,transform .25s;
    `;
    toast.textContent = message;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = "1";
        toast.style.transform = "translateX(0)";
    });

    setTimeout(() => {
        toast.style.opacity = "0";
        toast.style.transform = "translateX(20px)";
        setTimeout(() => toast.remove(), 250);
    }, 3000);
}

async function loadJournalOptions(selectedId = null) {
    const res = await fetch(JOURNALS_API, {
        headers: authHeaders(),
    });
    const json = await res.json();
    const select = document.getElementById("journal_id");
    select.innerHTML = '<option value="">Select journal...</option>';

    (json.data?.data ?? []).forEach((j) => {
        const selected = selectedId == j.id ? "selected" : "";
        select.innerHTML += `<option value="${j.id}" ${selected}>${j.title}</option>`;
    });
}

async function loadAllVolumes() {
    const res = await fetch(VOLUMES_API, {
        headers: authHeaders(),
    });
    const json = await res.json();
    allVolumes = json.data?.data ?? [];
}

function loadVolumeOptions(journalId, selectedVolumeId = null) {
    const select = document.getElementById("volume_id");
    select.innerHTML = '<option value="">Select volume...</option>';

    const seen = new Set();

    allVolumes
        .filter((v) => v.journal_id == journalId)
        .filter((v) => {
            if (seen.has(v.volume)) return false;
            seen.add(v.volume);
            return true;
        })
        .forEach((v) => {
            const selected = selectedVolumeId == v.id ? "selected" : "";
            select.innerHTML += `<option value="${v.id}" ${selected}>${v.volume}</option>`;
        });

    // Keep Year in sync with whatever volume ends up selected (e.g. when
    // editing an issue and its volume is pre-selected above).
    syncYearFromVolume();
}

// An issue's year must match its volume's year, so once a volume is
// chosen, Year is auto-filled from it and locked (read-only) to prevent
// mismatches. With no volume selected, Year falls back to being a normal,
// manually-typed field (still capped at the current year).
function syncYearFromVolume() {
    const volumeSelect = document.getElementById("volume_id");
    const yearInput = document.getElementById("year");
    if (!volumeSelect || !yearInput) return;

    const volumeId = volumeSelect.value;
    const volume = allVolumes.find((v) => v.id == volumeId);

    if (volume) {
        yearInput.value = volume.year ?? "";
        yearInput.readOnly = true;
        yearInput.classList.add("bg-light");
    } else {
        yearInput.readOnly = false;
        yearInput.classList.remove("bg-light");
    }
}
async function loadIssues(page = 1) {
    currentPage = page;
    const res = await fetch(`${API_BASE}?page=${page}`, {
        headers: authHeaders(),
    });
    const json = await res.json();

    const tbody = document.getElementById("issue-table-body");
    tbody.innerHTML = "";

    if (!json.status || !json.data.data.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4">No issues found.</td></tr>`;
        return;
    }

    const perPage = json.data.per_page ?? json.data.data.length;
    const startSerial = (json.data.current_page - 1) * perPage + 1;

    json.data.data.forEach((i, index) => {
        const serialNo = startSerial + index;
        tbody.innerHTML += `
            <tr>
                <td>${serialNo}</td>
                <td>${i.journal?.title ?? "-"}</td>
                <td>${i.volume?.volume ?? "-"}</td>
                <td>${i.issue}</td>
                <td>${i.year ?? "-"}</td>
                <td>${i.published_date ?? "-"}</td>
                <td><span class="green-btn">${i.status}</span></td>
                <td>
                    ${
                        i.is_current
                            ? '<span class="green-btn">Current</span>'
                            : `<button class="edit-btn" onclick="toggleCurrent(${i.id})">Archived</button>`
                    }
                </td>
                <td>
                    <button class="edit-btn" onclick="viewIssue(${i.id})">View</button>
                    <button class="edit-btn" onclick="editIssue(${i.id})">Edit</button>
                    <button class="delete-btn" data-bs-toggle="modal" data-bs-target="#delete_popup"
                            onclick="promptDeleteIssue(${i.id}, '${i.issue}')">Delete</button>
                </td>
            </tr>`;
    });

    renderPagination(json.data);
}


function renderPagination(pageData) {
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";
    if (!pageData.last_page || pageData.last_page <= 1) return;

    for (let i = 1; i <= pageData.last_page; i++) {
        pagination.innerHTML += `
            <li class="page-item ${i === pageData.current_page ? "active" : ""}">
                <a class="page-link" href="#" onclick="loadIssues(${i}); return false;">${i}</a>
            </li>`;
    }
}

async function openCreateModal() {
    document.getElementById("issueForm").reset();
    document.getElementById("issue_id").value = "";
    document.getElementById("issueModalTitle").innerText = "Add Issue";
    restrictDatesToPresentOrPast();
    await loadJournalOptions();
    await loadAllVolumes();
    document.getElementById("volume_id").innerHTML =
        '<option value="">Select volume...</option>';
    syncYearFromVolume(); // no volume selected yet -> unlocks Year
    new bootstrap.Modal(document.getElementById("issueModal")).show();
}

async function editIssue(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
        headers: authHeaders(),
    });
    const json = await res.json();
    if (!json.status) {
        showToast(json.message ?? "Failed to load issue.", "danger");
        return;
    }

    const i = json.data;
    await loadJournalOptions(i.journal_id);
    await loadAllVolumes();
    loadVolumeOptions(i.journal_id, i.volume_id);

    restrictDatesToPresentOrPast();

    document.getElementById("issue_id").value = i.id;
    document.getElementById("issue").value = i.issue;
    document.getElementById("year").value = i.year ?? "";
    document.getElementById("published_date").value = i.published_date ?? "";
    document.getElementById("status").value = i.status;
    document.getElementById("is_current").checked = !!i.is_current;
    document.getElementById("issueModalTitle").innerText = "Edit Issue";

    new bootstrap.Modal(document.getElementById("issueModal")).show();
}

async function viewIssue(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
        headers: authHeaders(),
    });
    const json = await res.json();
    if (!json.status) {
        showToast(json.message ?? "Failed to load issue.", "danger");
        return;
    }

    const i = json.data;
    document.getElementById("viewModalBody").innerHTML = `
        <p><strong>Journal:</strong> ${i.journal?.title ?? "-"}</p>
        <p><strong>Volume:</strong> ${i.volume?.volume ?? "-"}</p>
        <p><strong>Issue:</strong> ${i.issue}</p>
        <p><strong>Year:</strong> ${i.year ?? "-"}</p>
        <p><strong>Published Date:</strong> ${i.published_date ?? "-"}</p>
        <p><strong>Status:</strong> ${i.status}</p>
        <p><strong>Current:</strong> ${i.is_current ? "Yes" : "No"}</p>
    `;
    new bootstrap.Modal(document.getElementById("viewModal")).show();
}

async function toggleCurrent(id) {
    const res = await fetch(`${API_BASE}/${id}/toggle-current`, {
        method: "PATCH",
        headers: authHeaders(),
    });
    const json = await res.json();
    if (json.status) {
        loadIssues(currentPage);
        showToast(json.message ?? "Status updated.", "success");
    } else {
        showToast(json.message ?? "Failed to update current issue.", "danger");
    }
}

// ── Delete ────────────────────────────────────────────────────
function promptDeleteIssue(id, name) {
    pendingDeleteId = id;
    pendingDeleteName = name;
    document.getElementById("deleteIssueName").textContent = name;
    deleteIssueModal.show();
}

async function executeDeleteIssue(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
        method: "DELETE",
        headers: authHeaders(),
    });
    const json = await res.json();
    if (json.status) {
        loadIssues(currentPage);
        showToast(json.message ?? "Issue deleted.", "success");
    } else {
        showToast(json.message ?? "Failed to delete issue.", "danger");
    }
}

document
    .getElementById("issueForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const yearValue = document.getElementById("year").value;
        const publishedDateValue =
            document.getElementById("published_date").value;

        // Hard guards so manually typed/pasted values can't slip past the
        // inputs' max attributes — only today/current-year or earlier.
        // Skip this check when Year is locked to the selected volume's
        // year (read-only), since that value isn't user-entered.
        const yearIsUserEditable = !document.getElementById("year").readOnly;
        if (
            yearIsUserEditable &&
            yearValue !== "" &&
            Number(yearValue) > CURRENT_YEAR
        ) {
            showToast(
                `Year cannot be a future year. Please choose ${CURRENT_YEAR} or earlier.`,
                "danger",
            );
            document.getElementById("year").focus();
            return;
        }

        if (publishedDateValue !== "" && publishedDateValue > TODAY_STR) {
            showToast(
                "Published Date cannot be a future date. Please choose today or an earlier date.",
                "danger",
            );
            document.getElementById("published_date").focus();
            return;
        }

        const id = document.getElementById("issue_id").value;

        const payload = {
            journal_id: document.getElementById("journal_id").value,
            volume_id: document.getElementById("volume_id").value,
            issue: document.getElementById("issue").value,
            year: yearValue,
            published_date: publishedDateValue,
            status: document.getElementById("status").value,
            is_current: document.getElementById("is_current").checked,
        };

        const url = id ? `${API_BASE}/${id}` : API_BASE;
        const method = id ? "PUT" : "POST";

        const res = await fetch(url, {
            method,
            headers: authHeaders(),
            body: JSON.stringify(payload),
        });

        const json = await res.json();

        if (json.status) {
            bootstrap.Modal.getInstance(
                document.getElementById("issueModal"),
            ).hide();
            loadIssues(currentPage);
            showToast(
                json.message ?? (id ? "Issue updated." : "Issue created."),
                "success",
            );
        } else {
            showToast(json.message ?? "Something went wrong.", "danger");
            console.error(json.errors);
        }
    });

document.addEventListener("DOMContentLoaded", function () {
    deleteIssueModal = new bootstrap.Modal(document.getElementById("delete_popup"));

    restrictDatesToPresentOrPast();

    const volumeSelect = document.getElementById("volume_id");
    if (volumeSelect) {
        volumeSelect.addEventListener("change", syncYearFromVolume);
    }

    document
        .getElementById("confirmDeleteIssueBtn")
        .addEventListener("click", function () {
            if (pendingDeleteId === null) return;
            deleteIssueModal.hide();
            executeDeleteIssue(pendingDeleteId);
            pendingDeleteId = null;
            pendingDeleteName = null;
        });
});

loadIssues();