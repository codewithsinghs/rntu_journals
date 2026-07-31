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

    <div class="p_inner" id="saFinalPage" data-id="{{ $id }}">
        <a href="/admin/submit-articles">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to list
        </a>
        <h3>Final Decision</h3>
        <div>#{{ $id }}</div>

        <div>
            <div id="saFinalReviewerRemarksWrap" class="sa-remarks-box reviewer">
                <label>Reviewer's Remarks</label>
                <div id="saFinalReviewerRemarks">Loading…</div>
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-size:13px;font-weight:600;">Decision</label>
                <div class="sa-decision-toggle">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="saFinalDecision" id="saFinalDecisionApprove"
                            value="approve" checked>
                        <label class="form-check-label" style="font-size:13px;" for="saFinalDecisionApprove">Approve (send
                            to payment)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="saFinalDecision" id="saFinalDecisionReject"
                            value="reject">
                        <label class="form-check-label" style="font-size:13px;" for="saFinalDecisionReject">Reject</label>
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" style="font-size:13px;font-weight:600;">Remarks for author</label>
                <textarea class="form-control form-control-sm" id="saFinalDecisionRemarks" rows="5"
                    placeholder="Explain the decision to the author…"></textarea>
            </div>

            <div>
                <a href="/admin/submit-articles" class="btn btn-light btn-sm px-4">Cancel</a>
                <button type="button" class="btn btn-primary btn-sm px-4" id="saFinalDecisionConfirmBtn">Submit
                    Decision</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/finaldecisionarticles.js') }}"></script>
@endsection
