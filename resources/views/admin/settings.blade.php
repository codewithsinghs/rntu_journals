@extends('layouts.admin')

@section('content')
    <!-- Page -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Settings
                </div>

                <form id="settingsForm">

                    <!-- Branding -->
                    <div class="inner_fp">
                        <div class="ssid">Branding</div>

                        <div class="content_container">

                            <div class="content_inner">

                                <div class="row g-4">

                                    {{-- Logo slot --}}
                                    <div class="col-md-6">
                                        <div class=" mb-0">
                                            <div
                                                class="d-flex justify-content-between align-items-center slot-label-row mb-2">
                                                <label class="adm-label mb-0">Logo</label>
                                                <button type="button" class="delete-btn d-none" id="logo_removeBtn"
                                                    onclick="removeSlotMedia('logo')">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </div>

                                            <div id="logo_dropZone"
                                                class="drop-zone d-flex flex-column align-items-center justify-content-center p-4"
                                                data-key="logo">
                                                <div id="logo_preview" class="d-none mb-2 text-center">
                                                    <img id="logo_previewImg" src="" alt="Logo"
                                                        class="img-thumbnail" style="max-height: 120px;">
                                                </div>
                                                <div id="logo_placeholder" class="text-center">
                                                    <i class="bi bi-cloud-upload text-secondary"
                                                        style="font-size: 2.5rem;"></i>
                                                    <p class="mb-1 mt-2 text-secondary" id="logo_dropZoneText">Drag and drop
                                                        a logo here, or
                                                        click to browse</p>
                                                    <p class="text-muted small mb-0">Max size: 10 MB</p>
                                                </div>
                                                <input type="file" id="logo_file" class="d-none" accept="image/*">
                                            </div>
                                            <div class="invalid-feedback d-block" id="logo_fileError"></div>
                                        </div>
                                    </div>

                                    {{-- Favicon slot --}}
                                    <div class="col-md-6">
                                        <div class=" mb-0">
                                            <div
                                                class="d-flex justify-content-between align-items-center slot-label-row mb-2">
                                                <label class="adm-label mb-0">Favicon</label>
                                                <button type="button" class="delete-btn d-none" id="favicon_removeBtn"
                                                    onclick="removeSlotMedia('favicon')">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </div>

                                            <div id="favicon_dropZone"
                                                class="drop-zone d-flex flex-column align-items-center justify-content-center p-4"
                                                data-key="favicon">
                                                <div id="favicon_preview" class="d-none mb-2 text-center">
                                                    <img id="favicon_previewImg" src="" alt="Favicon"
                                                        class="img-thumbnail slot-favicon-preview">
                                                </div>
                                                <div id="favicon_placeholder" class="text-center">
                                                    <i class="bi bi-cloud-upload text-secondary"
                                                        style="font-size: 2.5rem;"></i>
                                                    <p class="mb-1 mt-2 text-secondary" id="favicon_dropZoneText">Drag and
                                                        drop a favicon
                                                        here, or click to browse</p>
                                                    <p class="text-muted small mb-0">Square PNG/SVG recommended — Max 10 MB
                                                    </p>
                                                </div>
                                                <input type="file" id="favicon_file" class="d-none" accept="image/*">
                                            </div>
                                            <div class="invalid-feedback d-block" id="favicon_fileError"></div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>



                    </div>

                    <!-- General Settings -->
                    <div class="inner_fp">

                        <div class="ssid">General Settings</div>

                        <div class="content_container">

                            <div class="content_inner">

                                <div class="content_partitions">

                                    <!-- Website Name -->
                                    <div class="partitions_inner">
                                        <label>Website Name</label>
                                        <input type="text" class="content_show" id="website_name"></input>
                                        <div class="invalid-feedback" id="website_nameError"></div>
                                    </div>

                                    <!-- Website URL -->
                                    <div class="partitions_inner">
                                        <label>Website URL</label>
                                        <input type="url" class="content_show" id="website_url"
                                            placeholder="https://example.com"></input>
                                        <div class="invalid-feedback" id="website_urlError"></div>
                                    </div>

                                    <!-- Email -->
                                    <div class="partitions_inner mar_part">
                                        <label>Email</label>
                                        <input type="text" class="content_show" id="email"></input>
                                        <div class="invalid-feedback" id="emailError"></div>
                                    </div>

                                    <!-- Phone -->
                                    <div class="partitions_inner mar_part">
                                        <label>Phone</label>
                                        <input type="text" class="content_show" id="phone"></input>
                                        <div class="invalid-feedback" id="phoneError"></div>
                                    </div>

                                </div>

                            </div>

                            <!-- Address -->
                            <div class="content_inner">
                                <label>Address</label>
                                <textarea id="address" rows="2" class="content_show"></textarea>
                                <div class="invalid-feedback" id="addressError"></div>
                            </div>

                        </div>


                    </div>

                    <!-- Social Links -->
                    <div class="inner_fp">

                        <div class="ssid">Social Links</div>

                        <form id="settingsForm">

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Facebook URL -->
                                        <div class="partitions_inner">
                                            <label>Facebook URL</label>
                                            <input type="url" class="content_show" id="facebook_url"></input>
                                            <div class="invalid-feedback" id="facebook_urlError"></div>
                                        </div>

                                        <!-- Instagram URL -->
                                        <div class="partitions_inner">
                                            <label>Instagram URL</label>
                                            <input type="url" class="content_show" id="instagram_url"></input>
                                            <div class="invalid-feedback" id="instagram_urlError"></div>
                                        </div>

                                        <!-- Twitter / X URL -->
                                        <div class="partitions_inner mar_part">
                                            <label>Twitter / X URL</label>
                                            <input type="url" class="content_show" id="twitter_url"></input>
                                            <div class="invalid-feedback" id="twitter_urlError"></div>
                                        </div>

                                        <!-- YouTube URL -->
                                        <div class="partitions_inner mar_part">
                                            <label>YouTube URL</label>
                                            <input type="url" class="content_show" id="youtube_url"></input>
                                            <div class="invalid-feedback" id="youtube_urlError"></div>
                                        </div>

                                        <!-- LinkedIn URL -->
                                        <div class="partitions_inner mar_part">
                                            <label>LinkedIn URL</label>
                                            <input type="url" class="content_show" id="linkedin_url"></input>
                                            <div class="invalid-feedback" id="linkedin_urlError"></div>
                                        </div>

                                    </div>

                                </div>

                                <!-- Button -->
                                <section class="term_con">
                                    <div class="button_d"><button class="green_d" id="saveSettingsBtn">Save
                                            Setting</button>
                                    </div>
                                </section>

                            </div>

                        </form>

                    </div>

                </form>

            </div>
        </div>
    </section>

    <!-- REMOVE CONFIRM MODAL -->
    <div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-4 px-4">
                    <div class="delete-icon-wrap mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.8rem;"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">Remove <span id="removeMediaLabel"></span></h6>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        Are you sure you want to remove this image?<br>
                        <span class="small">This action cannot be undone.</span>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmRemoveBtn">Yes, Remove</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SAVE SUCCESS MODAL -->
    <div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-5 px-4">
                    <div class="success-icon-box">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                            <path d="M5 13l4 4L19 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h5 class="fw-semibold mb-1" id="saveSuccessTitle">Saved Successfully</h5>
                    <p class="text-muted small mb-0" id="saveSuccessMsg"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-primary px-5" id="saveSuccessOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
        <div id="appToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex align-items-center gap-3 px-3 py-3">
                <div id="toastIconWrap" class="toast-icon-wrap flex-shrink-0">
                    <i id="toastIcon" class="bi fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <div id="toastTitle" class="fw-semibold" style="font-size:0.9rem;"></div>
                    <div id="toastMessage" class="opacity-75" style="font-size:0.8rem;"></div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2"
                    data-bs-dismiss="toast"></button>
            </div>
            <div id="toastProgressBar" class="toast-progress-bar"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const API = "/api/admin/settings";
        const TOKEN = localStorage.getItem('jwt_token') || '';

        const TEXT_FIELDS = [
            'address', 'email', 'phone', 'website_name', 'website_url',
            'facebook_url', 'instagram_url', 'twitter_url', 'youtube_url', 'linkedin_url'
        ];
        const MEDIA_KEYS = ['logo', 'favicon'];

        let confirmRemoveModal, saveSuccessModal;
        let pendingRemoveKey = null;

        // ── Boot ─────────────────────────────────────────────────────────
        document.addEventListener("DOMContentLoaded", function() {
            confirmRemoveModal = new bootstrap.Modal(document.getElementById("confirmRemoveModal"));
            saveSuccessModal = new bootstrap.Modal(document.getElementById("saveSuccessModal"));

            loadSettings();

            document.getElementById("settingsForm").addEventListener("submit", function(e) {
                e.preventDefault();
                submitSettingsForm();
            });

            document.getElementById("confirmRemoveBtn").addEventListener("click", function() {
                if (pendingRemoveKey === null) return;
                confirmRemoveModal.hide();
                executeRemoveSlotMedia(pendingRemoveKey);
                pendingRemoveKey = null;
            });

            document.getElementById("saveSuccessOkBtn").addEventListener("click", function() {
                saveSuccessModal.hide();
            });

            MEDIA_KEYS.forEach(key => setupSlotDropZone(key));
        });

        // ── Headers ──────────────────────────────────────────────────────
        function authHeaders() {
            return {
                "Accept": "application/json",
                "Authorization": `Bearer ${TOKEN}`
            };
        }

        function jsonHeaders() {
            return {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "Authorization": `Bearer ${TOKEN}`
            };
        }

        function formHeaders() {
            return {
                "Accept": "application/json",
                "Authorization": `Bearer ${TOKEN}`
            };
        }

        // ── Load settings ────────────────────────────────────────────────
        function loadSettings() {
            fetch(API, {
                    headers: authHeaders()
                })
                .then(handleAuthErrors)
                .then(res => res.json())
                .then(res => {
                    if (!res.status) {
                        showToast(res.message || "Failed to load settings.", "danger");
                        return;
                    }

                    const settings = res.data || {};

                    TEXT_FIELDS.forEach(field => {
                        const el = document.getElementById(field);
                        if (el) el.value = settings[field] || "";
                    });

                    const media = settings.media || {};
                    MEDIA_KEYS.forEach(key => renderSlotMedia(key, media[key] || null));
                })
                .catch(err => {
                    console.error("Load settings failed:", err.message);
                    showToast("Failed to load settings.", "danger");
                });
        }

        // ── Save settings (text fields only) ────────────────────────────
        function submitSettingsForm() {
            clearFieldErrors(TEXT_FIELDS);

            const payload = {};
            TEXT_FIELDS.forEach(field => {
                const el = document.getElementById(field);
                payload[field] = el.value.trim() || null;
            });

            const btn = document.getElementById("saveSettingsBtn");
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            fetch(API, {
                    method: "PUT",
                    headers: jsonHeaders(),
                    body: JSON.stringify(payload)
                })
                .then(handleAuthErrors)
                .then(async res => {
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
                .then(data => {
                    document.getElementById("saveSuccessTitle").textContent = "Saved Successfully";
                    document.getElementById("saveSuccessMsg").textContent = data.message ||
                        "Settings updated successfully.";
                    saveSuccessModal.show();
                })
                .catch(err => {
                    if (err.message !== "Validation failed") showToast(err.message, "danger");
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

            zone.addEventListener("dragover", e => {
                e.preventDefault();
                zone.classList.add("dragover");
            });
            zone.addEventListener("dragleave", () => zone.classList.remove("dragover"));
            zone.addEventListener("drop", e => {
                e.preventDefault();
                zone.classList.remove("dragover");
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    uploadSlotMedia(key, e.dataTransfer.files[0]);
                }
            });

            input.addEventListener("change", function() {
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
                    body: formData
                })
                .then(handleAuthErrors)
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        if (res.status === 422 && data.errors) {
                            const msg = data.errors.file?.[0] || data.errors.key?.[0] || "Upload failed.";
                            document.getElementById(`${key}_fileError`).textContent = msg;
                            throw new Error("Validation failed");
                        }
                        throw new Error(data.message || "Upload failed");
                    }
                    return data;
                })
                .then(data => {
                    renderSlotMedia(key, data.data.media);
                    showToast(data.message || `${capitalize(key)} uploaded successfully.`, "success");
                })
                .catch(err => {
                    if (err.message !== "Validation failed") showToast(err.message, "danger");
                    // reset drop zone on failure
                    zone.classList.remove("has-file");
                    document.getElementById(`${key}_dropZoneText`).textContent = key === 'logo' ?
                        "Drag and drop a logo here, or click to browse" :
                        "Drag and drop a favicon here, or click to browse";
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
                    headers: jsonHeaders()
                })
                .then(handleAuthErrors)
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || "Failed to remove.");
                    return data;
                })
                .then(data => {
                    renderSlotMedia(key, null);
                    showToast(data.message || `${capitalize(key)} removed successfully.`, "success");
                })
                .catch(err => showToast(err.message, "danger"));
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
                dropZoneText.textContent = key === 'logo' ?
                    "Drag and drop a logo here, or click to browse" :
                    "Drag and drop a favicon here, or click to browse";
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
        function showToast(message, type = 'success') {
            const toast = document.getElementById('appToast');
            const icon = document.getElementById('toastIcon');
            const titleEl = document.getElementById('toastTitle');
            const messageEl = document.getElementById('toastMessage');

            toast.classList.remove('toast-success', 'toast-danger', 'toast-warning');
            toast.classList.add(`toast-${type}`);

            const config = {
                success: {
                    icon: 'bi-check-circle-fill',
                    title: 'Success'
                },
                danger: {
                    icon: 'bi-x-circle-fill',
                    title: 'Error'
                },
                warning: {
                    icon: 'bi-exclamation-circle-fill',
                    title: 'Warning'
                },
            };
            const cfg = config[type] || config.success;

            icon.className = `bi ${cfg.icon} fs-5`;
            titleEl.textContent = cfg.title;
            messageEl.textContent = message;

            const bar = document.getElementById('toastProgressBar');
            bar.style.display = 'none';
            void bar.offsetWidth;
            bar.style.display = '';

            new bootstrap.Toast(toast, {
                delay: 4000
            }).show();
        }

        // ── Field errors ─────────────────────────────────────────────────
        function showFieldErrors(errors) {
            Object.keys(errors).forEach(field => {
                const errorEl = document.getElementById(`${field}Error`);
                if (errorEl) errorEl.textContent = errors[field][0];
            });
        }

        function clearFieldErrors(fields) {
            fields.forEach(field => {
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
    </script>
@endsection
