@extends('layouts.admin')

@section('content')

    <!-- List -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Roles Management
                </div>

                <div class="table-controls">
                    <button type="button" class="add-btn" id="openCreateModalBtn">Create Role</button> &nbsp; &nbsp;
                    <input type="text" id="searchInput" class="form-control form-control-sm" style="width: 220px;"
                        placeholder="Search roles..."> &nbsp; &nbsp;
                    <button class="btn btn-outline-secondary btn-sm" id="clearSearch">Clear</button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Role Name</th>
                                <th>Guard</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rolesTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-style-group">
                    <span>Show</span>
                    <select id="perPageSelect" class="form-select form-select-sm" style="width: 100px;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>Entries</span>
                </div>

                <div class="pagination-footet-two ">
                    <div id="paginationInfo"></div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

    <!-- CREATE MODAL -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModallabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title">Create New Role</div>
                    </div>

                    <div class="middle-3 middle"></div>

                    <!-- Role -->
                    <div class=" mb-3">
                        <label>Role Name <span class="text-danger">*</span></label>
                        <input type="text" id="createRoleName" class="form-control form-control-sm"
                            placeholder="e.g. editor, moderator">
                        <div class="invalid-feedback" id="createRoleNameError"></div>
                    </div>

                    <div class=" mb-0">
                        <label>Assign Permissions</label>
                        <br>
                        <div id="createPermissionsBox" class="row border rounded p-2 g-2 bg-white">
                            <div class="col-12 text-center text-muted py-2">Loading permissions...</div>
                        </div>
                    </div>

                    <!-- Btn -->
                    <div class="bottom-btn">
                        <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel </button>
                        <button type="button" class="blue" id="createRoleBtn">
                            <span id="createBtnSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            <span id="createBtnText">Create Role</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true" aria-labelledby="editRoleModallabel"
        style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title">Edit Role</div>
                    </div>

                    <input type="hidden" id="editRoleId">

                    <div class="middle"></div>

                    <div class=" mb-3">
                        <label>Role Name <span class="text-danger">*</span></label>
                        <br>
                        <input type="text" id="editRoleNameInput" class="form-control form-control-sm">
                        <div class="invalid-feedback" id="editRoleNameInputError"></div>
                    </div>

                    <div class=" mb-0">
                        <label>Assign Permissions</label>
                        <br>
                        <div id="editPermissionsBox" class="row border rounded p-2 g-2 bg-white">
                            <div class="col-12 text-center text-muted py-2">Loading permissions...</div>
                        </div>
                    </div>

                    <!-- Btn -->
                    <div class="bottom-btn">
                        <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                        </button>
                        <button type="submit" class="blue" id="editRoleBtn">
                            <span id="editBtnSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            <span id="editBtnText">Save Changes</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-4 px-4">
                    <div class="delete-icon-wrap mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.8rem;"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">Are you sure?</h6>
                    <p class="text-muted mb-3" style="font-size:0.9rem;">
                        You are about to delete role <strong class="text-dark" id="deleteRoleName"></strong>.
                        <br>This action <strong>cannot</strong> be undone.
                    </p>
                    <input type="hidden" id="deleteRoleId">
                    <div id="deleteModalError" class="alert alert-danger d-none py-2 text-start"
                        style="font-size:0.85rem;">
                        <i class="bi bi-x-circle me-1"></i><span id="deleteModalErrorText"></span>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="deleteRoleBtn">
                        <span id="deleteBtnText"><i class="bi bi-trash3 me-1"></i>Yes, Delete</span>
                        <span id="deleteBtnSpinner" class="spinner-border spinner-border-sm d-none ms-1"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST -->
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
   <script src="{{ asset('assets/js/admin/roles.js') }}"></script>
@endsection
