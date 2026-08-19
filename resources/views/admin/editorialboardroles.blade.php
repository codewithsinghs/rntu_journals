@extends('layouts.admin')

@section('content')
    <!-- Role List -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Editorial Board Roles</div>

                <div class="table-controls" style="display:flex; align-items:center;">
                    <button class="add-btn d-none" id="ebrEmpty" onclick="document.getElementById('openEbrModal').click()">No
                        editorial board roles found.</button>

                    <div style="display:flex; align-items:center;">
                        <button class="add-btn" id="openEbrModal" style="width: 100%;">Add Role</button> &nbsp; &nbsp;
                        <select id="journalFilter" class="form-select form-select-sm">
                            <option value="">All Journals</option>
                        </select>
                    </div>
                </div>

                <div class="table-container" style="margin: 0;" id="ebrTableWrap">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Journal</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ebrTbody">
                            <tr>
                                <div id="ebrLoading" class="text-center py-5">
                                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status">
                                    </div>
                                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                                </div>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="pagination-container" class="an-pagination"></div>

            </div>
        </div>
    </section>

    <!-- Add / Edit Modal -->
    <div class="modal fade" id="ebrModal" tabindex="-1" aria-labelledby="ebrModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <form id="ebrForm" novalidate>
                    @csrf
                    <input type="hidden" id="ebrId">
                    <input type="hidden" id="ebrMethod" value="POST">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title" id="ebrModalTitle">Add Role</div>
                        </div>

                        <div class="middle-3 middle">

                            <!-- Journal -->
                            <span class="input-set">
                                <label>Journal</label>
                                <select class="form-select" id="ebr_journal_id" name="journal_id">
                                    <option value="">— None —</option>
                                </select>
                                <div class="invalid-feedback" id="err_ebr_journal_id"></div>
                            </span>

                            <!-- Role -->
                            <span class="input-set">
                                <label>Role <span class="text-danger">*</span></label>
                                <input type="text" id="ebr_role" name="role" placeholder="e.g. Editor-in-Chief" required>
                                <div class="invalid-feedback" id="err_ebr_role"></div>
                            </span>

                            <!-- Status -->
                            <div class="rjf-checklist">
                                <label for="ebr_status">
                                    <input type="checkbox" role="switch" id="ebr_status" name="status" checked>
                                    Active
                                </label>
                            </div>

                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">

                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>

                            <button type="submit" class="blue" id="ebrSaveBtn">
                                <span id="ebrSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                                <span id="ebrSaveBtnText">Save</span>
                            </button>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div class="modal fade" id="ebrDeleteModal" tabindex="-1" aria-labelledby="ebrDeleteModalLabel" aria-hidden="true">
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
                            This role will be permanently removed.
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="ebrConfirmDeleteBtn"> <span id="ebrDeleteSpinner">
                                Delete
                            </span> </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Keep it
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="ebrToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="ebrToastIcon" style="color:white;"></span>
                    <div>
                        <div id="ebrToastTitle" class="fw-semibold" style="font-size:14px;color:white;"></div>
                        <div id="ebrToastMsg" class="opacity-75" style="font-size:13px;color:white;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="ebrToastBar"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')

    <script>
        window.APP_CONFIG = {
            API_BASE: "{{ url('/api/admin/editorial-board-roles') }}",
            JOURNALS_API: "{{ url('/api/admin/journals') }}?page=1&per_page=100"
        };
    </script>

    <script src="{{ asset('assets/js/admin/editorialboardrole.js') }}"></script>
@endsection