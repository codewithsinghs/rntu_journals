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

    <div class="p_inner" id="saRejectPage" data-id="{{ $id }}">
        <a href="/admin/submit-articles">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to list
        </a>
        <h3>Reject Submission</h3>
        <div>#{{ $id }}</div>

        <div>
            <label class="form-label" style="font-size:13px;font-weight:600;">Reason for rejection</label>
            <textarea class="form-control form-control-sm" id="saRejectRemarks" rows="5"
                placeholder="Why is this being rejected…"></textarea>

            <div>
                <a href="/admin/submit-articles" class="btn btn-light btn-sm px-4">Cancel</a>
                <button type="button" class="btn btn-danger btn-sm px-4" id="saRejectConfirmBtn">Reject</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_BASE = '/api/admin/submit-articles';
            const TOKEN = localStorage.getItem('jwt_token') || '';
            const authHeaders = () => ({
                'Accept': 'application/json',
                'Authorization': `Bearer ${TOKEN}`
            });
            const id = document.getElementById('saRejectPage').dataset.id;

            function showToast(type, title, msg) {
                const el = document.getElementById('saToast');
                document.getElementById('saToastTitle').textContent = title;
                const msgEl = document.getElementById('saToastMsg');
                msgEl.textContent = msg || '';
                msgEl.style.display = msg ? 'block' : 'none';
                document.getElementById('saToastIcon').innerHTML = type === 'success' ?
                    `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>` :
                    `<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`;
                el.classList.remove('bg-success', 'bg-danger');
                el.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
                bootstrap.Toast.getOrCreateInstance(el, {
                    delay: 4000,
                    autohide: true
                }).show();
            }

            document.getElementById('saRejectConfirmBtn').addEventListener('click', async () => {
                const remarks = document.getElementById('saRejectRemarks').value;
                if (!remarks.trim()) {
                    showToast('error', 'Reject failed', 'A reason is required.');
                    return;
                }
                try {
                    const res = await fetch(`${API_BASE}/${id}/reject`, {
                        method: 'POST',
                        headers: {
                            ...authHeaders(),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            remarks
                        }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.status) throw new Error(json.message || 'Reject failed.');
                    showToast('success', 'Rejected', json.message);
                    setTimeout(() => {
                        window.location.href = '/admin/submit-articles';
                    }, 600);
                } catch (e) {
                    showToast('error', 'Reject failed', e.message);
                }
            });
        });
    </script>
@endsection
