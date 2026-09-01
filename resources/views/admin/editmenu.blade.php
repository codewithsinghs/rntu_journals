@extends('layouts.admin')

@section('content')
    @php
        $pageKeys = config('menu.pages', []);
        $isEditMode = request()->query('path') !== null;
    @endphp

    <!-- Add / Edit Menu Item -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="menu-item-card">

                <div class="mic-header">
                    <div>
                        <div class="mic-eyebrow">Navigation</div>
                        <div class="heading mic-title" id="itemFormHeading">
                            {{ $isEditMode ? 'Edit Menu Item' : 'Add Menu Item' }}
                        </div>
                    </div>
                    <div class="mic-header-actions">
                        <button type="button" class="delete-pill-btn {{ $isEditMode ? '' : 'd-none' }}" id="removeItemBtn">
                            <i class="bi bi-trash3"></i> Delete
                        </button>
                        <a href="{{ route('admin.menus.manage', ['id' => $id]) }}" class="back-pill-btn">
                            <i class="bi bi-arrow-left"></i> Back to Menu
                        </a>
                    </div>
                </div>

                <div id="pageLoader" class="mic-loader">
                    <div class="mic-spinner"></div>
                    <span>Loading…</span>
                </div>

                <form id="itemForm" class="mic-form d-none">

                    <!-- Section: Basics -->
                    <div class="mic-section">
                        <div class="mic-section-label"><i class="bi bi-pencil-square"></i> Basics</div>
                        <div class="mic-grid">
                            <span class="input-set mic-field">
                                <label>Menu Label <span class="text-danger">*</span></label>
                                <div class="mic-input-wrap">
                                    <i class="bi bi-type"></i>
                                    <input type="text" id="label" maxlength="191" required placeholder="e.g. Journals">
                                </div>
                                <div class="form-note">Up to 191 characters</div>
                                <div class="invalid-feedback" id="labelError"></div>
                            </span>

                            <span class="input-set mic-field">
                                <label>Type</label>
                                <div class="mic-input-wrap">
                                    <i class="bi bi-signpost-split"></i>
                                    <select id="type">
                                        <option value="url">Url Page</option>
                                        <option value="page">Page</option>
                                    </select>
                                </div>
                                <div class="form-note">Determines whether the URL or the page below is used</div>
                            </span>
                        </div>
                    </div>

                    <!-- Section: Link target -->
                    <div class="mic-section">
                        <div class="mic-section-label"><i class="bi bi-link-45deg"></i> Link Target</div>
                        <div class="mic-grid">
                            <span class="input-set mic-field">
                                <label>Custom Link URL</label>
                                <div class="mic-input-wrap">
                                    <i class="bi bi-globe2"></i>
                                    <input type="text" id="url" placeholder="#">
                                </div>
                            </span>

                            <span class="input-set mic-field">
                                <label>Select Page</label>
                                <div class="mic-input-wrap">
                                    <i class="bi bi-file-earmark-richtext"></i>
                                    <select id="pageSelect">
                                        <option value="">Select One</option>
                                    </select>
                                </div>
                            </span>
                        </div>
                    </div>

                    <!-- Section: Placement -->
                    <div class="mic-section">
                        <div class="mic-section-label"><i class="bi bi-diagram-3"></i> Placement</div>
                        <div class="mic-grid mic-grid-1">
                            <span class="input-set mic-field">
                                <label>Parent Menu</label>
                                <div class="mic-input-wrap">
                                    <i class="bi bi-bounding-box"></i>
                                    <select id="parentSelect">
                                        <option value="">— Top Level —</option>
                                    </select>
                                </div>
                            </span>
                        </div>
                    </div>

                    <!-- Section: Advanced -->
                    <div class="mic-section">
                        <div class="mic-section-label"><i class="bi bi-sliders"></i> Advanced</div>
                        <div class="mic-grid">
                            <span class="input-set mic-field">
                                <label>CSS Class</label>
                                <div class="mic-input-wrap">
                                    <i class="bi bi-code-slash"></i>
                                    <input type="text" id="css_class" placeholder="nav-highlight">
                                </div>
                            </span>

                            <span class="input-set mic-field">
                                <label>CSS ID</label>   
                                <div class="mic-input-wrap">
                                    <i class="bi bi-hash"></i>
                                    <input type="text" id="css_id" placeholder="nav-item-01">
                                </div>
                            </span>

                            <span class="input-set mic-field mic-field-full">
                                <label>Menu Slug</label>
                                <div class="mic-input-wrap mic-slug-wrap">
                                    <span class="mic-slug-prefix">/</span>
                                    <input type="text" id="slug" class="slug-readonly" readonly>
                                </div>
                                <div class="form-note">Generated automatically from the label</div>
                            </span>
                        </div>
                    </div>

                    <!-- Btn -->
                    <div class="mic-footer">
                        <button type="button" class="mic-btn mic-btn-ghost" onclick="window.location.href = MANAGE_MENU_URL">Cancel</button>
                        <button type="submit" class="mic-btn mic-btn-primary" id="saveBtn">
                            <i class="bi bi-check2"></i> Update
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </section>

    <!-- Confirm Remove Item Modal -->
    <div class="modal fade" id="confirmRemoveItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center py-4 px-4">
                    <div class="delete-icon-wrap mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.8rem;"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">Delete Menu Item</h6>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        Are you sure you want to delete <strong id="removeItemLabel"></strong>?<br>
                        <span class="small">Any sub-items will move up and take its place.</span>
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmRemoveItemBtn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div id="itemFormConfig"
        data-menu-id="{{ (int) ($id ?? 0) }}"
        data-manage-menu-url="{{ route('admin.menus.manage', ['id' => $id]) }}"
        style="display:none;"></div>

<style>
    :root {
        --mic-ink: #2D3748;
        --mic-muted: #A0AEC0;
        --mic-accent: #002B5B;
        --mic-accent-soft: #F5F7FA;
        --mic-teal: #002B5B;
        --mic-danger: #D32F2F;
        --mic-border: #e6e8f0;
        --mic-surface: #ffffff;
    }

    .content_top_wrapper {
        width: 100%;
    }

    .menu-item-card {
        width: 100%;
        max-width: none;
        background: var(--mic-surface);
        border: 1px solid var(--mic-border);
        border-radius: 18px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04),
                    0 12px 32px -16px rgba(0, 0, 0, 0.12);
        overflow: hidden;
        box-sizing: border-box;
    }

    .mic-form {
        box-sizing: border-box;
    }

    .mic-grid {
        width: 100%;
    }

    .mic-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 24px 28px;
        border-bottom: 1px solid var(--mic-border);
        background: #ffffff;
    }

    .mic-eyebrow {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--mic-accent);
        margin-bottom: 4px;
    }

    .mic-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--mic-ink);
    }

    .mic-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .back-pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--mic-border);
        color: var(--mic-muted);
        background: #fff;
        border-radius: 999px;
        padding: 7px 16px;
        font-size: 0.85rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .back-pill-btn:hover {
        border-color: var(--mic-accent);
        color: var(--mic-accent);
    }

    .delete-pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--mic-danger);
        color: var(--mic-danger);
        background: #fff;
        border-radius: 999px;
        padding: 7px 18px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.15s ease;
    }

    .delete-pill-btn:hover {
        background: var(--mic-danger);
        color: #fff;
    }

    .mic-loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 60px 0;
        color: var(--mic-muted);
        font-size: 0.9rem;
    }

    .mic-spinner {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 3px solid var(--mic-accent-soft);
        border-top-color: var(--mic-accent);
        animation: mic-spin 0.7s linear infinite;
    }

    @keyframes mic-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .mic-form {
        padding: 8px 28px 28px;
    }

    .mic-section {
        padding: 20px 0;
        border-bottom: 1px dashed var(--mic-border);
    }

    .mic-section:last-of-type {
        border-bottom: none;
    }

    .mic-section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--mic-accent);
        margin-bottom: 16px;
    }

    .mic-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 420px));
        gap: 18px 32px;
    }

    .mic-grid-1 {
        grid-template-columns: minmax(280px, 420px);
    }

    .mic-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .mic-field-full {
        grid-column: 1 / -1;
        max-width: 320px;
    }

    .mic-field label {
        font-size: 0.83rem;
        font-weight: 600;
        color: var(--mic-ink);
    }

    .mic-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .mic-input-wrap > i {
        position: absolute;
        left: 13px;
        color: var(--mic-muted);
        font-size: 0.95rem;
        pointer-events: none;
    }

    .mic-input-wrap input,
    .mic-input-wrap select {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border: 1px solid var(--mic-border);
        border-radius: 6.77px;
        font-size: 0.92rem;
        color: var(--mic-ink);
        background: #F5F7FA;
        transition: border-color 0.15s ease,
                    box-shadow 0.15s ease,
                    background 0.15s ease;
        appearance: none;
    }

    .mic-input-wrap select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23A0AEC0' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 34px;
    }

    .mic-input-wrap input:hover,
    .mic-input-wrap select:hover {
        border-color: #002B5B;
    }

    .mic-input-wrap input:focus,
    .mic-input-wrap select:focus {
        outline: none;
        border-color: var(--mic-accent);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(0, 43, 91, 0.10);
    }

    .mic-slug-wrap {
        border: 1px solid var(--mic-border);
        border-radius: 6.77px;
        background: #F5F7FA;
        overflow: hidden;
    }

    .mic-slug-prefix {
        padding: 10px 0 10px 14px;
        font-family: 'JetBrains Mono',
                     ui-monospace,
                     SFMono-Regular,
                     Menlo,
                     monospace;
        color: var(--mic-muted);
    }

    .mic-slug-wrap input.slug-readonly {
        border: none;
        background: transparent !important;
        font-family: 'JetBrains Mono',
                     ui-monospace,
                     SFMono-Regular,
                     Menlo,
                     monospace;
        color: var(--mic-ink) !important;
        padding-left: 4px;
        cursor: default;
    }

    .mic-slug-wrap input.slug-readonly:focus {
        box-shadow: none;
    }

    .form-note {
        font-size: 0.76rem;
        color: var(--mic-muted);
    }

    .mic-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 22px;
    }

    .mic-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 10px 26px;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }

    .mic-btn-ghost {
        background: #fff;
        border-color: var(--mic-border);
        color: var(--mic-muted);
    }

    .mic-btn-ghost:hover {
        border-color: var(--mic-accent);
        color: var(--mic-accent);
    }

    .mic-btn-primary {
        background: var(--mic-accent);
        color: #fff;
        box-shadow: 0 8px 20px -8px rgba(0, 43, 91, 0.55);
    }

    .mic-btn-primary:hover {
        background: #153162;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px -8px rgba(0, 43, 91, 0.65);
    }

    .mic-btn-primary:active {
        transform: translateY(0);
    }

    @media (max-width: 640px) {
        .mic-grid {
            grid-template-columns: 1fr;
        }

        .mic-header {
            padding: 20px;
        }

        .mic-form {
            padding: 4px 20px 20px;
        }
    }
</style>
@endsection

@section('scripts')
    <script type="application/json" id="pageKeysData">{!! json_encode($pageKeys ?? []) !!}</script>
    <script src="{{ asset('assets/js/admin/editmenu.js') }}"></script>
@endsection