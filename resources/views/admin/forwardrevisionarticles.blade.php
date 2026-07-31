@extends('layouts.admin')

@section('content')
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="saToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="saToastIcon"></span>
                    <div>
                        <div id="saToastTitle" class="fw-semibold" style="font-size:14px;"></div>
                        <div id="saToastMsg" class="opacity-75" style="font-size:13px;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <div class="p_inner" id="saRevisionPage" data-id="{{ $id }}">
        <a href="/admin/submit-articles">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to list
        </a>
        <h3>Send Back to Author — Revision Needed</h3>
        <div>#{{ $id }}</div>

        <div>
            <div class="mb-3">
                <label class="form-label"
                    style="font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.3px;">Current
                    Stage</label>
                <div><span class=" reviewer_correction">Correction Needed</span></div>
            </div>
            <div class=" reviewer">
                <label>Reviewer's Remarks</label>
                <div id="saRevisionReviewerRemarks">Loading…</div>
            </div>
            <div class="mb-0">
                <label class="form-label" style="font-size:13px;font-weight:600;">Your note to the author</label>
                <textarea class="form-control form-control-sm" id="saRevisionEditorRemarks" rows="5"
                    placeholder="Additional context for the author…"></textarea>
            </div>

            <div>
                <a href="/admin/submit-articles" class="btn btn-light btn-sm px-4">Cancel</a>
                <button type="button" class="btn btn-primary btn-sm px-4" id="saForwardRevisionConfirmBtn">Send to
                    Author</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/forwardrevisionarticles.js') }}"></script>
@endsection
