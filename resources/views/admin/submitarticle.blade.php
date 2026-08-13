@extends('layouts.admin')

@section('content')
    <!--  Articles Management-->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Articles Management
                </div>

                <div class="table-controls">
                    <button class="add-btn"><a href="{{ route('admin.submit-articles.create') }}">Create Article</a></button>
                    &nbsp; &nbsp;
                    <input type="text" class="form-control form-control-sm sa-search" id="saSearch"
                        placeholder="Search by name, email, or title…" style="max-width: 320px;">
                </div>

                <div class="table-container" style="margin: 0;">

                    <div id="saLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                        <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                    </div>

                    <div id="saEmpty" class="text-center py-5 d-none">
                        <p class="text-muted mb-0" style="font-size:14px;">No submissions found.</p>
                    </div>

                    <div id="saTableWrap" class="d-none">
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>Author</th>
                                    <th>Email</th>
                                    <th>Journal</th>
                                    <!-- <th>Manuscript Title</th> -->
                                    <th>Stage</th>
                                    <th>Submitted</th>
                                    <th id="saReviewerNameTh" class="d-none">Reviewer</th>
                                    <th id="saForwardedTh" class="d-none">Forward to Reviewer</th>
                                    <th id="saReviewerApprovedTh" class="d-none">Reviewer Verify</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="saTableBody">
                                <tr>
                                </tr>
                            </tbody>
                        </table>

                        <div class="sa-pagination">
                            <small id="saPageInfo"></small>
                            <div class="btn-group">
                                <button class="btn btn-light btn-sm" id="saPrevBtn">Prev</button>
                                <button class="btn btn-light btn-sm" id="saNextBtn">Next</button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- Send Reviewer Modal -->
    <div class="modal fade" id="saForwardModal" tabindex="-1" aria-labelledby="saForwardModallabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title">Forward to Reviewer</div>
                    </div>

                    <!-- Keep this -->
                    <div class="middle-3 middle"></div>

                    <!-- Forward to -->
                    <div class="reason">
                        <label>Forward to</label>
                        <select class="form-select" id="saForwardReviewer">
                            <option value="">Loading…</option>
                        </select>
                    </div>

                    <!-- Remarks (optional) -->
                    <div class="reason">
                        <label>Remarks (optional)</label>
                        <textarea type="text" id="saForwardRemarks" rows="3" placeholder="Any notes for the reviewer…"></textarea>
                    </div>

                    <!-- Btn -->
                    <div class="bottom-btn">
                        <button type="button" class="red" data-bs-dismiss="modal" aria-label="Close"> Cancel
                        </button>
                        <button type="button" class="blue" id="saForwardConfirmBtn">Forward for Review</button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Reviewer Modal -->
    <div class="modal fade" id="saReviewDecisionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-semibold mb-0">Submit Review Decision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Decision</label>
                        <div class="sa-decision-toggle">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="saReviewDecision"
                                    id="saReviewDecisionApproved" value="approved" checked>
                                <label class="form-check-label" style="font-size:13px;"
                                    for="saReviewDecisionApproved">Approved</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="saReviewDecision"
                                    id="saReviewDecisionCorrection" value="correction_needed">
                                <label class="form-check-label" style="font-size:13px;"
                                    for="saReviewDecisionCorrection">Correction Needed</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="saReviewDecision"
                                    id="saReviewDecisionRejected" value="rejected">
                                <label class="form-check-label" style="font-size:13px;"
                                    for="saReviewDecisionRejected">Reject</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Remarks</label>
                        <textarea class="form-control form-control-sm" id="saReviewDecisionRemarks" rows="4"
                            placeholder="Notes on your review…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-4"
                        id="saReviewDecisionConfirmBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Editor Modal -->
    <div class="modal fade" id="saFinalDecisionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-semibold mb-0">Final Decision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="saFinalReviewerRemarksWrap" class="sa-remarks-box reviewer">
                        <label>Reviewer's Remarks</label>
                        <div id="saFinalReviewerRemarks">—</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Decision</label>
                        <div class="sa-decision-toggle">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="saFinalDecision"
                                    id="saFinalDecisionApprove" value="approve" checked>
                                <label class="form-check-label" style="font-size:13px;"
                                    for="saFinalDecisionApprove">Approve
                                    (send to payment)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="saFinalDecision"
                                    id="saFinalDecisionReject" value="reject">
                                <label class="form-check-label" style="font-size:13px;"
                                    for="saFinalDecisionReject">Reject</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Remarks for author</label>
                        <textarea class="form-control form-control-sm" id="saFinalDecisionRemarks" rows="4"
                            placeholder="Explain the decision to the author…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-4" id="saFinalDecisionConfirmBtn">Submit
                        Decision</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Author — Revision Modal  -->
    <div class="modal fade" id="saForwardRevisionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-semibold mb-0">Send Back to Author — Revision Needed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label"
                            style="font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.3px;">Current
                            Stage</label>
                        <div><span class="sa-stage-chip reviewer_correction">Correction Needed</span></div>
                    </div>
                    <div class="sa-remarks-box reviewer">
                        <label>Reviewer's Remarks</label>
                        <div id="saRevisionReviewerRemarks">—</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Your note to the author</label>
                        <textarea class="form-control form-control-sm" id="saRevisionEditorRemarks" rows="3"
                            placeholder="Additional context for the author…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-4" id="saForwardRevisionConfirmBtn">Send to
                        Author</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="saRejectModal" tabindex="-1" aria-labelledby="saRejectModalLabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="top">
                        <div class="pop-title-remove">Reject Submission</div>
                    </div>

                    <div class="middle-content">
                        <span>
                            Reason for rejection
                        </span>
                    </div>

                    <div class="reason">
                        <textarea type="text" id="saRejectRemarks" rows="4" placeholder="Why is this being rejected…"></textarea>
                    </div>

                    <div class="bottom-btn">
                        <button type="button" class="red" id="saRejectConfirmBtn"> Reject </button>
                        <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Keep it
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade popup-two" id="saConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content border-0 shadow-lg">
                <div id="saConfirmIcon"></div>
                <div class="popup-title-two" id="saConfirmTitle"></div>
                <div class="popup-text-two" id="saConfirmDesc"></div>
                <div class="popup-flex-two">
                    <button type="button" class="btn btn-light" id="saConfirmCancelBtn">Cancel</button>
                    <button type="button" class="btn text-white" id="saConfirmOkBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="saToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
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
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/submitarticle.js') }}"></script>
@endsection
