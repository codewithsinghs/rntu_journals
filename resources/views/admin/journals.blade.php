@extends('layouts.admin')

@section('content')
    {{--  Journal Statistics (card_d pattern) --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Journal Statistics</div>

                <div class="grid_colums_card">

                    <div class="card_d">
                        <div class="card-content">
                            <p>Total Journals</p>
                            <h3 id="statTotal">0 Journals</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_1.png">
                        </div>
                    </div>

                    <div class="card_d">
                        <div class="card-content">
                            <p>Active Journals</p>
                            <h3 id="statActive">0 Journals</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_2.png">
                        </div>
                    </div>

                    <div class="card_d">
                        <div class="card-content">
                            <p>Inactive Journals</p>
                            <h3 id="statInactive">0 Journals</h3>
                        </div>
                        <div class="card-image">
                            <img src="/storage/dashboard/d_2.png">
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{--  Journal List (status-table pattern) --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">Journal List</div>

                <div class="table-controls">
                    <button class="add-btn" id="openAddBtn" onclick="openCreateModal()">Add Journal</button> &nbsp; &nbsp;
                    <input type="text" id="searchInput" class="form-control form-control-sm" oninput="onSearchInput()"
                        placeholder="Search by title..." style="max-width: 240px;">
                </div>

                <div id="tableLoading" class="text-center py-4">Loading...</div>
                <div id="tableEmpty" class="text-center py-4" style="display:none;">No journals found.</div>

                <div class="table-container" style="margin: 0; display:none;" id="tableWrap">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Abbreviation</th>
                                <th>ISSN</th>
                                <th>Volume / Issue</th>
                                <th>Sequence</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="journalTableBody"></tbody>
                    </table>
                </div>

                <div class="jm-toolbar">
                    <div>
                        <select id="perPage" class="form-select form-select-sm" style="width: 90px; display:inline-block;"
                            onchange="onPerPageChange()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
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

    {{--  Add / Edit Journal Modal (input-set / reason / bottom-btn pattern) --}}
    <div class="modal fade" id="AddJournal" tabindex="-1" aria-labelledby="AddJournalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="journalForm">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title" id="journalModalTitle">Add Journal</div>
                        </div>

                        <input type="hidden" id="journal_id">

                        <div class="middle-3 middle">

                            {{-- Cover Image --}}
                            <span class="input-set">
                                <label>Cover Image</label>
                                <input type="file" id="cover_image" accept="image/*">
                                <img id="coverPreviewCurrent" class="journal-page-img-show" style="display:none;">
                            </span>

                            {{-- Title --}}
                            <span class="input-set">
                                <label>Title *</label>
                                <input type="text" id="title" placeholder="e.g. Anusandhan (RNTUJ-AN)">
                            </span>

                            {{-- Heading / title_2 --}}
                            <span class="input-set">
                                <label>Heading (secondary title)</label>
                                <input type="text" id="heading_1" placeholder="e.g. Our Flagship Journal">
                            </span>

                            {{-- Abbreviation --}}
                            <span class="input-set">
                                <label>Abbreviation</label>
                                <input type="text" id="abbreviation"
                                    placeholder="e.g. Int. Res. J. Multidiscip. Technovation">
                            </span>

                            {{-- Badge --}}
                            <span class="input-set">
                                <label>Badge</label>
                                <input type="text" id="badge">
                            </span>

                            {{-- ISSN --}}
                            <span class="input-set">
                                <label>e-ISSN</label>
                                <input type="text" id="e_issn">
                            </span>

                            <span class="input-set">
                                <label>p-ISSN</label>
                                <input type="text" id="p_issn">
                            </span>

                            <span class="input-set">
                                <label>ISSN Online</label>
                                <input type="text" id="issn_online">
                            </span>

                            {{-- Volume / Issue --}}
                            <span class="input-set">
                                <label>Volume</label>
                                <input type="text" id="volume">
                            </span>

                            <span class="input-set">
                                <label>Issue</label>
                                <input type="text" id="issue">
                            </span>

                            <span class="input-set">
                                <label>Latest Volume</label>
                                <input type="text" id="latest_volume" placeholder="e.g. Sept, 2025">
                            </span>

                            {{-- Publishing info --}}
                            <span class="input-set">
                                <label>Publication Language</label>
                                <input type="text" id="publication_language">
                            </span>

                            <span class="input-set">
                                <label>Publishing Frequency</label>
                                <input type="text" id="publishing_frequency" placeholder="e.g. Bimonthly">
                            </span>

                            <span class="input-set">
                                <label>Publishing Months</label>
                                <input type="text" id="publishing_months"
                                    placeholder="e.g. Jan, Mar, May, Jul, Sept, Nov">
                            </span>

                            {{-- Indexing / review timeline --}}
                            <span class="input-set">
                                <label>Impact Factor</label>
                                <input type="text" id="indexing_impact_factor">
                            </span>

                            <span class="input-set">
                                <label>Time to First Decision</label>
                                <input type="text" id="time_to_first_decision">
                            </span>

                            <span class="input-set">
                                <label>Time to Review</label>
                                <input type="text" id="time_to_review">
                            </span>

                            <span class="input-set">
                                <label>Acceptance to Publication</label>
                                <input type="text" id="acceptance_to_publication">
                            </span>

                            {{-- Article Template --}}
                            <span class="input-set">
                                <label>Article Template URL</label>
                                <input type="text" id="article_template_url">
                            </span>

                            {{-- Sequence + Status --}}
                            <span class="input-set">
                                <label>Sequence</label>
                                <input type="number" id="sequence" value="0" min="0">
                            </span>

                            <span class="input-set">
                                <label>Status</label>
                                <select class="form-select" id="is_active">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </span>

                        </div>

                        {{-- Redirect / CTA Buttons --}}
                        <div class="middle mt-4">

                            <span class="input-set">
                                <label>"View All Issues" Label</label>
                                <input type="text" id="view_all_issues_label" placeholder="e.g. View All Issues">
                            </span>

                            <span class="input-set">
                                <label>"View All Issues" Link</label>
                                <input type="text" id="view_all_issues_link" placeholder="https://...">
                            </span>

                            <span class="input-set">
                                <label>"Explore Journals" Label</label>
                                <input type="text" id="explore_journals_label" placeholder="e.g. Explore Journals">
                            </span>

                            <span class="input-set">
                                <label>"Explore Journals" Link</label>
                                <input type="text" id="explore_journals_link" placeholder="https://...">
                            </span>

                        </div>

                        {{-- Description (CKEditor) --}}
                        <div class="reason">
                            <label>Journal Description</label>
                            <div id="ck_description" class="ckeditor-wrapper"></div>
                            <textarea class="d-none" id="description"></textarea>
                        </div>

                        {{-- Aim & Scope --}}
                        <div class="reason">
                            <label>Aim & Scope Title</label>
                            <input type="text" id="aim_and_scope_title" class="form-control"
                                placeholder="e.g. Aim & Scope">
                        </div>

                        <div class="reason">
                            <label>Aim & Scope</label>
                            <div id="ck_aim_and_scope" class="ckeditor-wrapper"></div>
                            <textarea class="d-none" id="aim_and_scope"></textarea>
                        </div>

                        {{-- Fields Covered --}}
                        <div class="reason">
                            <label>Fields Covered</label>
                            <div id="fieldsCoveredContainer"></div>
                            <button type="button" class="edit-btn mt-2" id="addFieldBtn">+ Add Field</button>
                        </div>

                    </div>

                    <div class="bottom-btn mb-5">
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close">
                            Cancel</button>
                        <button type="submit" class="green" id="saveJournalBtn"> <span id="saveJournalBtnText">Create
                                Journal</span> </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="delete_popup" tabindex="-1" aria-labelledby="delete_popupLabel" aria-hidden="true">
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
                            Do you really want to delete "<strong id="deleteJournalName"></strong>"? <br>
                            The cover image will also be deleted. This action cannot be undone.
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

    {{--  Toast --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="ecToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="ecToastIcon"></span>
                    <div>
                        <div id="ecToastTitle" class="fw-semibold" style="font-size:14px; color:white;"></div>
                        <div id="ecToastMsg" class="opacity-75" style="font-size:13px; color:white;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="ecToastBarInner"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<<<<<<< HEAD
    {{-- CKEditor 5 CDN --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script src="{{ asset('assets/js/admin/journals.js') }}"></script>
@endsection
=======

    <script src="{{ asset('assets/js/admin/journals.js') }}"></script>
@endsection
>>>>>>> main
