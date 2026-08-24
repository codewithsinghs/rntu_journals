const API_BASE = "/api/admin/volumes";
const JOURNALS_API = "/api/admin/journals?page=1&per_page=100";
const token = localStorage.getItem("token");
let currentPage = 1;
let deleteVolumeModal;
let pendingDeleteId = null;
let pendingDeleteName = null;

const CURRENT_YEAR = new Date().getFullYear();

const YEAR_RANGE_BACK = 50;

function formatDate(value) {
    if (!value) return "-";
    const d = new Date(value);
    if (isNaN(d.getTime())) return value; // fallback: show raw value if unparseable
    return d.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function authHeaders() {
    return {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        "Content-Type": "application/json",
    };
}


function populateYearOptions(selectedYear = null) {
    const yearSelect = document.getElementById("year");
    if (!yearSelect) return;

    const minYear = CURRENT_YEAR - YEAR_RANGE_BACK;
    let options = '<option value="">Select year...</option>';
    for (let y = CURRENT_YEAR; y >= minYear; y--) {
        const selected = selectedYear != null && String(selectedYear) === String(y)
            ? "selected"
            : "";
        options += `<option value="${y}" ${selected}>${y}</option>`;
    }

    if (
        selectedYear != null &&
        selectedYear !== "" &&
        (Number(selectedYear) < minYear || Number(selectedYear) > CURRENT_YEAR)
    ) {
        options += `<option value="${selectedYear}" selected>${selectedYear} (outside range)</option>`;
    }

    yearSelect.innerHTML = options;
}

function showToast(message, type = "success", title = null) {
    const el = document.getElementById("ecToast");
    if (!el) {
        console.warn("Toast element #ecToast not found in DOM");
        return;
    }

    const defaultTitle = type === "success" ? "Success" : "Error";
    document.getElementById("ecToastTitle").textContent = title ?? defaultTitle;

    const msgEl = document.getElementById("ecToastMsg");
    msgEl.textContent = message || "";
    msgEl.style.display = message ? "block" : "none";

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

async function loadVolumes(page = 1) {
    currentPage = page;
    const res = await fetch(`${API_BASE}?page=${page}`, {
        headers: authHeaders(),
    });
    const json = await res.json();

    const tbody = document.getElementById("volume-table-body");
    tbody.innerHTML = "";

    if (!json.status || !json.data.data.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4">No volumes found.</td></tr>`;
        return;
    }

    const perPage = json.data.per_page ?? json.data.data.length;
    const startSerial = (json.data.current_page - 1) * perPage + 1;

    json.data.data.forEach((v, index) => {
        const serialNo = startSerial + index;
        tbody.innerHTML += `
            <tr>
                <td>${serialNo}</td>
                <td>${v.journal?.title ?? "-"}</td>
                <td>${v.volume}</td>
                <td>${v.year ?? "-"}</td>
                <td>${formatDate(v.published_date)}</td>
                <td><span class="green-btn">${v.status}</span></td>
                <td>
                    ${
                        v.is_current
                            ? '<span class="green-btn">Current</span>'
                            : `<button class="edit-btn" onclick="toggleCurrent(${v.id})">Archieved</button>`
                    }
                </td>
                <td>
                    <button class="edit-btn" onclick="viewVolume(${v.id})">View</button>
                    <button class="edit-btn" onclick="editVolume(${v.id})">Edit</button>
                    <button class="delete-btn" data-bs-toggle="modal" data-bs-target="#delete_popup"
                            onclick="promptDeleteVolume(${v.id}, '${v.volume}')">Delete</button>
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
                <a class="page-link" href="#" onclick="loadVolumes(${i}); return false;">${i}</a>
            </li>`;
    }
}

async function openCreateModal() {
    document.getElementById("volumeForm").reset();
    document.getElementById("volume_id").value = "";
    document.getElementById("volumeModalTitle").innerText = "Add Volume";
    populateYearOptions();
    await loadJournalOptions();
    new bootstrap.Modal(document.getElementById("volumeModal")).show();
}

async function editVolume(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
        headers: authHeaders(),
    });
    const json = await res.json();
    if (!json.status)
        return showToast(json.message ?? "Failed to load volume.", "error");

    const v = json.data;
    await loadJournalOptions(v.journal_id);

    document.getElementById("volume_id").value = v.id;
    document.getElementById("volume").value = v.volume;
    populateYearOptions(v.year);
    document.getElementById("published_date").value = v.published_date
        ? v.published_date.substring(0, 10)
        : "";
    document.getElementById("status").value = v.status;
    document.getElementById("is_current").checked = !!v.is_current;
    document.getElementById("volumeModalTitle").innerText = "Edit Volume";

    new bootstrap.Modal(document.getElementById("volumeModal")).show();
}

async function viewVolume(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
        headers: authHeaders(),
    });
    const json = await res.json();
    if (!json.status)
        return showToast(json.message ?? "Failed to load volume.", "error");

    const v = json.data;

    document.getElementById("viewModalBody").innerHTML = `
        <p><strong>Journal:</strong> ${v.journal?.title ?? "-"}</p>
        <p><strong>Volume:</strong> ${v.volume}</p>
        <p><strong>Year:</strong> ${v.year ?? "-"}</p>
        <p><strong>Published Date:</strong> ${formatDate(v.published_date)}</p>
        <p><strong>Status:</strong> ${v.status}</p>
        <p><strong>Current:</strong> ${v.is_current ? "Yes" : "No"}</p>
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
        showToast(json.message ?? "Marked as current successfully.", "success");
        loadVolumes(currentPage);
    } else {
        showToast(json.message ?? "Failed to update current volume.", "error");
    }
}

// ── Delete ────────────────────────────────────────────────────
function promptDeleteVolume(id, name) {
    pendingDeleteId = id;
    pendingDeleteName = name;
    document.getElementById("deleteVolumeName").textContent = name;
    deleteVolumeModal.show();
}

async function executeDeleteVolume(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
        method: "DELETE",
        headers: authHeaders(),
    });
    const json = await res.json();
    if (json.status) {
        showToast(json.message ?? "Volume deleted successfully.", "success");
        loadVolumes(currentPage);
    } else {
        showToast(json.message ?? "Failed to delete volume.", "error");
    }
}

document
    .getElementById("volumeForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const id = document.getElementById("volume_id").value;
        const yearValue = document.getElementById("year").value;

        // Year is now a dropdown that only ever renders current-year-or-past
        // options, so this is just a defense-in-depth check against DOM
        // tampering / a stray value, not the primary safeguard anymore.
        if (yearValue !== "" && Number(yearValue) > CURRENT_YEAR) {
            showToast(
                `Year cannot be a future year. Please choose ${CURRENT_YEAR} or earlier.`,
                "error",
            );
            document.getElementById("year").focus();
            return;
        }

        const payload = {
            journal_id: document.getElementById("journal_id").value,
            volume: document.getElementById("volume").value,
            year: yearValue,
            published_date: document.getElementById("published_date").value || null,
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
                document.getElementById("volumeModal"),
            ).hide();
            showToast(json.message ?? "Volume saved successfully.", "success");
            loadVolumes(currentPage);
        } else {
            showToast(json.message ?? "Something went wrong.", "error");
            console.error(json.errors);
        }
    });

document.addEventListener("DOMContentLoaded", function () {
    deleteVolumeModal = new bootstrap.Modal(document.getElementById("delete_popup"));

    populateYearOptions();

    document
        .getElementById("confirmDeleteVolumeBtn")
        .addEventListener("click", function () {
            if (pendingDeleteId === null) return;
            deleteVolumeModal.hide();
            executeDeleteVolume(pendingDeleteId);
            pendingDeleteId = null;
            pendingDeleteName = null;
        });
});

loadVolumes();