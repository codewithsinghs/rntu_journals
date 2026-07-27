@extends('layouts.admin')

@section('content')
    {{-- Volumes List --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Volumes List
                </div>

                <div class="table-controls">
                    <button type="button" class="add-btn" onclick="openCreateModal()">+ Add Volume</button>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Journal</th>
                                <th>Volume</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Current</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="volume-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Loading…</td>
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

    <!-- Toast Container -->
    <div id="toastContainer">
    </div>


    <!-- Create / Edit Modal -->
    <div class="modal fade" id="volumeModal" tabindex="-1" aria-labelledby="volumeModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form id="volumeForm">

                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="volume_id" name="id">

                        <div class="top">
                            <div class="pop-title" id="volumeModalTitle">Add Volume</div>
                        </div>

                        <div class="middle-3 middle">

                            <!-- Journal-->
                            <span class="input-set">
                                <label>Journal <span class="text-danger">*</span></label>
                                <select class="form-select" name="journal_id" id="journal_id" required>
                                    <option value="">Select journal...</option>
                                </select>
                            </span>

                            <!-- Volume -->
                            <span class="input-set">
                                <label>Volume <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" name="volume" id="volume"
                                    placeholder="e.g. Volume 12" required>
                            </span>

                            <!-- Year -->
                            <span class="input-set">
                                <label>Year <span class="text-danger">*</span></label>
                                <input type="text" class="content_show" name="year" id="year"
                                    placeholder="e.g. 2025">
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

                        <!-- Role -->
                        <div class=" mt-5">
                            <input class="form-check-input" type="checkbox" name="is_current" id="is_current">
                            <label class="form-check-label" for="is_current">Set as current volume for this journal</label>
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
                        <div class="pop-title">Volume Details</div>
                    </div>

                    <div class="middle-3 middle"></div>

                    <!-- Data Load -->
                    <div class="reason" id="viewModalBody">Loading...</div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/volume.js') }}"></script>
@endsection
