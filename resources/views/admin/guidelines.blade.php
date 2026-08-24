@extends('layouts.admin')

@section('content')

    {{-- Guidelines List --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Guidelines</div>

                <div class="table-controls">
                    <button class="add-btn" id="openAddBtn">+ Add Guideline</button> &nbsp; &nbsp;
                    <input type="text" id="searchInput" class="form-control form-control-sm"
                        placeholder="Search by journal or heading..." style="max-width: 240px;">
                </div>

                <div id="tableLoading" class="text-center py-4">Loading...</div>
                <div id="tableEmpty" class="text-center py-4" style="display:none;">No guidelines found.</div>

                <div class="table-container" style="margin: 0; display:none;" id="tableWrap">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Journal</th>
                                <th>Author Heading</th>
                                <th>Process Heading</th>
                                <th>Manuscript Heading</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="guidelinesTableBody"></tbody>
                    </table>
                </div>

                <div class="jm-toolbar">
                    <div>
                        <select id="perPage" class="form-select form-select-sm" style="width: 90px; display:inline-block;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-muted small ms-1">entries</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="entriesInfo">Showing 0 to 0 of 0 entries</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

    {{-- Add / Edit Guideline Modal (matches Journal modal's input-set / reason / bottom-btn pattern) --}}
    <div class="modal fade" id="GuidelineModal" tabindex="-1" aria-labelledby="GuidelineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="glForm" novalidate>
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title" id="glModalTitle">Add Guideline</div>
                        </div>

                        <input type="hidden" id="glId">
                        <input type="hidden" id="glMethod" value="POST">

                        {{-- Journal selector --}}
                        <div class="middle-3 middle">
                            <span class="input-set">
                                <label>Journal <span class="text-danger">*</span></label>
                                <select class="form-select" id="journal_id" name="journal_id">
                                    <option value="">Select journal…</option>
                                </select>
                                <div class="invalid-feedback d-block" id="err_journal_id"></div>
                            </span>
                        </div>

                        {{-- Instructions for Authors --}}
                        <div class="inner_fp">
                            <div class="ssid">Instructions for Authors</div>

                            <div class="middle-3 middle">
                                <span class="input-set">
                                    <label>Badge <span class="gl-hint">AUTHOR GUIDELINES</span></label>
                                    <input type="text" class="content_show" id="author_badge" name="author_badge" placeholder="AUTHOR GUIDELINES">
                                    <div class="invalid-feedback d-block" id="err_author_badge"></div>
                                </span>
                                <span class="input-set">
                                    <label>Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="author_heading" name="author_heading" placeholder="Instructions for Authors" required>
                                    <div class="invalid-feedback d-block" id="err_author_heading"></div>
                                </span>
                            </div>

                            <div class="reason">
                                <label>Description <span class="text-danger">*</span></label>
                                <div id="ck_author_description" class="gl-ck-wrap"></div>
                                <textarea class="content_show d-none" id="author_description" name="author_description"></textarea>
                                <div class="gl-ck-error" id="err_author_description"></div>
                            </div>
                        </div>

                        {{-- Submission Process --}}
                        <div class="inner_fp">
                            <div class="ssid">Submission Process</div>

                            <div class="middle-3 middle">
                                <span class="input-set">
                                    <label>Badge <span class="gl-hint">PROCESS</span></label>
                                    <input type="text" class="content_show" id="process_badge" name="process_badge" placeholder="PROCESS">
                                    <div class="invalid-feedback d-block" id="err_process_badge"></div>
                                </span>
                                <span class="input-set">
                                    <label>Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="process_heading" name="process_heading" placeholder="Submission Process" required>
                                    <div class="invalid-feedback d-block" id="err_process_heading"></div>
                                </span>
                            </div>

                            <div class="reason">
                                <label>Description <span class="text-danger">*</span></label>
                                <div id="ck_process_description" class="gl-ck-wrap"></div>
                                <textarea class="content_show d-none" id="process_description" name="process_description"></textarea>
                                <div class="gl-ck-error" id="err_process_description"></div>
                            </div>
                        </div>

                        {{-- New Manuscript --}}
                        <div class="inner_fp">
                            <div class="ssid">New Manuscript</div>

                            <div class="middle-3 middle">
                                <span class="input-set">
                                    <label>Badge <span class="gl-hint">MANUSCRIPT PREPARATION</span></label>
                                    <input type="text" class="content_show" id="manuscript_badge" name="manuscript_badge" placeholder="MANUSCRIPT PREPARATION">
                                    <div class="invalid-feedback d-block" id="err_manuscript_badge"></div>
                                </span>
                                <span class="input-set">
                                    <label>Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="manuscript_heading" name="manuscript_heading" placeholder="New Manuscripts" required>
                                    <div class="invalid-feedback d-block" id="err_manuscript_heading"></div>
                                </span>
                            </div>

                            <div class="reason">
                                <label>Description <span class="text-danger">*</span></label>
                                <div id="ck_manuscript_description" class="gl-ck-wrap"></div>
                                <textarea class="content_show d-none" id="manuscript_description" name="manuscript_description"></textarea>
                                <div class="gl-ck-error" id="err_manuscript_description"></div>
                            </div>
                        </div>

                        {{-- Formatting --}}
                        <div class="inner_fp">
                            <div class="ssid">Formatting</div>

                            <div class="middle-3 middle">
                                <span class="input-set">
                                    <label>Badge <span class="gl-hint">DOCUMENT FORMAT REFERENCE</span></label>
                                    <input type="text" class="content_show" id="formatting_badge1" name="formatting_badge1" placeholder="DOCUMENT FORMAT REFERENCE">
                                    <div class="invalid-feedback d-block" id="err_formatting_badge1"></div>
                                </span>
                                <span class="input-set">
                                    <label>Badge 2 <span class="gl-hint">e.g. IEEE STYLE</span></label>
                                    <input type="text" class="content_show" id="formatting_badge2" name="formatting_badge2" placeholder="IEEE STYLE">
                                    <div class="invalid-feedback d-block" id="err_formatting_badge2"></div>
                                </span>
                                <span class="input-set">
                                    <label>Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="formatting_heading" name="formatting_heading" placeholder="Formatting" required>
                                    <div class="invalid-feedback d-block" id="err_formatting_heading"></div>
                                </span>
                            </div>

                            <div class="reason">
                                <label>Description <span class="text-danger">*</span></label>
                                <div id="ck_formatting_description" class="gl-ck-wrap"></div>
                                <textarea class="content_show d-none" id="formatting_description" name="formatting_description"></textarea>
                                <div class="gl-ck-error" id="err_formatting_description"></div>
                            </div>
                        </div>

                        {{-- Page Layout --}}
                        <div class="inner_fp">
                            <div class="ssid">Page Layout</div>

                            <div class="middle-3 middle">
                                <span class="input-set">
                                    <label>Badge <span class="gl-hint">PAGE LAYOUT</span></label>
                                    <input type="text" class="content_show" id="layout_badge1" name="layout_badge1" placeholder="PAGE LAYOUT">
                                    <div class="invalid-feedback d-block" id="err_layout_badge1"></div>
                                </span>
                                <span class="input-set">
                                    <label>Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="layout_heading" name="layout_heading" placeholder="New Manuscripts" required>
                                    <div class="invalid-feedback d-block" id="err_layout_heading"></div>
                                </span>
                            </div>

                            <div class="reason">
                                <label>Description <span class="text-danger">*</span></label>
                                <div id="ck_layout_description" class="gl-ck-wrap"></div>
                                <textarea class="content_show d-none" id="layout_description" name="layout_description"></textarea>
                                <div class="gl-ck-error" id="err_layout_description"></div>
                            </div>
                        </div>

                        {{-- Acknowledgement --}}
                        <div class="inner_fp">
                            <div class="ssid">Acknowledgement</div>

                            <div class="middle-3 middle">
                                <span class="input-set">
                                    <label>Badge <span class="gl-hint">RNTU JOURNALS</span></label>
                                    <input type="text" class="content_show" id="acknowlegdement_badge1" name="acknowlegdement_badge1" placeholder="RNTU JOURNALS">
                                    <div class="invalid-feedback d-block" id="err_acknowlegdement_badge1"></div>
                                </span>
                                <span class="input-set">
                                    <label>Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="acknowlegdement_heading" name="acknowlegdement_heading" placeholder="New Manuscripts" required>
                                    <div class="invalid-feedback d-block" id="err_acknowlegdement_heading"></div>
                                </span>
                            </div>

                            <div class="reason">
                                <label>Description <span class="text-danger">*</span></label>
                                <div id="ck_acknowlegdement_description" class="gl-ck-wrap"></div>
                                <textarea class="content_show d-none" id="acknowlegdement_description" name="acknowlegdement_description"></textarea>
                                <div class="gl-ck-error" id="err_acknowlegdement_description"></div>
                            </div>
                        </div>

                    </div>

                    <div class="bottom-btn mb-5">
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button type="button" class="green" id="glSaveBtn">
                            <span id="glSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            <span id="glSaveBtnText">Save</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="glDeleteModal" tabindex="-1" aria-labelledby="glDeleteModalLabel" aria-hidden="true">
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
                        <span>Do you really want to delete the guidelines for "<strong id="deleteGuidelineJournal"></strong>"? This action cannot be undone.</span>
                    </div>
                    <div class="bottom-btn">
                        <button type="button" class="red" id="glConfirmDeleteBtn">
                            <span id="glDeleteSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Delete
                        </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close">Keep it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="glToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="glToastIcon" style="color:white;"></span>
                    <div>
                        <div id="glToastTitle" class="fw-semibold" style="font-size:14px; color:white;"></div>
                        <div id="glToastMsg" class="opacity-75" style="font-size:13px; color:white;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="glToastBar" style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/guidelines.js') }}"></script>
@endsection