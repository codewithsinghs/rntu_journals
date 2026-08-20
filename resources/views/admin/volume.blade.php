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
                                <th>S.No.</th>
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
                                <input
                                    type="text"
                                    class="content_show"
                                    name="volume"
                                    id="volume"
                                    placeholder="e.g. 6"
                                    inputmode="numeric"
                                    pattern="\d{1}"
                                    maxlength="1"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    required>
                            </span>

                            <!-- Year -->
<span class="input-set">
    <label>Year <span class="text-danger">*</span></label>
    <select class="form-select" name="year" id="year" required>
        <option value="">Select year...</option>
    </select>
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
                            Do you want to delete volume "<strong id="deleteVolumeName"></strong>"?
                        </span>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="confirmDeleteVolumeBtn"> Delete </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Keep it
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Toast --}}
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
    <script src="{{ asset('assets/js/admin/volume.js') }}"></script>
@endsection