@extends('layouts.admin')

@section('content')
    <!-- List -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards" style="overflow:visible">

                <div class="heading">
                    Media Library
                </div>

                <div class="table-controls">
                    <button type="button" class="add-btn" onclick="openUploadModal()">Upload Media</button> &nbsp; &nbsp;
                    <input type="text" id="searchInput" class="form-control form-control-sm" style="width: 220px;"
                        oninput="onSearchInput()" placeholder="Search by name...">
                </div>

                <div class="table-container" style="margin: 0;overflow:visible">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Original Name</th>
                                <th>Mime Type</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="mediaTable">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-style">
                    <div class="pagination-style-group">
                        <span>Show</span>
                        <select id="perPage" class="form-select form-select-sm" style="width: 100px;"
                            onchange="onPerPageChange()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>Entries</span>
                    </div>
                </div>

                <div class="pagination-footet-two">
                    <div id="entriesInfo">Showing 0 to 0 of 0 entries</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                    </nav>
                </div>


            </div>
        </div>
    </section>

    <!-- UPLOAD MODAL -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="uploadForm">

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title">Upload Media</div>
                        </div>

                        <div class="middle-3 middle">

                        </div>

                        <div class="reason">
                            <label>Custom File Name</label>
                            <input type="text" class="form-control form-control-sm" id="upload_custom_name"
                                placeholder="e.g. my-banner-image">
                            <div class="invalid-feedback" id="custom_nameError"></div>
                            <div class="form-text">Leave blank to use the original file name.</div>
                        </div>

                        <div class="reason">
                            <label>Choose File <span class="text-danger">*</span></label>
                            <div id="uploadDropZone"
                                class="drop-zone d-flex flex-column align-items-center justify-content-center p-4">
                                <i class="bi bi-cloud-upload text-secondary" style="font-size: 2.5rem;"></i>
                                <p class="mb-1 mt-2 text-secondary" id="uploadDropZoneText">Drag and drop a file here,
                                    or
                                    click to browse</p>
                                <p class="text-muted small mb-0">Max size: 10 MB</p>
                                <input type="file" id="upload_file" class="d-none">
                            </div>
                            <div class="invalid-feedback d-block" id="fileError"></div>

                            <div id="uploadPreview" class="d-none mt-3">
                                <p class="mb-1 fw-semibold small">Preview:</p>
                                <img id="uploadPreviewImg" src="" alt="Preview" class="img-thumbnail"
                                    style="max-height: 160px;">
                            </div>
                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">
                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>
                            <button type="submit" class="blue" id="uploadBtn"> Upload </button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="editForm">

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title">Edit Media</div>
                        </div>

                        <div class="middle-3 middle">

                        </div>

                        <input type="hidden" id="edit_id" value="">

                        <div class="reason">
                            <label>File Name</label>
                            <input type="text" class="form-control form-control-sm" id="edit_original_name">
                            <div class="invalid-feedback" id="edit_original_nameError"></div>
                            <div class="form-text">Edit the display name for this file.</div>
                        </div>

                        <div class="reason">
                            <label>Replace File <span>optional</span></label>
                            <div id="editDropZone"
                                class="drop-zone d-flex flex-column align-items-center justify-content-center p-4">
                                <div id="editDropZoneIcon">
                                    <i class="bi bi-cloud-upload text-secondary" style="font-size: 2.5rem;"></i>
                                </div>
                                <p class="mb-1 mt-2 text-secondary" id="editDropZoneText">Drag and drop or click to
                                    replace
                                    file</p>
                                <p class="text-muted small mb-0">Leave empty to keep existing file</p>
                                <input type="file" id="edit_file" class="d-none">
                            </div>
                            <div class="invalid-feedback d-block" id="new_fileError"></div>

                            <div id="editPreview" class="d-none mt-3">
                                <p class="mb-1 fw-semibold small">New File Preview:</p>
                                <img id="editPreviewImg" src="" alt="Preview" class="img-thumbnail"
                                    style="max-height: 160px;">
                            </div>
                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">
                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>
                            <button type="submit" class="blue" id="editBtn"> Update Media </button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title-remove">Delete Media</div>
                    </div>

                    <div class="middle-content">
                        <span>
                            Are you sure you want to delete <strong id="deleteMediaName"></strong>?<br>
                            Make sure your work is saved before leaving.
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="confirmDeleteBtn"> Delete </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Stay Logged In
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- DELETE SUCCESS MODAL -->
    <div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center">
                    <h5 class="fw-semibold mb-1">Deleted Successfully</h5>
                    <p class="text-muted small mb-0" id="deleteSuccessMsg"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-primary px-5" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SAVE SUCCESS MODAL -->
    <div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center">
                    <h5 class="fw-semibold mb-1" id="saveSuccessTitle">Saved Successfully</h5>
                    <p class="text-muted small mb-0" id="saveSuccessMsg"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-primary px-5" id="saveSuccessOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
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
    <script src="{{ asset('assets/js/admin/medias.js') }}"></script>
@endsection
