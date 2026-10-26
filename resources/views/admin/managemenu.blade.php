@extends('layouts.admin')

@section('content')
    @php
        $pageKeys = config('menu.pages', []);
    @endphp

    <!-- Manage Menu -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards" style="overflow:visible">

                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="heading" id="manageMenuHeading">Manage Menu</div>

                </div>

                <div id="pageLoader" class="text-center text-muted py-5">Loading menu…</div>

                <form id="menuForm" class="d-none">

                    <input type="hidden" id="menuId" value="{{ $id }}">
                    <input type="hidden" id="name" value="">
                    <input type="hidden" id="location" value="">
                    <input type="hidden" id="is_active" value="1">

                    <div class="reason mt-3">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="adm-label mb-0"></label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="goToItemForm(null)">
                                    <i class="bi bi-plus-lg"></i> Add Item
                                </button>
                                <a href="{{ route('admin.menus') }}" class="back-pill-btn">
                                    <i class="bi bi-arrow-left"></i> Back to Menu
                                </a>
                            </div>
                        </div>

                        <p class="text-muted small mb-2">Drag to reorder or move items — items can be dropped at any level.</p>

                        <div id="itemsTree" class="tree-wrap"></div>

                        <div id="noItemsMsg" class="text-center text-muted py-4 border rounded bg-white">
                            No items yet. Click "Add Item" to begin.
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </section>

    <!-- ------------------- Popup Start ------------------- -->

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-4 px-4">
                    <div class="delete-icon-wrap mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.8rem;"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">Delete Item</h6>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        Are you sure you want to delete <strong id="deleteMenuName"></strong>?<br>
                        <span class="small">Any sub-items under it will be permanently removed.</span>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">Yes, Delete</button>
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
                    <h5 class="fw-semibold mb-1" id="saveSuccessTitle">Updated Successfully</h5>
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


    <div id="manageMenuConfig"
        data-menu-id="{{ (int) ($id ?? 0) }}"
        data-menus-list-url="{{ route('admin.menus') }}"
        data-item-form-url-base="{{ route('admin.editmenu', ['id' => $id]) }}"
        style="display:none;"></div>

    <style>

        .tree-wrap {
            border: 1px solid #e2e2e2;
            border-radius: 6px;
            overflow: hidden;
        }

        .tree-item-header {
            padding: 10px 16px;
            border-bottom: 1px solid #e2e2e2;
        }

        .tree-item-children .tree-item-header {
            padding-left: 32px;
        }

        .tree-item-children .tree-item-children .tree-item-header {
            padding-left: 56px;
        }

        .tree-item-children .tree-item-children .tree-item-children .tree-item-header {
            padding-left: 80px;
        }

        .tree-item-label {
            font-weight: 600;
            color: #2b2b2b;
        }

        .tree-drag-handle {
            color: #999;
            cursor: grab;
            font-size: 1rem;
        }

        .tree-item.dragging {
            opacity: 0.4;
        }

        .tree-item > .tree-item-header.drag-over,
        .tree-item.drag-over > .tree-item-header {
            outline: 2px dashed #002B5B;
            outline-offset: -2px;
        }

        .tree-toggle-btn {
            border: none;
            background: none;
            padding: 0;
            line-height: 0;
            color: #444;
            font-size: 1.05rem;
        }

        .tree-edit-link {
            color: #002B5B;
            font-weight: 500;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .tree-edit-link:hover {
            text-decoration: underline;
            color: #ffb347;
        }

        .tree-delete-link {
            color: #dc3545;
            font-size: 0.95rem;
            text-decoration: none;
            line-height: 0;
        }

        .tree-delete-link:hover {
            color: #a71d2a;
        }

        .back-pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #e2e2e2;
            color: #999;
            background: #fff;
            border-radius: 999px;
            padding: 7px 16px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .back-pill-btn:hover {
            border-color: #002B5B;
            color: #002B5B;
        }
    </style>
@endsection

@section('scripts')
    <script type="application/json" id="pageKeysData">{!! json_encode($pageKeys ?? []) !!}</script>
    <script src="{{ asset('assets/js/admin/managemenu.js') }}"></script>
@endsection