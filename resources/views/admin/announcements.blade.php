@extends('layouts.admin')

@section('content')

    <!-- Announcement List -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards" style="overflow:visible">

                <div class="heading">Announcement List</div>

                <div class="table-controls">
                    <button class="add-btn" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">Add
                        Announcement</button> &nbsp; &nbsp;
                </div>

                <div class="table-container" style="margin: 0;" id="saTableWrap">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Link</th>
                                <th>Sequence</th>
                                <th>Attachment</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="announcements-table-body">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="pagination-container" class="an-pagination"></div>

            </div>
        </div>
    </section>

    <!-- ADD MODAL -->
    <div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <form id="addForm">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title">Add Announcement</div>
                        </div>

                        <div class="middle">

                            <!-- Name -->
                            <span class="input-set">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="add_name" required>
                            </span>

                            <!-- Link -->
                            <span class="input-set" id="add_link_wrapper">
                                <label>Link</label>
                                <input type="url" name="link" id="add_link" placeholder="https://...">
                            </span>

                        </div>

                        <!-- Attachment -->
                        <div class="reason" id="add_attachment_wrapper">
                            <label>Attachment</label>
                            <small class="text-muted"> Allowed: PDF, DOC, DOCX, JPG, PNG, WEBP (max 5MB). Adding an
                                attachment will disable the link field.</small>
                            <input type="file" name="attachment" id="add_attachment" class="form-control"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                        </div>

                        <!-- Sequence -->
                        <div class="reason">
                            <label>Sequence</label>
                            <input type="number" name="sequence" id="add_sequence" class="form-control" value="0"
                                min="0">
                        </div>

                        <div class="bottom-btn">
                            <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>
                            <button type="submit" class="green" id="add-spinner"> Save</button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <form id="editForm">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title">Edit Announcement</div>
                        </div>

                        <div class="middle">

                            <input type="hidden" id="edit_id">

                            <!-- Name -->
                            <span class="input-set">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_name" required>
                            </span>

                            <!-- Link -->
                            <span class="input-set" id="edit_link_wrapper">
                                <label>Link</label>
                                <input type="url" name="link" id="edit_link">
                                <small class="text-muted">Adding a link will disable the attachment field.</small>
                            </span>

                        </div>

                        <!-- Replace Attachment -->
                        <div class="reason" id="edit_attachment_wrapper">
                            <label>Replace Attachment</label>
                            <small class="text-muted"> Allowed: PDF, DOC, DOCX, JPG, PNG, WEBP (max 5MB). Adding an
                                attachment will disable the link field.</small>
                            <input type="file" name="attachment" id="edit_attachment" class="form-control"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                        </div>

                        <!-- Replace Attachment -->
                        <div class="reason" id="edit_current_attachment">
                            <label>Replace Attachment</label>
                            <a id="edit_attachment_link" href="#" target="_blank" class="edit-btn">
                                <i class="bi bi-paperclip"></i> View Current File
                            </a>
                        </div>

                        <!-- Sequence -->
                        <div class="reason">
                            <label>Sequence</label>
                            <input type="number" name="sequence" id="edit_sequence" class="form-control"
                                value="0" min="0">
                        </div>

                        <div class="bottom-btn">
                            <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>
                            <button type="submit" class="green" id="edit-spinner"> Save</button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title-remove">Confirm Delete</div>
                    </div>

                    <div class="middle-content">
                        <span>
                            Do you really want to Delete? <br>
                            <span id="announcement_name"></span>?
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="confirmDeleteBtn"> Delete </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Keep it
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!--TOAST -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="flash-toast" class="toast align-items-center text-white border-0 shadow d-none" role="alert"
            aria-live="assertive" data-bs-autohide="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="toast-icon" class="fs-5"></i>
                    <span id="toast-message"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast">
                </button>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="{{ asset('assets/js/admin/announcements.js') }}"></script>
@endsection
