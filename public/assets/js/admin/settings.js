const API = "/api/admin/settings";
const TOKEN = localStorage.getItem("jwt_token") || "";

const TEXT_FIELDS = [
    "address",
    "email",
    "phone",
    "website_name",
    "website_url",
    "facebook_url",
    "instagram_url",
    "twitter_url",
    "youtube_url",
    "linkedin_url",
];
const MEDIA_KEYS = ["logo", "favicon"];

let confirmRemoveModal, saveSuccessModal;
let pendingRemoveKey = null;

// ── Boot ─────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    confirmRemoveModal = new bootstrap.Modal(
        document.getElementById("confirmRemoveModal"),
    );
    saveSuccessModal = new bootstrap.Modal(
        document.getElementById("saveSuccessModal"),
    );

    loadSettings();

    document
        .getElementById("settingsForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            submitSettingsForm();
        });

    document
        .getElementById("confirmRemoveBtn")
        .addEventListener("click", function () {
            if (pendingRemoveKey === null) return;
            confirmRemoveModal.hide();
            executeRemoveSlotMedia(pendingRemoveKey);
            pendingRemoveKey = null;
        });

    document
        .getElementById("saveSuccessOkBtn")
        .addEventListener("click", function () {
            saveSuccessModal.hide();
        });

    MEDIA_KEYS.forEach((key) => setupSlotDropZone(key));
});

// ── Headers ──────────────────────────────────────────────────────
function authHeaders() {
    return {
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    };
}

function jsonHeaders() {
    return {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    };
}

function formHeaders() {
    return {
        Accept: "application/json",
        Authorization: `Bearer ${TOKEN}`,
    };
}

// ── Load settings ────────────────────────────────────────────────
function loadSettings() {
    fetch(API, {
        headers: authHeaders(),
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((res) => {
            if (!res.status) {
                showToast(res.message || "Failed to load settings.", "danger");
                return;
            }

            const settings = res.data || {};

            TEXT_FIELDS.forEach((field) => {
                const el = document.getElementById(field);
                if (el) el.value = settings[field] || "";
            });

            const media = settings.media || {};
            MEDIA_KEYS.forEach((key) =>
                renderSlotMedia(key, media[key] || null),
            );
        })
        .catch((err) => {
            console.error("Load settings failed:", err.message);
            showToast("Failed to load settings.", "danger");
        });
}

// ── Save settings (text fields only) ────────────────────────────
function submitSettingsForm() {
    clearFieldErrors(TEXT_FIELDS);

    const payload = {};
    TEXT_FIELDS.forEach((field) => {
        const el = document.getElementById(field);
        payload[field] = el.value.trim() || null;
    });

    const btn = document.getElementById("saveSettingsBtn");
    btn.disabled = true;
    btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    fetch(API, {
        method: "PUT",
        headers: jsonHeaders(),
        body: JSON.stringify(payload),
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
            document.getElementById("saveSuccessTitle").textContent =
                "Saved Successfully";
            document.getElementById("saveSuccessMsg").textContent =
                data.message || "Settings updated successfully.";
            saveSuccessModal.show();
        })
        .catch((err) => {
            if (err.message !== "Validation failed")
                showToast(err.message, "danger");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Save Settings';
        });
}

// ── Drag & drop (logo / favicon) ────────────────────────────────
function setupSlotDropZone(key) {
    const zone = document.getElementById(`${key}_dropZone`);
    const input = document.getElementById(`${key}_file`);
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
            uploadSlotMedia(key, e.dataTransfer.files[0]);
        }
    });

    input.addEventListener("change", function () {
        if (this.files.length) uploadSlotMedia(key, this.files[0]);
    });
}

// ── Upload a slot file ───────────────────────────────────────────
function uploadSlotMedia(key, file) {
    document.getElementById(`${key}_fileError`).textContent = "";

    const zone = document.getElementById(`${key}_dropZone`);
    zone.classList.add("has-file");
    document.getElementById(`${key}_dropZoneText`).textContent = "Uploading...";

    const formData = new FormData();
    formData.append("file", file);

    fetch(`${API}/media/${key}`, {
        method: "POST",
        headers: formHeaders(), // no Content-Type — browser sets multipart boundary
        body: formData,
    })
        .then(handleAuthErrors)
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    const msg =
                        data.errors.file?.[0] ||
                        data.errors.key?.[0] ||
                        "Upload failed.";
                    document.getElementById(`${key}_fileError`).textContent =
                        msg;
                    throw new Error("Validation failed");
                }
                throw new Error(data.message || "Upload failed");
            }
            return data;
        })
        .then((data) => {
            renderSlotMedia(key, data.data.media);
            showToast(
                data.message || `${capitalize(key)} uploaded successfully.`,
                "success",
            );
        })
        .catch((err) => {
            if (err.message !== "Validation failed")
                showToast(err.message, "danger");
            // reset drop zone on failure
            zone.classList.remove("has-file");
            document.getElementById(`${key}_dropZoneText`).textContent =
                key === "logo"
                    ? "Drag and drop a logo here, or click to browse"
                    : "Drag and drop a favicon here, or click to browse";
        })
        .finally(() => {
            document.getElementById(`${key}_file`).value = "";
        });
}

// ── Remove a slot ────────────────────────────────────────────────
function removeSlotMedia(key) {
    pendingRemoveKey = key;
    document.getElementById("removeMediaLabel").textContent = capitalize(key);
    confirmRemoveModal.show();
}

function executeRemoveSlotMedia(key) {
    fetch(`${API}/media/${key}`, {
        method: "DELETE",
        headers: jsonHeaders(),
    })
        .then(handleAuthErrors)
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || "Failed to remove.");
            return data;
        })
        .then((data) => {
            renderSlotMedia(key, null);
            showToast(
                data.message || `${capitalize(key)} removed successfully.`,
                "success",
            );
        })
        .catch((err) => showToast(err.message, "danger"));
}

// ── Render slot preview / placeholder ───────────────────────────
function renderSlotMedia(key, media) {
    const preview = document.getElementById(`${key}_preview`);
    const previewImg = document.getElementById(`${key}_previewImg`);
    const placeholder = document.getElementById(`${key}_placeholder`);
    const zone = document.getElementById(`${key}_dropZone`);
    const removeBtn = document.getElementById(`${key}_removeBtn`);
    const dropZoneText = document.getElementById(`${key}_dropZoneText`);

    if (media && media.url) {
        previewImg.src = media.url;
        preview.classList.remove("d-none");
        placeholder.classList.add("d-none");
        zone.classList.add("has-file");
        removeBtn.classList.remove("d-none");
    } else {
        preview.classList.add("d-none");
        previewImg.src = "";
        placeholder.classList.remove("d-none");
        zone.classList.remove("has-file");
        removeBtn.classList.add("d-none");
        dropZoneText.textContent =
            key === "logo"
                ? "Drag and drop a logo here, or click to browse"
                : "Drag and drop a favicon here, or click to browse";
    }
}

// ── Auth errors ──────────────────────────────────────────────────
function handleAuthErrors(res) {
    if (res.status === 401) {
        window.location.href = "/login";
        throw new Error("Not authenticated");
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

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str == null ? "" : str;
    return div.innerHTML;
}