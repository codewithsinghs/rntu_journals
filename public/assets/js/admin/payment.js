// public/assets/js/admin/payment.js
// "Pay Now": details + amount popup (with confirmation checkbox) -> create order on server
// -> redirect to payment page.
// Usage from any page:  ArticlePayment.open(uuid);  ArticlePayment.showReceipt(orderId);

(function () {
    "use strict";

    const API_BASE = "/api/admin/submit-articles";

    const authHeaders = () => ({
        Accept: "application/json",
        Authorization: `Bearer ${localStorage.getItem("jwt_token") || ""}`,
    });

    const toast = (type, title, msg) => {
        if (typeof window.saShowToast === "function") {
            window.saShowToast(type, title, msg);
        } else {
            alert(msg ? `${title}: ${msg}` : title);
        }
    };

    const el = (id) => document.getElementById(id);

    const esc = (v) =>
        String(v ?? "-").replace(/[&<>"']/g, (c) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
        }[c]));

    /* Set text of an element only if it exists (the modal markup may differ per page) */
    const setText = (id, value) => {
        const node = el(id);
        if (node) node.textContent = value === null || value === undefined || value === "" ? "—" : value;
    };

    /* First non-empty value from the list, else null */
    const pick = (...vals) => vals.find((v) => v !== null && v !== undefined && v !== "") ?? null;

    /* Journal / author can arrive flat or nested depending on the API, so look in the common places */
    const nameOf = (v) => (v && typeof v === "object" ? pick(v.name, v.title, v.full_name) : v);
    const journalOf = (r) =>
        pick(
            r.journal_name, r.journal_title, nameOf(r.journal),
            nameOf(r.article?.journal), r.article?.journal_name, r.journal_full_name
        );
    const authorOf = (r) =>
        pick(
            r.author_name, r.full_name, r.user_name, r.submitted_by, r.corresponding_author,
            nameOf(r.author), nameOf(r.user), nameOf(r.article?.user), r.article?.author_name,
            window.APP_CONFIG?.CURRENT_USER_NAME
        );

    const emailOf = (r) =>
        pick(r.author_email, r.email, r.user_email, r.author?.email, r.user?.email, r.article?.user?.email);

    let modal = null;
    let currentId = null;
    let receiptOrderId = null;
    let receiptData = null;   // last loaded receipt (used to build the PDF)
    let amountLoaded = false; // amount fetched successfully
    let starting = false;     // order request in flight
    let loadToken = 0;        // ignores stale responses if open() is called again quickly
    let receiptToken = 0;     // same, for showReceipt()

    function getModal() {
        if (!modal) modal = bootstrap.Modal.getOrCreateInstance(el("saPayModal"));
        return modal;
    }

    function formatAmount(amount, currency) {
        try {
            return new Intl.NumberFormat("en-IN", { style: "currency", currency: currency || "INR" }).format(amount);
        } catch {
            return `${currency || "INR"} ${amount}`;
        }
    }

    function formatDate(value) {
        if (!value) return "—";
        const d = new Date(value);
        if (isNaN(d.getTime())) return value; // already a display string
        return d.toLocaleDateString("en-IN", { day: "2-digit", month: "short", year: "numeric" });
    }

    /* Proceed button is enabled only when: amount loaded + checkbox ticked + not already starting */
    function updateProceedState() {
        const btn = el("saPayProceedBtn");
        const check = el("saPayConfirmCheck");
        if (!btn) return;
        btn.disabled = !(amountLoaded && check && check.checked && !starting);
        btn.textContent = starting ? "Redirecting…" : "Proceed to Pay";
    }

    /* Step 1: popup with article id, author, manuscript, submission date, amount and confirmation checkbox */
    async function open(id) {
        currentId = id;
        amountLoaded = false;
        starting = false;
        const token = ++loadToken;

        // reset everything so old values never flash for a new article
        setText("saPayArticleUid", "—");
        setText("saPayUserName", "—");
        setText("saPayTitle", "—");
        setText("saPayDate", "—");
        setText("saPayAmount", "Loading…");

        const check = el("saPayConfirmCheck");
        if (check) check.checked = false; // must re-confirm every time
        updateProceedState();
        getModal().show();

        try {
            const res = await fetch(`${API_BASE}/${encodeURIComponent(id)}`, { headers: authHeaders() });
            const json = await res.json();
            if (token !== loadToken) return; // a newer open() call replaced this one
            if (!res.ok || !json.status) throw new Error(json.message || "Failed to load details.");

            const d = json.data;

            const amount = d.payment_amount;
            if (amount === null || amount === undefined) throw new Error("Amount not available.");

            setText("saPayArticleUid", d.article_uid);
            setText("saPayUserName", d.full_name || window.APP_CONFIG?.CURRENT_USER_NAME);
            // Adjust these field names if your API returns different keys
            setText("saPayTitle", d.title || d.article_title || d.manuscript_title);
            el("saPayDate") && (el("saPayDate").textContent = formatDate(d.submitted_at || d.created_at));
            setText("saPayAmount", formatAmount(amount, d.payment_currency || "INR"));

            amountLoaded = true;
            updateProceedState(); // still disabled until the checkbox is ticked
        } catch (e) {
            if (token !== loadToken) return;
            setText("saPayAmount", "Unable to load amount");
            toast("error", "Payment", e.message);
        }
    }

    /* Step 2: server creates the order, browser goes to the payment page */
    async function start() {
        if (!currentId || starting) return;

        // Guard in case the button was enabled some other way
        if (!el("saPayConfirmCheck")?.checked) {
            toast("error", "Payment", "Please tick the confirmation box first.");
            return;
        }

        starting = true;
        updateProceedState();

        try {
            const res = await fetch(`${API_BASE}/${encodeURIComponent(currentId)}/payment/initiate`, {
                method: "POST",
                headers: { ...authHeaders(), "Content-Type": "application/json" },
                body: JSON.stringify({}),
            });
            const json = await res.json();
            if (!res.ok || !json.status) throw new Error(json.message || "Could not start payment.");
            if (!json.data?.redirect_url) throw new Error("Payment page link missing. Please try again.");

            window.location.href = json.data.redirect_url;
        } catch (e) {
            starting = false;
            updateProceedState();
            toast("error", "Payment", e.message);
        }
    }

    /* Receipt modal (also used for redirects that land with ?status=&order_id=) */
    const RECEIPT_LABELS = { paid: "Successful", pending: "Pending", failed: "Failed", created: "Not completed" };

    async function showReceipt(orderId) {
        if (!orderId) return;
        receiptOrderId = orderId;
        const token = ++receiptToken;

        const body = el("saReceiptBody");
        const refreshBtn = el("saReceiptRefreshBtn");
        const downloadBtn = el("saReceiptDownloadBtn");
        body.innerHTML = '<div class="sa-receipt-state">Loading…</div>';
        if (refreshBtn) refreshBtn.style.display = "none";
        if (downloadBtn) downloadBtn.disabled = true;
        bootstrap.Modal.getOrCreateInstance(el("saReceiptModal")).show();

        try {
            const res = await fetch(`/api/admin/payments/${encodeURIComponent(orderId)}/receipt`, {
                headers: authHeaders(),
            });
            const json = await res.json();
            if (token !== receiptToken) return; // a newer receipt request replaced this one
            if (!res.ok || !json.status) throw new Error(json.message || "Could not load receipt.");
            const r = json.data;
            receiptData = r;

            const status = RECEIPT_LABELS[r.status] ? r.status : "created";
            const label = RECEIPT_LABELS[r.status] || r.status || "Unknown";
            const row = (k, v) => `<tr><td class="k">${k}</td><td class="v">${esc(v)}</td></tr>`;

            body.innerHTML = `
                <div class="sa-receipt-modal-content">
                    <table class="sa-receipt-summary">
                        <tr>
                            <td>
                                <div class="sa-receipt-caption">${status === "paid" ? "Amount paid" : "Amount"}</div>
                                <div class="sa-receipt-amount">${esc(formatAmount(r.amount, r.currency))}</div>
                            </td>
                            <td class="status-cell">
                                <span class="sa-receipt-badge is-${status}">${esc(label)}</span>
                                <div class="sa-receipt-date">${esc(r.date)}</div>
                            </td>
                        </tr>
                    </table>
                    <div class="sa-receipt-no-line">Receipt no. <strong>${esc(r.receipt_no)}</strong></div>
                    ${r.status === "pending" ? '<div class="sa-receipt-note">Your payment is still processing. Click Refresh to check again.</div>' : ""}
                    ${r.status === "failed" && r.failure_reason ? `<div class="sa-receipt-note is-danger"><strong>Reason:</strong> ${esc(r.failure_reason)}</div>` : ""}

                    <div class="sa-receipt-section">Article details</div>
                    <table class="sa-receipt-table">
                        ${row("Journal", journalOf(r))}
                        ${row("Author", authorOf(r))}
                        ${row("Email", emailOf(r))}
                        ${row("Article ID", r.article_uid)}
                        ${row("Manuscript", r.article_title)}
                    </table>

                    <div class="sa-receipt-section">Payment details</div>
                    <table class="sa-receipt-table">
                        ${row("Order ID", r.order_id)}
                        ${row("Transaction ID", r.txn_id)}
                        ${row("Bank transaction ID", r.bank_txn_id)}
                        ${row("Payment mode", r.payment_mode)}
                    </table>
                </div>`;

            if (refreshBtn) refreshBtn.style.display = r.status === "pending" ? "" : "none";
            // PDF receipt only makes sense once the payment has a final result
            if (downloadBtn) downloadBtn.disabled = r.status === "pending";
        } catch (e) {
            if (token !== receiptToken) return;
            body.innerHTML = `<div class="sa-receipt-state is-error">${esc(e.message)}</div>`;
        }
    }

    /* PDF amounts: the default PDF fonts have no rupee glyph, so write "Rs." */
    function pdfAmount(amount, currency) {
        const n = Number(amount);
        const num = isNaN(n)
            ? String(amount ?? "-")
            : new Intl.NumberFormat("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        const cur = (currency || "INR").toUpperCase();
        return cur === "INR" ? `Rs. ${num}` : `${cur} ${num}`;
    }

    /* Draws the receipt as a real PDF document with jsPDF (selectable text, no screenshot).
       Layout: centered title, status banner, then bordered label/value tables per section. */
    function buildReceiptPdf(r, JsPDF) {
        const doc = new JsPDF({ unit: "mm", format: "a4", orientation: "portrait" });
        const L = 15;
        const R = 195;
        const W = R - L;
        const PAGE_BOTTOM = 274;

        const INK = [27, 38, 59];
        const GRAY = [108, 117, 125];
        const BORDER = [212, 216, 222];
        const LABEL_BG = [246, 247, 249];

        const status = RECEIPT_LABELS[r.status] ? r.status : "created";
        const label = RECEIPT_LABELS[r.status] || r.status || "Unknown";
        const theme = {
            paid: {
                bg: [232, 248, 240], border: [150, 225, 180], pill: [168, 236, 201], fg: [10, 95, 55],
                pillText: "PAID", title: "Article Processing Charge Received",
                msg: "The article processing charge for this manuscript has been received successfully.",
                amountLabel: "AMOUNT PAID",
            },
            pending: {
                bg: [255, 246, 226], border: [245, 215, 140], pill: [252, 226, 160], fg: [150, 90, 0],
                pillText: "PENDING", title: "Payment Is Being Processed",
                msg: "Your payment is still being processed. Please check the status again shortly.",
                amountLabel: "AMOUNT",
            },
            failed: {
                bg: [253, 236, 237], border: [240, 170, 175], pill: [248, 200, 204], fg: [160, 35, 48],
                pillText: "FAILED", title: "Payment Was Not Successful",
                msg: r.failure_reason ? String(r.failure_reason) : "The payment could not be completed. Please try again.",
                amountLabel: "AMOUNT",
            },
            created: {
                bg: [240, 242, 245], border: [208, 213, 220], pill: [220, 224, 230], fg: [85, 85, 85],
                pillText: "NOT PAID", title: "Payment Not Completed",
                msg: "The payment was started but has not been completed.",
                amountLabel: "AMOUNT",
            },
        }[status];

        const journal = journalOf(r) || "-";
        const author = authorOf(r) || "-";
        const submitted = pick(r.submitted_at, r.submission_date, r.created_at);
        const email = emailOf(r);
        const affiliation = pick(r.affiliation, r.institution, r.author_affiliation);
        const val = (v) => (v === null || v === undefined || v === "" ? "-" : String(v));
        const total = pdfAmount(r.amount, r.currency);

        const text = (t, x, y, size, style, color, opts, font) => {
            doc.setFont(font || "helvetica", style);
            doc.setFontSize(size);
            doc.setTextColor(...color);
            doc.text(t, x, y, opts);
        };

        /* ---------- Title ---------- */
        let y = 22;
        const titleLines = (doc.setFont("helvetica", "bold"), doc.setFontSize(18), doc.splitTextToSize(journal.toUpperCase(), W)).slice(0, 2);
        text(titleLines, 105, y, 18, "bold", INK, { align: "center", lineHeightFactor: 1.2 });
        y += (titleLines.length - 1) * 7.5 + 8;
        text("Article Processing Charge Receipt", 105, y, 10.5, "bold", GRAY, { align: "center" });
        y += 6;
        doc.setDrawColor(...BORDER);
        doc.setLineWidth(0.4);
        doc.line(L, y, R, y);
        y += 8;

        /* ---------- Status banner ---------- */
        doc.setFont("helvetica", "normal");
        doc.setFontSize(8.5);
        const msgLines = doc.splitTextToSize(theme.msg, 82);
        const bannerH = Math.max(24, 15 + msgLines.length * 4);
        doc.setFillColor(...theme.bg);
        doc.setDrawColor(...theme.border);
        doc.setLineWidth(0.4);
        doc.roundedRect(L, y, W, bannerH, 3, 3, "FD");

        // left pill
        doc.setFont("helvetica", "bold");
        doc.setFontSize(8.5);
        const pillW = Math.max(26, doc.getTextWidth(theme.pillText) + 10);
        const pillY = y + bannerH / 2 - 4;
        doc.setFillColor(...theme.pill);
        doc.setDrawColor(...theme.fg);
        doc.setLineWidth(0.3);
        doc.roundedRect(L + 4, pillY, pillW, 8, 1.8, 1.8, "FD");
        text(theme.pillText, L + 4 + pillW / 2, pillY + 5.3, 8.5, "bold", theme.fg, { align: "center" });

        // message
        const mx = L + 4 + pillW + 5;
        text(theme.title, mx, y + 8, 10, "bold", INK);
        doc.setFont("helvetica", "normal");
        doc.setFontSize(8.5);
        doc.setTextColor(...theme.fg);
        doc.text(msgLines, mx, y + 13);

        // right amount box
        const boxW = 44;
        const boxH = 16;
        const boxX = R - 4 - boxW;
        const boxY = y + bannerH / 2 - boxH / 2;
        doc.setFillColor(255, 255, 255);
        doc.setDrawColor(...theme.border);
        doc.roundedRect(boxX, boxY, boxW, boxH, 2.5, 2.5, "FD");
        text(theme.amountLabel, boxX + boxW / 2, boxY + 5.6, 6.5, "bold", INK, { align: "center" });
        text(total, boxX + boxW / 2, boxY + 12.2, 12, "bold", theme.fg, { align: "center" });
        y += bannerH + 10;

        /* ---------- Section tables ---------- */
        const COLS = [36, 54, 36, 54]; // label, value, label, value
        const PAD = 3.2;
        const LH = 4.4;

        const ensure = (h) => {
            if (y + h > PAGE_BOTTOM) {
                doc.addPage();
                y = 20;
            }
        };

        // cell = { k, v, mono, color, bold }; a row is [cellA, cellB] or [cellA] (value spans 3 columns)
        const measure = (cell, width) => {
            doc.setFont(cell.mono ? "courier" : "helvetica", cell.bold || cell.color ? "bold" : "normal");
            doc.setFontSize(cell.mono ? 8.5 : 9);
            return doc.splitTextToSize(val(cell.v), width - PAD * 2);
        };

        const section = (title, rows) => {
            // first row height, so the heading never sits alone at the bottom of a page
            const built = rows.map((row) => {
                const spans = row.length === 1;
                const valW = spans ? COLS[1] + COLS[2] + COLS[3] : COLS[1];
                const parts = row.map((c, i) => ({ c, lines: measure(c, i === 0 ? valW : COLS[3]) }));
                doc.setFont("helvetica", "bold");
                doc.setFontSize(9);
                const labelLines = row.map((c, i) => doc.splitTextToSize(c.k, (i === 0 ? COLS[0] : COLS[2]) - PAD * 2));
                const maxLines = Math.max(...parts.map((p) => p.lines.length), ...labelLines.map((l) => l.length));
                return { row, parts, labelLines, spans, h: Math.max(11, maxLines * LH + PAD * 2 - 0.4) };
            });

            ensure(14 + built[0].h);
            text(title, L, y + 4, 12.5, "bold", INK);
            y += 8;

            built.forEach((b) => {
                ensure(b.h);
                let x = L;
                b.row.forEach((cell, i) => {
                    const labelW = i === 0 ? COLS[0] : COLS[2];
                    const valW = b.spans ? COLS[1] + COLS[2] + COLS[3] : COLS[1];

                    // label cell
                    doc.setFillColor(...LABEL_BG);
                    doc.setDrawColor(...BORDER);
                    doc.setLineWidth(0.25);
                    doc.rect(x, y, labelW, b.h, "FD");
                    doc.setFont("helvetica", "bold");
                    doc.setFontSize(9);
                    doc.setTextColor(...INK);
                    const ly = y + b.h / 2 - ((b.labelLines[i].length - 1) * LH) / 2 + 1.2;
                    doc.text(b.labelLines[i], x + PAD, ly);
                    x += labelW;

                    // value cell
                    doc.setFillColor(255, 255, 255);
                    doc.rect(x, y, valW, b.h, "FD");
                    const lines = b.parts[i].lines;
                    doc.setFont(cell.mono ? "courier" : "helvetica", cell.bold || cell.color ? "bold" : "normal");
                    doc.setFontSize(cell.mono ? 8.5 : 9);
                    doc.setTextColor(...(cell.color || INK));
                    const vy = y + b.h / 2 - ((lines.length - 1) * LH) / 2 + 1.2;
                    doc.text(lines, x + PAD, vy);
                    x += valW;
                });
                y += b.h;
            });
            y += 9;
        };

        section("Article Summary", [
            [{ k: "Journal", v: journal }, { k: "Article ID", v: r.article_uid, mono: true, bold: true, color: [16, 42, 84] }],
            [{ k: "Manuscript Title", v: r.article_title, bold: true }],
            [{ k: "Date of Submission", v: submitted ? formatDate(submitted) : "-" }, { k: "Processing Charge", v: total, bold: true }],
        ]);

        const authorRows = [[{ k: "Author Name", v: author, bold: true }, { k: "Email", v: email }]];
        if (affiliation) authorRows.push([{ k: "Affiliation", v: affiliation }]);
        section("Author Information", authorRows);

        section("Transaction Information", [
            [{ k: "Receipt No", v: r.receipt_no, mono: true }, { k: "Transaction Status", v: label.toUpperCase(), bold: true, color: theme.fg }],
            [{ k: "Order / Reference No", v: r.order_id, mono: true }, { k: "Transaction ID", v: r.txn_id, mono: true }],
            [{ k: "Bank Transaction ID", v: r.bank_txn_id, mono: true }, { k: "Payment Mode", v: r.payment_mode, bold: true }],
            [{ k: "Amount", v: total, bold: true }, { k: "Date & Time", v: r.date }],
        ]);

        /* ---------- Footer on every page ---------- */
        const pages = doc.getNumberOfPages();
        for (let i = 1; i <= pages; i++) {
            doc.setPage(i);
            doc.setDrawColor(...BORDER);
            doc.setLineWidth(0.3);
            doc.line(L, 282, R, 282);
            const note = `This is a computer generated official payment receipt from ${journal === "-" ? "the journal" : journal}. No physical signature is required.`;
            const noteLines = doc.splitTextToSize(note, W);
            text(noteLines, 105, 288, 8, "normal", GRAY, { align: "center" });
            if (pages > 1) text(`Page ${i} of ${pages}`, R, 293, 7.5, "normal", GRAY, { align: "right" });
        }

        return doc;
    }

    function downloadReceipt() {
        if (!receiptOrderId || !receiptData) {
            toast("error", "Receipt", "No receipt is open. Please reopen the receipt and try again.");
            return;
        }
        const JsPDF = window.jspdf && window.jspdf.jsPDF;
        if (!JsPDF) {
            toast("error", "Receipt", "PDF library failed to load. Please refresh the page.");
            return;
        }

        try {
            const doc = buildReceiptPdf(receiptData, JsPDF);
            doc.save(`receipt-${receiptData.receipt_no || receiptOrderId}.pdf`);
        } catch (e) {
            console.error(e);
            toast("error", "Receipt", "Could not create the PDF.");
        }
    }

    /* Shown when a redirect sends the user back (?status=...&order_id=...) */
    function showReturnResult() {
        const p = new URLSearchParams(window.location.search);
        const status = p.get("status");
        const orderId = p.get("order_id");
        if (!status || !orderId) return;

        if (status === "success") {
            toast("success", "Payment successful", "Your submission will be published by the editor.");
        } else if (status === "pending") {
            toast("warning", "Payment pending", "Your payment is still being processed. Please check again shortly.");
        } else {
            toast("error", "Payment failed", p.get("message") || "Payment was not successful.");
        }

        ["status", "order_id", "message"].forEach((k) => p.delete(k));
        const qs = p.toString();
        history.replaceState({}, "", window.location.pathname + (qs ? `?${qs}` : ""));

        showReceipt(orderId);
    }

    function init() {
        // Delegated listeners: they still work if the modal markup is added to the page
        // after this script runs (partials, AJAX-loaded layouts, etc.)
        document.addEventListener("click", (e) => {
            if (e.target.closest("#saPayProceedBtn")) start();
            else if (e.target.closest("#saReceiptDownloadBtn")) downloadReceipt();
            else if (e.target.closest("#saReceiptRefreshBtn")) showReceipt(receiptOrderId);
        });
        document.addEventListener("change", (e) => {
            if (e.target.id === "saPayConfirmCheck") updateProceedState();
        });

        // If the user presses Back from the payment page, the browser may restore this page
        // with `starting` still true. Reset it so the button works again.
        window.addEventListener("pageshow", (e) => {
            if (e.persisted) {
                starting = false;
                updateProceedState();
            }
        });

        showReturnResult();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    window.ArticlePayment = { open, showReceipt };
})();