@extends('layouts.admin')

@section('content')

{{-- Pages List --}}
<section class="inner_p">
    <div class="content_top_wrapper">
        <div class="p_cards">

            <div class="heading">Pages</div>

            <div class="table-controls">
                <button class="add-btn" id="openAddBtn">+ Add Page</button> &nbsp; &nbsp;
                <input type="text" id="searchInput" class="form-control form-control-sm"
                    placeholder="Search by title or slug..." style="max-width: 240px;">
            </div>

            <div id="tableLoading" class="text-center py-4">Loading...</div>
            <div id="tableEmpty" class="text-center py-4" style="display:none;">No pages found.</div>

            <div class="table-container" style="margin: 0; display:none;" id="tableWrap">
                <table class="status-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Homepage</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="pagesTableBody"></tbody>
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

{{-- Add / Edit Page Modal --}}
<div class="modal fade" id="PageModal" tabindex="-1" aria-labelledby="PageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <form id="pgForm" novalidate>
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title" id="pgModalTitle">Add Page</div>
                    </div>

                    <input type="hidden" id="pgId">
                    <input type="hidden" id="pgMethod" value="POST">

                    <div class="middle-3 middle">
                        <span class="input-set">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" class="content_show" id="title" name="title" placeholder="Page title" required>
                            <div class="invalid-feedback d-block" id="err_title"></div>
                        </span>
                        <span class="input-set">
                            <label>Slug <span class="gl-hint">auto-generated if left blank</span></label>
                            <input type="text" class="content_show" id="slug" name="slug" placeholder="page-slug">
                            <div class="invalid-feedback d-block" id="err_slug"></div>
                        </span>
                    </div>

                    <div class="middle-3 middle">
                        <span class="input-set">
                            <label>Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published">Published</option>
                                <option value="draft" selected>Draft</option>
                            </select>
                        </span>
                        <span class="input-set">
                            <label class="d-block">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_homepage" name="is_homepage">
                                <label class="form-check-label" for="is_homepage">Set as homepage</label>
                            </div>
                        </span>
                    </div>

                    <div class="inner_fp">
                        <div class="ssid">Content</div>
                        <div class="reason">
                            <label>Body <span class="text-danger">*</span></label>
                            <div id="ck_content" class="gl-ck-wrap"></div>
                            <textarea class="content_show d-none" id="content" name="content"></textarea>
                            <div class="gl-ck-error" id="err_content"></div>
                        </div>
                    </div>

                    <div class="inner_fp">
                        <div class="ssid">SEO / Meta</div>

                        <div class="middle-3 middle">
                            <span class="input-set">
                                <label>Meta Title</label>
                                <input type="text" class="content_show" id="meta_title" name="meta_title" placeholder="Meta title">
                                <div class="invalid-feedback d-block" id="err_meta_title"></div>
                            </span>
                            <span class="input-set">
                                <label>Meta Image</label>
                                <input type="file" class="form-control" id="meta_image" name="meta_image" accept="image/*">
                                <div class="invalid-feedback d-block" id="err_meta_image"></div>
                            </span>
                        </div>

                        <div class="reason">
                            <label>Meta Description</label>
                            <textarea class="content_show" id="meta_description" name="meta_description" rows="3" placeholder="Meta description"></textarea>
                            <div class="invalid-feedback d-block" id="err_meta_description"></div>
                        </div>
                    </div>

                </div>

                <div class="bottom-btn mb-5">
                    <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="button" class="green" id="pgSaveBtn">
                        <span id="pgSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                        <span id="pgSaveBtnText">Save</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal fade" id="pgDeleteModal" tabindex="-1" aria-labelledby="pgDeleteModalLabel" aria-hidden="true">
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
                    <span>Do you really want to delete the page "<strong id="deletePageTitle"></strong>"? This action cannot be undone.</span>
                </div>
                <div class="bottom-btn">
                    <button type="button" class="red" id="pgConfirmDeleteBtn">
                        <span id="pgDeleteSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
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
    <div id="pgToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <span id="pgToastIcon" style="color:white;"></span>
                <div>
                    <div id="pgToastTitle" class="fw-semibold" style="font-size:14px; color:white;"></div>
                    <div id="pgToastMsg" class="opacity-75" style="font-size:13px; color:white;"></div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
        <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
            <div id="pgToastBar" style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/admin/pages.js') }}"></script>
@endsection