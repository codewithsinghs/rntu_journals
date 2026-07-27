@extends('layouts.admin')

@section('content')
    <!-- Memder List -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Editorial Board Members</div>

                <div class="table-controls" style="display:flex; align-items:center;">
                    <button class="add-btn d-none" id="ebEmpty" onclick="document.getElementById('openEbModal').click()">No
                        editorial board members found.</button>

                    <!-- Right-aligned group: Add button + Journal filter -->
                    <div style="display:flex; align-items:center;">
                        <button class="add-btn" id="openEbModal" style="width: 100%;">Add Member</button> &nbsp; &nbsp;
                        <select id="journalFilter" class="form-select form-select-sm">
                            <option value="">All Journals</option>
                        </select>
                    </div>
                </div>

                <div class="table-container" style="margin: 0;" id="ebTableWrap">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Journal</th>
                                <th>Role</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Seq</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ebTbody">
                            <tr>
                                <div id="ebLoading" class="text-center py-5">
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
    <div class="modal fade" id="ebModal" tabindex="-1" aria-labelledby="ebModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="ebForm" novalidate>
                    @csrf
                    <input type="hidden" id="ebId">
                    <input type="hidden" id="ebMethod" value="POST">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title" id="ebModalTitle">Add Member</div>
                        </div>

                        <div class="middle-3 middle">

                            <!-- Journal -->
                            <span class="input-set">
                                <label>Journal</label>
                                <select class="form-select" id="journal_id" name="journal_id">
                                    <option value="">— None —</option>
                                </select>
                                <div class="invalid-feedback" id="err_journal_id"></div>
                            </span>

                            <!-- Role -->
                            <span class="input-set">
                                <label>Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select role</option>
                                    <option value="Editor-in-Chief">Editor-in-Chief</option>
                                    <option value="Managing Editor">Managing Editor</option>
                                    <option value="Executive Editor">Executive Editor</option>
                                    <option value="Editors">Editors</option>
                                    <option value="Associate Editors">Associate Editors</option>
                                    <option value="Members">Members</option>
                                </select>
                                <div class="invalid-feedback" id="err_role"></div>
                            </span>

                            <!-- Name -->
                            <span class="input-set">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Name" required>
                                <div class="invalid-feedback" id="err_name"></div>
                            </span>

                            <!-- Designation -->
                            <span class="input-set">
                                <label>Designation</label>
                                <input type="text" id="designation" name="designation" placeholder="Name">
                                <div class="invalid-feedback" id="err_department"></div>
                            </span>

                            <!-- Department -->
                            <span class="input-set">
                                <label>Department</label>
                                <input type="text" id="department" name="department" placeholder="Name">
                            </span>

                            <!-- Institute -->
                            <span class="input-set">
                                <label>Institute</label>
                                <input type="text" id="institute" name="institute">
                                <div class="invalid-feedback" id="err_institute"></div>
                            </span>

                            <!-- University / Organization -->
                            <span class="input-set">
                                <label>University / Organization</label>
                                <input type="text" id="university_or_org" name="university_or_org">
                                <div class="invalid-feedback" id="err_university_or_org"></div>
                            </span>

                            <!-- City -->
                            <span class="input-set">
                                <label>City</label>
                                <input type="text" id="city" name="city" placeholder="Name">
                                <div class="invalid-feedback" id="err_city"></div>
                            </span>

                            <!-- Email -->
                            <span class="input-set">
                                <label>Email</label>
                                <input type="email" id="email" name="email">
                                <div class="invalid-feedback" id="err_email"></div>
                            </span>

                            <!-- ORCID URL -->
                            <span class="input-set">
                                <label>ORCID URL</label>
                                <input type="url" id="orcid_url" name="orcid_url">
                                <div class="invalid-feedback" id="err_orcid_url"></div>
                            </span>

                            <!-- Scopus URL -->
                            <span class="input-set">
                                <label>Scopus URL</label>
                                <input type="url" id="scopus_url" name="scopus_url">
                                <div class="invalid-feedback" id="err_scopus_url"></div>
                            </span>

                            <!-- Web of Science URL -->
                            <span class="input-set">
                                <label>Web of Science URL</label>
                                <input type="url" id="web_of_science_url" name="web_of_science_url">
                                <div class="invalid-feedback" id="err_web_of_science_url"></div>
                            </span>

                            <!-- Profile Image  -->
                            <span class="input-set">
                                <label>Profile Image (JPEG / PNG / WEBP, max) </label>
                                <input type="file" id="profile_image" name="profile_image"
                                    accept=".jpg,.jpeg,.png,.webp">
                                <div class="invalid-feedback" id="err_profile_image"></div>
                                <img id="ebImagePreview" src="" class="mt-2 rounded d-none"
                                    style="height:60px;width:60px;object-fit:cover;">
                            </span>

                            <!-- Sequence -->
                            <span class="input-set">
                                <label>Sequence</label>
                                <input type="number" min="0" id="sequence" name="sequence" value="0">
                                <div class="invalid-feedback" id="err_sequence"></div>
                            </span>

                            <!-- checklist -->
                            <div class="rjf-checklist">
                                <label for="is_active">
                                    <input type="checkbox" role="switch" id="is_active" name="is_active" checked>
                                    Active
                                </label>
                            </div>

                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">

                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>

                            <button type="submit" class="blue" id="ebSaveBtn">
                                <span id="ebSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                                <span id="ebSaveBtnText">Save</span>
                            </button>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div class="modal fade" id="ebDeleteModal" tabindex="-1" aria-labelledby="ebDeleteModalLabel" aria-hidden="true">
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
                            The member and their profile image will be permanently removed.
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="ebConfirmDeleteBtn"> <span id="ebDeleteSpinner">
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
        <div id="ebToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="ebToastIcon"></span>
                    <div>
                        <div id="ebToastTitle" class="fw-semibold" style="font-size:14px;"></div>
                        <div id="ebToastMsg" class="opacity-75" style="font-size:13px;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="ebToastBar"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')

    <script>
        window.APP_CONFIG = {
            API_BASE: "{{ url('/api/admin/editorial-board') }}",
            JOURNALS_API: "{{ url('/api/admin/journals') }}?page=1&per_page=100"
        };
    </script>

    <script src="{{ asset('assets/js/admin/editorialboard.js') }}"></script>
@endsection
