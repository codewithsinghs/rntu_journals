@extends('layouts.admin')

@section('content')
    @php
        $pageKeys = config('menu.pages', []);
    @endphp


    <!-- Table -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards" style="overflow:visible">

                <div class="heading">Site Navigations</div>

                <div class="table-controls">
                    <button class="add-btn" onclick="openCreateModal()">Add Menu</button> &nbsp; &nbsp;
                    <input type="text" id="searchInput" class="form-control" oninput="onSearchInput()"
                        placeholder="Search menus..." style="max-width: 260px;">
                </div>

                <div class="table-container" style="margin: 0;">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Menu Name</th>
                                <th>Location</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="menuTable">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Loading…</td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <div class="pagination-style">
                    <div class="pagination-style-group">
                        <span>Show</span>
                        <select id="perPage" class="form-select form-select-sm" style="width: 100px;"
                            onchange="onPerPageChange()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>Entries</span>
                    </div>
                </div>

                <div class="pagination-footet-two ">
                    <div id="entriesInfo">Showing 0 to 0 of 0 entries</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

    <!-- ------------------- Popup Start ------------------- -->


    <!-- Create / Edit Menu Modal -->
    <div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="menuForm">

                    <div class="modal-body">

                        <div class="top">
                            <div class="pop-title" id="menuModalTitle">Add Menu</div>
                        </div>

                        <div class="middle-3 middle">

                            <input type="hidden" id="menuId" value="">

                            <!-- Name -->
                            <span class="input-set">
                                <label>Name</label>
                                <input type="text" id="name" required>
                                <div class="invalid-feedback" id="nameError"></div>
                            </span>

                            <!-- Location -->
                            <span class="input-set">
                                <label>Location</label>
                                <select id="location" class="form-select" required onchange="onLocationChange()">
                                    <option value="topbar">Topbar</option>
                                    <option value="header">Header</option>
                                    <option value="footer">Footer</option>
                                </select>
                                <div class="invalid-feedback" id="locationError"></div>
                            </span>

                            <!-- Active -->
                            <span class="input-set">
                                <label>Active</label>
                                <input type="checkbox" id="is_active" role="switch" checked>
                            </span>

                        </div>

                        <div class="reason">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="adm-label mb-0">Menu Items</label>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTopLevelItem()">+
                                    Add Item</button>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-3" id="nestedHint" style="display:none;">
                                This menu's location is <strong>Header</strong> — you can nest child items by clicking
                                <strong>"+ Child"</strong> on any item.
                            </div>

                            <div class="alert alert-secondary py-2 px-3 small mb-3">
                                <strong>Page visibility</strong> on each item is optional. Leave it on
                                <strong>"Everywhere"</strong> to show the item on every page. Switch to
                                <strong>"Only on"</strong> and pick pages to show it ONLY there (e.g. Editorial Board
                                only on the Journal page). Switch to <strong>"Hide on"</strong> to show it everywhere
                                EXCEPT the pages you pick.
                            </div>

                            <div id="itemsTree"></div>

                            <div id="noItemsMsg" class="text-center text-muted py-4 border rounded bg-white">
                                No items yet. Click "Add Item" to begin.
                            </div>
                        </div>

                        <!-- Btn -->
                        <div class="bottom-btn">
                            <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                            </button>
                            <button type="submit" class="blue" id="saveBtn"> Save Menu</button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Confirm Delete Menu Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-4 px-4">
                    <div class="delete-icon-wrap mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.8rem;"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">Delete Menu</h6>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        Are you sure you want to delete <strong id="deleteMenuName"></strong>?<br>
                        <span class="small">All its items will be permanently removed.</span>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Remove Item Modal -->
    <div class="modal fade" id="confirmRemoveItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-4 px-4">
                    <div class="delete-icon-wrap mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.8rem;"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">Remove Item</h6>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        Are you sure you want to remove <strong id="removeItemLabel"></strong>?<br>
                        <span class="small" id="removeItemWarning">This action cannot be undone.</span>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmRemoveItemBtn">Yes, Remove</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Success Modal -->
    <div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-5 px-4">
                    <div class="success-icon-box">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                            <path d="M5 13l4 4L19 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h5 class="fw-semibold mb-1">Deleted Successfully</h5>
                    <p class="text-muted small mb-0" id="deleteSuccessMsg"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-primary px-5" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Success Modal -->
    <div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-5 px-4">
                    <div class="success-icon-box">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                            <path d="M5 13l4 4L19 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h5 class="fw-semibold mb-1" id="saveSuccessTitle">Saved Successfully</h5>
                    <p class="text-muted small mb-0" id="saveSuccessMsg"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-primary px-5" id="saveSuccessOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ------------------- Popup END ------------------- -->

    <!-- Toast -->
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
    <script type="application/json" id="pageKeysData">{!! json_encode($pageKeys ?? []) !!}</script>
    <script src="{{ asset('assets/js/admin/Menus.js') }}"></script>
@endsection
