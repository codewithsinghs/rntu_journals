@extends('layouts.admin')

@section('content')
    {{-- View By ID Hidden --}}
    <div class="d-none" id="saShowPage" data-id="{{ $id }}">
        <div id="saShowSubtitle"></div>
    </div>

    {{-- Html Start --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                {{-- Heading --}}
                <div class="heading">
                    Article Details
                </div>

                <div id="saShowBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Reject Modal -->
    <div class="modal fade" id="saRejectModal" tabindex="-1" aria-labelledby="saRejectModalLabel" style="display: none;"
        aria-hidden="true">
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

    <!-- Forward to Reviewer Modal -->
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

    <!-- Confirm Modal (used for Approve) -->
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
                    <i id="saToastIcon" style="font-size:20px;"></i>
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
    <script src="{{ asset('assets/js/admin/showarticles.js') }}"></script>
@endsection