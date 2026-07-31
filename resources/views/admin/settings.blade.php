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
    <script src="{{ asset('assets/js/admin/settings.js') }}"></script>
@endsection
