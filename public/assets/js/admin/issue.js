const API_BASE = "/api/admin/issues";
const JOURNALS_API = "/api/admin/journals?page=1&per_page=100";
const VOLUMES_API = "/api/admin/volumes?page=1&per_page=200";
const token = localStorage.getItem("token");
let currentPage = 1;
let allVolumes = [];

function authHeaders() {
    return {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        "Content-Type": "application/json",
    };
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

    json.data.data.forEach((i) => {
        tbody.innerHTML += `
            <tr>
                <td>${i.id}</td>
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
                    <button class="delete-btn" onclick="deleteIssue(${i.id}, '${i.issue}')">Delete</button>
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
    await loadJournalOptions();
    await loadAllVolumes();
    document.getElementById("volume_id").innerHTML =
        '<option value="">Select volume...</option>';
    new bootstrap.Modal(document.getElementById("issueModal")).show();
}

async function editIssue(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
        headers: authHeaders(),
    });
    const json = await res.json();
    if (!json.status) return alert("Failed to load issue.");

    const i = json.data;
    await loadJournalOptions(i.journal_id);
    await loadAllVolumes();
    loadVolumeOptions(i.journal_id, i.volume_id);

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
    if (!json.status) return alert("Failed to load issue.");

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
    if (json.status) loadIssues(currentPage);
    else alert(json.message ?? "Failed to update current issue.");
}

async function deleteIssue(id, name) {
    if (!confirm(`Delete issue "${name}"? This cannot be undone.`)) return;

    const res = await fetch(`${API_BASE}/${id}`, {
        method: "DELETE",
        headers: authHeaders(),
    });
    const json = await res.json();
    if (json.status) loadIssues(currentPage);
    else alert(json.message ?? "Failed to delete issue.");
}

document
    .getElementById("issueForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const id = document.getElementById("issue_id").value;

        const payload = {
            journal_id: document.getElementById("journal_id").value,
            volume_id: document.getElementById("volume_id").value,
            issue: document.getElementById("issue").value,
            year: document.getElementById("year").value,
            published_date: document.getElementById("published_date").value,
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
        } else {
            alert(json.message ?? "Something went wrong.");
            console.error(json.errors);
        }
    });

loadIssues();
