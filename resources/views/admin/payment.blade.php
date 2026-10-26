<div class="modal fade" id="saPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content">

            <div class="sa-pay-header">
                <div class="sa-pay-icon"><i class="fa fa-credit-card"></i></div>
                <div>
                    <h5>Article Processing Payment</h5>
                    <small>Review the details before you pay</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="sa-pay-body">
                <div class="sa-pay-details">
                    <div class="sa-pay-row">
                        <span class="label">Article ID</span>
                        <span class="value" id="saPayArticleUid">—</span>
                    </div>
                    <div class="sa-pay-row">
                        <span class="label">Author</span>
                        <span class="value" id="saPayUserName">—</span>
                    </div>
                    <div class="sa-pay-row">
                        <span class="label">Manuscript</span>
                        <span class="value" id="saPayTitle">—</span>
                    </div>
                    <div class="sa-pay-row">
                        <span class="label">Date of submission</span>
                        <span class="value" id="saPayDate">—</span>
                    </div>
                </div>

                <div class="sa-pay-amount">
                    <div class="caption">Amount payable</div>
                    <div id="saPayAmount">Loading…</div>
                </div>

                <div class="sa-pay-confirm">
                    <input type="checkbox" id="saPayConfirmCheck">
                    <label for="saPayConfirmCheck">
                        I confirm that the article details and the amount above are correct.
                    </label>
                </div>

                <p class="sa-pay-secure"><i class="fa fa-lock"></i> You will be redirected to Paytm's secure payment page.</p>
            </div>

            <div class="sa-pay-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success text-white" id="saPayProceedBtn" disabled>
                    Proceed to Pay
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="saReceiptModal" tabindex="-1" aria-labelledby="saReceiptTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:520px;">
        <div class="modal-content">

            <div class="sa-receipt-modal-header">
                <div class="sa-receipt-modal-icon"><i class="fa fa-file"></i></div>
                <div>
                    <h5 id="saReceiptTitle">Payment Receipt</h5>
                    <small>Details of your article processing payment</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="saReceiptBody"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="saReceiptRefreshBtn" style="display:none;">Refresh</button>
                <button type="button" class="btn btn-primary" id="saReceiptDownloadBtn" disabled>Download PDF</button>
            </div>

        </div>
    </div>
</div>

{{-- PDF library used by the "Download PDF" button (loaded once per page) --}}
<script>
    if (!(window.jspdf && window.jspdf.jsPDF)) {
        document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"><\/script>');
    }
</script>