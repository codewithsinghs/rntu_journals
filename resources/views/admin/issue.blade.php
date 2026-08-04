@extends('layouts.admin')

@section('content')
    {{-- Issue List --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Issue List
                </div>

                <div class="table-controls">
                    <button type="button" class="add-btn" onclick="openCreateModal()">+ Add Issue</button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Journal</th>
                                <th>Volume</th>
                                <th>Issue</th>
                                <th>Year</th>
                                <th>Published Date</th>
                                <th>Status</th>
                                <th>Current</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="issue-table-body">
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Loading…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-footet-two ">
                    <div id="paginationInfo"></div>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-end" id="pagination"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

    <!-- Create / Edit Modal -->
    <div class="modal fade" id="issueModal" tabindex="-1" aria-labelledby="issueModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="issueForm">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="issue_id" name="id">

                        <div class="top">
                            <div class="pop-title" id="issueModalTitle">Add Volume</div>
                        </div>

                        <div class="middle-3 middle">

                            <!-- Select journal-->
                            <span class="input-set">
                                <label>Journal <span class="text-danger">*</span></label>
                                <select class="form-select" name="journal_id" id="journal_id" required
                                    onchange="loadVolumeOptions(this.value)">
                                    <option value="">Select journal...</option>
                                </select>
                            </span>

                            <!-- Select volume -->
                            <span class="input-set">
                                <label>Volume <span class="text-danger">*</span></label>
                                <select class="form-select" name="volume_id" id="volume_id" required>
                                    <option value="">Select volume...</option>
                                </select>
                            </span>

                            <!-- Issue -->
                            <span class="input-set">
                                <label>Issue <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="content_show" name="issue" id="issue"
                                    placeholder="e.g. 3" inputmode="numeric"
                                    pattern="\d{1}"
                                    maxlength="1"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    required>
                            </span>

                            <span class="input-set">
                                <label>Year <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="content_show"
                                    name="year"
                                    id="year"
                                    placeholder="e.g. 2025"
                                    inputmode="numeric"
                                    pattern="\d{4}"
                                    maxlength="4"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)"
                                    required>
                            </span>


                            <!-- Published Date -->
                            <span class="input-set">
                                <label>Published Date <span class="text-danger">*</span></label>
                                <input type="date" class="content_show" name="published_date" id="published_date">
                            </span>

                            <!-- Status -->
                            <span class="input-set">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published/Archived</option>
                                </select>
                            </span>

                        </div>

                        <!-- Current -->
                        <div class=" mt-5">
                            <input class="form-check-input" type="checkbox" name="is_current" id="is_current">
                            <label class="form-check-label" for="is_current">Set as current issue for this volume</label>
                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">
                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>
                            <button type="submit" class="blue">Save</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title">Issue Details</div>
                    </div>

                    <div class="middle-3 middle"></div>

                    <!-- Data Load -->
                    <div class="reason" id="viewModalBody">Loading...</div>

                </div>
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
                            Do you want to delete issue "<strong id="deleteIssueName"></strong>"?
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="confirmDeleteIssueBtn"> Delete </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Keep it
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/issue.js') }}"></script>
@endsection