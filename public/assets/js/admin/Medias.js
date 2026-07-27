const API = "/api/admin/medias";
const CSRF_TOKEN =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

let uploadModal,
    editModal,
    confirmDeleteModal,
    deleteSuccessModal,
    saveSuccessModal;
let pendingDeleteId = null;
let editingId = null;

let allMedia = [];
let filteredMedia = [];
let currentPage = 1;
let perPage = 10;

// ── Boot ─────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    uploadModal = new bootstrap.Modal(document.getElementById("uploadModal"));
    editModal = new bootstrap.Modal(document.getElementById("editModal"));
    confirmDeleteModal = new bootstrap.Modal(
        document.getElementById("confirmDeleteModal"),
    );
    deleteSuccessModal = new bootstrap.Modal(
        document.getElementById("deleteSuccessModal"),
    );
    saveSuccessModal = new bootstrap.Modal(
        document.getElementById("saveSuccessModal"),
    );

    perPage = parseInt(document.getElementById("perPage").value, 10);
    loadMedia();

    document
        .getElementById("uploadForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            submitUpload();
        });

    document
        .getElementById("editForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            submitEdit();
        });

    document
        .getElementById("confirmDeleteBtn")
        .addEventListener("click", function () {
            if (pendingDeleteId === null) return;
            confirmDeleteModal.hide();
            executeDelete(pendingDeleteId);
            pendingDeleteId = null;
        });

    document
        .getElementById("saveSuccessOkBtn")
        .addEventListener("click", function () {
            saveSuccessModal.hide();
        });

    setupDropZone("uploadDropZone", "upload_file", handleUploadFile);
    setupDropZone("editDropZone", "edit_file", handleEditFile);
});

// ── Headers (JSON requests only — uploads use FormData, no Content-Type) ──
function jsonHeaders() {
    return {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": CSRF_TOKEN,
    };
}

function formHeaders() {
    return {
        Accept: "application/json",
        "X-CSRF-TOKEN": CSRF_TOKEN,
    };
}

// ── Load media ───────────────────────────────────────────────────
function loadMedia() {
    fetch(API, {
        credentials: "include",
        headers: {
            Accept: "application/json",
        },
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            // Controller paginates via Media::latest()->paginate(20); unwrap "data" from the paginator
            allMedia =
                data.data && data.data.data ? data.data.data : data.data || [];
            applyFilterAndRender();
        })
        .catch((err) => {
            console.error("Load media failed:", err.message);
            document.getElementById("mediaTable").innerHTML =
                `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load media.</td></tr>`;
        });
}

// ── Search + Pagination ──────────────────────────────────────────
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
    filteredMedia = query
        ? allMedia.filter((m) =>
              (m.original_name || "").toLowerCase().includes(query),
          )
        : allMedia;
    renderTable();
    renderPagination();
}

function renderTable() {
    const tbody = document.getElementById("mediaTable");

    if (filteredMedia.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No media found.</td></tr>`;
        document.getElementById("entriesInfo").textContent =
            "Showing 0 to 0 of 0 entries";
        return;
    }

    const totalPages = Math.max(1, Math.ceil(filteredMedia.length / perPage));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * perPage;
    const pageItems = filteredMedia.slice(start, start + perPage);

    let rows = "";
    pageItems.forEach((item) => {
        const isImage = (item.mime_type || "").startsWith("image/");
        const thumb =
            isImage && item.url
                ? `<img src="${item.url}" class="media-thumb" alt="">`
                : `<div class="media-thumb d-flex align-items-center justify-content-center bg-light"><i class="bi ${fileIconClass(item.mime_type)}"></i></div>`;

        const sizeKb = item.size ? (item.size / 1024).toFixed(2) + " KB" : "—";
        const viewLink = item.url
            ? `<a href="${item.url}" target="_blank" rel="noopener" class="small">View</a>`
            : '<span class="adm-pill-muted">N/A</span>';

        rows += `
                <tr>
                    <td>${thumb}</td>
                    <td>
                        ${escapeHtml(item.original_name || "")}<br>
                        ${viewLink}
                    </td>
                    <td><span class="green-btn">${escapeHtml(item.mime_type || "—")}</span></td>
                    <td>${sizeKb}</td>
                    <td>${escapeHtml(item.created_at || "")}</td>
                    <td>
                        <div class="d-flec">
                            <button class="edit-btn" onclick="openEditModal(${item.id})">Edit</button>
                            <button class="delete-btn" onclick="deleteMedia(${item.id}, '${escapeHtml(item.original_name || "").replace(/'/g, "\\'")}')">Delete</button>
                        </div>
                    </td>
                </tr>`;
    });

    tbody.innerHTML = rows;
    document.getElementById("entriesInfo").textContent =
        `Showing ${start + 1} to ${Math.min(start + perPage, filteredMedia.length)} of ${filteredMedia.length} entries`;
}

function fileIconClass(mimeType) {
    mimeType = mimeType || "";
    if (mimeType.includes("pdf")) return "bi-file-earmark-pdf text-danger";
    if (mimeType.includes("word") || mimeType.includes("document"))
        return "bi-file-earmark-word text-primary";
    if (mimeType.includes("sheet") || mimeType.includes("excel"))
        return "bi-file-earmark-excel text-success";
    if (mimeType.includes("presentation"))
        return "bi-file-earmark-ppt text-warning";
    if (mimeType.includes("video")) return "bi-file-earmark-play text-info";
    if (mimeType.includes("zip")) return "bi-file-earmark-zip text-secondary";
    return "bi-file-earmark text-secondary";
}

function renderPagination() {
    const totalPages = Math.max(1, Math.ceil(filteredMedia.length / perPage));
    const ul = document.getElementById("paginationControls");
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
    const totalPages = Math.max(1, Math.ceil(filteredMedia.length / perPage));
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
    renderPagination();
}

// ── Drag & drop ──────────────────────────────────────────────────
function setupDropZone(zoneId, inputId, onFile) {
    const zone = document.getElementById(zoneId);
    const input = document.getElementById(inputId);
    if (!zone || !input) return;

    zone.addEventListener("click", () => input.click());

    zone.addEventListener("dragover", (e) => {
        e.preventDefault();
        zone.classList.add("dragover");
    });
    zone.addEventListener("dragleave", () => zone.classList.remove("dragover"));
    zone.addEventListener("drop", (e) => {
        e.preventDefault();
        zone.classList.remove("dragover");
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            onFile(e.dataTransfer.files[0]);
        }
    });

    input.addEventListener("change", function () {
        if (this.files.length) onFile(this.files[0]);
    });
}

function handleUploadFile(file) {
    const zone = document.getElementById("uploadDropZone");
    document.getElementById("uploadDropZoneText").innerHTML =
        '<i class="bi bi-file-earmark-check text-success me-1"></i>' +
        escapeHtml(file.name);
    zone.classList.add("has-file");

    const customNameInput = document.getElementById("upload_custom_name");
    if (customNameInput && !customNameInput.value) {
        customNameInput.value = file.name.replace(/\.[^/.]+$/, "");
    }

    showPreviewIfImage(file, "uploadPreview", "uploadPreviewImg");
}

function handleEditFile(file) {
    const zone = document.getElementById("editDropZone");
    document.getElementById("editDropZoneText").innerHTML =
        '<i class="bi bi-file-earmark-check text-success me-1"></i>' +
        escapeHtml(file.name);
    zone.classList.add("has-file");

    showPreviewIfImage(file, "editPreview", "editPreviewImg");
}

function showPreviewIfImage(file, previewId, imgId) {
    const preview = document.getElementById(previewId);
    const img = document.getElementById(imgId);

    if (file.type && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (e) => {
            img.src = e.target.result;
            preview.classList.remove("d-none");
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add("d-none");
        img.src = "";
    }
}

function resetDropZone(zoneId, textId, defaultText) {
    const zone = document.getElementById(zoneId);
    zone.classList.remove("has-file", "dragover");
    document.getElementById(textId).textContent = defaultText;
}

// ── Upload modal ─────────────────────────────────────────────────
function openUploadModal() {
    document.getElementById("uploadForm").reset();
    document.getElementById("upload_file").value = "";
    resetDropZone(
        "uploadDropZone",
        "uploadDropZoneText",
        "Drag and drop a file here, or click to browse",
    );
    document.getElementById("uploadPreview").classList.add("d-none");
    clearFieldErrors(["custom_name", "file"]);
    uploadModal.show();
}

function submitUpload() {
    const fileInput = document.getElementById("upload_file");
    clearFieldErrors(["custom_name", "file"]);

    if (!fileInput.files.length) {
        document.getElementById("fileError").textContent =
            "Please choose a file to upload.";
        return;
    }

    const formData = new FormData();
    formData.append("file", fileInput.files[0]);
    const customName = document
        .getElementById("upload_custom_name")
        .value.trim();
    if (customName) formData.append("custom_name", customName);

    const btn = document.getElementById("uploadBtn");
    btn.disabled = true;
    btn.textContent = "Uploading...";

    fetch(API, {
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
                    showFieldErrors(data.errors);
                    throw new Error("Validation failed");
                }
                throw new Error(data.message || "Upload failed");
            }
            return data;
        })
        .then((data) => {
            loadMedia();
            uploadModal.hide();
            document.getElementById("saveSuccessTitle").textContent =
                "Uploaded Successfully";
            document.getElementById("saveSuccessMsg").textContent =
                data.message || "Media uploaded successfully.";
            saveSuccessModal.show();
        })
        .catch((err) => {
            if (err.message !== "Validation failed")
                showToast(err.message, "danger");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> Upload';
        });
}

// ── Edit modal ───────────────────────────────────────────────────
function openEditModal(id) {
    const item = allMedia.find((m) => m.id === id);
    if (!item) {
        showToast("Media not found.", "danger");
        return;
    }

    editingId = id;
    document.getElementById("edit_id").value = item.id;
    document.getElementById("edit_original_name").value =
        item.original_name || "";
    document.getElementById("edit_file").value = "";

    resetDropZone(
        "editDropZone",
        "editDropZoneText",
        "Drag and drop or click to replace file",
    );
    document.getElementById("editPreview").classList.add("d-none");

    const icon = document.getElementById("editDropZoneIcon");
    const isImage = (item.mime_type || "").startsWith("image/");
    if (isImage && item.url) {
        icon.innerHTML = `<img src="${item.url}" alt="current" class="img-thumbnail mb-1" style="max-height:80px;">`;
    } else {
        icon.innerHTML = `<i class="bi ${fileIconClass(item.mime_type)}" style="font-size:2.5rem;"></i>`;
    }

    clearFieldErrors(["edit_original_name", "new_file"]);
    editModal.show();
}

function submitEdit() {
    clearFieldErrors(["edit_original_name", "new_file"]);

    const id = document.getElementById("edit_id").value;
    const formData = new FormData();
    formData.append(
        "original_name",
        document.getElementById("edit_original_name").value.trim(),
    );
    formData.append("_method", "POST"); // controller route is POST (see routes/api.php note on multipart updates)

    const newFileInput = document.getElementById("edit_file");
    if (newFileInput.files.length) {
        formData.append("new_file", newFileInput.files[0]);
    }

    const btn = document.getElementById("editBtn");
    btn.disabled = true;
    btn.textContent = "Saving...";

    fetch(`${API}/${id}`, {
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
                    showFieldErrors(data.errors);
                    throw new Error("Validation failed");
                }
                throw new Error(data.message || "Update failed");
            }
            return data;
        })
        .then((data) => {
            loadMedia();
            editModal.hide();
            document.getElementById("saveSuccessTitle").textContent =
                "Updated Successfully";
            document.getElementById("saveSuccessMsg").textContent =
                data.message || "Media updated successfully.";
            saveSuccessModal.show();
        })
        .catch((err) => {
            if (err.message !== "Validation failed")
                showToast(err.message, "danger");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Update Media';
        });
}

// ── Delete ───────────────────────────────────────────────────────
function deleteMedia(id, name) {
    pendingDeleteId = id;
    document.getElementById("deleteMediaName").textContent = name;
    confirmDeleteModal.show();
}

function executeDelete(id) {
    fetch(`${API}/${id}`, {
        method: "DELETE",
        credentials: "include",
        headers: jsonHeaders(),
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            loadMedia();
            document.getElementById("deleteSuccessMsg").textContent =
                data.message || "The media has been deleted.";
            deleteSuccessModal.show();
        })
        .catch((err) => showToast(err.message || "Delete failed.", "danger"));
}

// ── Auth errors ──────────────────────────────────────────────────
function handleAuthErrors(res) {
    if (res.status === 401) {
        window.location.href = "/login";
        throw new Error("Not authenticated");
    }
    if (res.status === 419) {
        showToast("Session expired. Reloading...", "warning");
        setTimeout(() => window.location.reload(), 1500);
        throw new Error("CSRF token expired");
    }
    return res;
}

// ── Toast ────────────────────────────────────────────────────────
function showToast(message, type = "success") {
    const toast = document.getElementById("appToast");
    const icon = document.getElementById("toastIcon");
    const titleEl = document.getElementById("toastTitle");
    const messageEl = document.getElementById("toastMessage");

    toast.classList.remove("toast-success", "toast-danger", "toast-warning");
    toast.classList.add(`toast-${type}`);

    const config = {
        success: {
            icon: "bi-check-circle-fill",
            title: "Success",
        },
        danger: {
            icon: "bi-x-circle-fill",
            title: "Error",
        },
        warning: {
            icon: "bi-exclamation-circle-fill",
            title: "Warning",
        },
    };
    const cfg = config[type] || config.success;

    icon.className = `bi ${cfg.icon} fs-5`;
    titleEl.textContent = cfg.title;
    messageEl.textContent = message;

    const bar = document.getElementById("toastProgressBar");
    bar.style.display = "none";
    void bar.offsetWidth;
    bar.style.display = "";

    new bootstrap.Toast(toast, {
        delay: 4000,
    }).show();
}

// ── Field errors ─────────────────────────────────────────────────
function showFieldErrors(errors) {
    Object.keys(errors).forEach((field) => {
        const errorEl = document.getElementById(`${field}Error`);
        if (errorEl) errorEl.textContent = errors[field][0];
    });
}

function clearFieldErrors(fields) {
    fields.forEach((field) => {
        const errorEl = document.getElementById(`${field}Error`);
        if (errorEl) errorEl.textContent = "";
    });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str == null ? "" : str;
    return div.innerHTML;
}
