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

            const id = document.getElementById('saShowPage').dataset.id;


            const esc = (s) => (s ?? '')
                .toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');


            const fmtDate = (d) => {

                if (!d) return '—';

                const dt = new Date(d);

                return isNaN(dt) ?
                    d :
                    dt.toLocaleDateString('en-IN', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });

            };


            const fmtDateTime = (d) => {

                if (!d) return '—';

                const dt = new Date(d);

                return isNaN(dt) ?
                    d :
                    dt.toLocaleString('en-IN', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

            };


            const STAGE_LABELS = {
                submitted: 'Submitted',
                editor_approved: 'Editor Approved',
                with_reviewer: 'With Reviewer',
                reviewer_approved: 'Reviewer Approved',
                reviewer_correction: 'Correction Needed',
                reviewer_rejected: 'Reviewer Rejected',
                with_author: 'With Author',
                with_author_payment: 'Awaiting Payment',
                rejected: 'Rejected',
                published: 'Published',
            };


            function stageChip(stage) {

                if (!stage)
                    return '<span class="text-muted">—</span>';

                return `<span class="${esc(stage)}">
            ${esc(STAGE_LABELS[stage] || stage)}
            </span>`;

            }


            function statusPill(status) {

                if (!status)
                    return '<span class="text-muted">—</span>';

                return `
                ${esc(status.replace(/_/g,' '))}`;

            }

            function fileLink(url, label) {

                if (!url)
                    return '<span class="text-muted">Not provided</span>';

                return `
                <a href="${url}" target="_blank">
                    ${label}
                </a>`;
            }

            async function load() {

                try {

                    const res = await fetch(`${API_BASE}/${id}`, {
                        headers: authHeaders()
                    });

                    const json = await res.json();

                    if (!res.ok || !json.status)
                        throw new Error(json.message || 'Failed to load submission');
                    render(json.data);

                } catch (e) {

                    document.getElementById('saShowBody').innerHTML =
                        `<p class="text-danger">${esc(e.message)}</p>`;

                }

            }

            function render(r) {

                document.getElementById('saShowSubtitle').textContent = `#${r.id} · ${r.manuscript_title ?? ''}`;

                const html = s => s ? esc(s) : '<span class="text-muted">—</span>';

                const keywords = (r.keywords || []).map(k => `<span class="sa-decl-chip">${esc(k)}</span>`).join(
                    '') || html(null);

                const declarations = (r.declarations || []).map(d => `<span class="sa-decl-chip">${esc(d)}</span>`)
                    .join('') || html(null);

                const coAuthorsRows = (r.co_authors || []).length ?

                    r.co_authors.map(c => `
                    <tr>
                        <td>${esc(c.name)}</td>
                        <td>${esc(c.email)}</td>
                        <td>${esc(c.affiliation)}</td>
                        <td>${esc(c.orcid_id || '—')}</td>
                    </tr>
                    
                    `).join('') :

                    `<tr>
                        <td colspan="4" class="text-muted text-center">
                        No co-authors added
                        </td>
                    </tr>`;

                const reviewersRows = (r.reviewers || []).length ?

                    r.reviewers.map(rv => `
                    <tr>
                        <td>${esc(rv.name)}</td>
                        <td>${esc(rv.email)}</td>
                        <td>${esc(rv.institution)}</td>
                        <td>${esc(rv.area_of_expertise)}</td>
                    </tr>

                    `).join('') :

                    `<tr>
                        <td colspan="4" class="text-muted text-center">
                        No reviewers added
                        </td>
                    </tr>`;

                const review = r.review || {};

                document.getElementById('saShowBody').innerHTML = `

                <div class="inner_fp">

                    <div class="ssid">Workflow Status</div>

                    <div class="content_container">

                        <div class="content_inner"> 
                        
                            <div class="content_partitions"> 
                                
                                <div class="partitions_inner">
                                    <label>Current Stage</label>
                                    <div class="content_show">${stageChip(review.current_stage)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Editor Status</label>
                                    <div class="content_show">${statusPill(review.editor_status)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Reviewer</label>
                                    <div class="content_show">${statusPill(review.reviewer_status)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Final Status</label>
                                   <div class="content_show">${statusPill(review.final_status)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Submitted On</label>
                                   <div class="content_show">${fmtDate(r.submission_date)}</div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Created At</label>
                                    <div class="content_show">${fmtDateTime(r.created_at)}</div>
                                </div>

                            </div>
                    
                        </div>

                    </div>

                    </div>

                    <div class="inner_fp mt-4">

                        <div class="ssid">Author Details</div>

                        <div class="content_container">

                            <div class="content_inner"> 
                            
                                <div class="content_partitions"> 
                                    
                                    <div class="partitions_inner">
                                        <label>Name</label>
                                        <div class="content_show">${html(r.full_name)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Email</label>
                                        <div class="content_show">${html(r.email)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Mobile</label>
                                        <div class="content_show">${html(r.mobile_no)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Institute</label>
                                    <div class="content_show">${html(r.affiliating_institute)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>Department</label>
                                    <div class="content_show">${html(r.department)}</div>
                                    </div>

                                    <div class="partitions_inner">
                                        <label>ORCID</label>
                                        <div class="content_show">${html(r.orcid_id)}</div>
                                    </div>

                                </div>
                        
                            </div>

                        </div>

                        </div>


                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Manuscript &amp; Abstract</div>

                            <div class="content_container">

                                <div class="content_inner">
                                    <div class="heading_p">Manuscript Title</div>
                                    <div class="content_show">${html(r.manuscript_title)}</div>
                                </div>

                                <div class="content_inner">
                                    <div class="heading_p">Abstract</div>
                                    <div class="content_show">${html(r.abstract_summary)}</div>
                                </div>
                                
                                <div class="content_inner">

                                    <div class="heading_p">Keywords</div>
                                    
                                    <div class="content_partitions">
                                        
                                        <div class="partitions_inner">
                                            <div class="content_show">${keywords}</div>
                                        </div>
                                            
                                    </div>
                                            
                                </div>
                                            
                                <div class="content_inner">
                                    <div class="heading_p">References</div>
                                    <div class="content_show">${html(r.references)}</div>
                                </div>

                            </div>

                        </div>



                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Co Author Details</div>

                            <div class="content_container">

                                <div class="table-container" style="margin-top: 70px;">
                                <table class="status-table">
                                    <thead>
                                    <tr>
                                        <th>Name of Co Author</th>
                                        <th>Email Address</th>
                                        <th>Affiliating Institute</th>
                                        <th>ORCID ID</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        ${coAuthorsRows}
                                    </tbody>
                                </table>
                                </div>

                            </div>

                        </div>


                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Co Author Details</div>

                            <div class="content_container">

                                <div class="table-container" style="margin-top: 70px;">
                                <table class="status-table">
                                    <thead>
                                    <tr>
                                        <th>Name of Reviewer</th>
                                        <th>Email Address</th>
                                        <th>Affiliating Institute</th>
                                        <th>Area of Expertise</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        ${reviewersRows}
                                    </tbody>
                                </table>
                                </div>

                            </div>

                        </div>


                        <div class="inner_fp mt-4">

                            <div class="ssid">Corresponding Author Signature</div>

                            <div class="content_container">

                                <div class="content_inner"> 
                                
                                    <div class="content_partitions"> 
                                        
                                        <div class="partitions_inner">
                                            <label>Name of Corresponding Author</label>
                                            <div class="content_show">${html(r.author_signature)}</div>
                                        </div>

                                        <div class="partitions_inner">
                                            <label>Signature</label>
                                            <div class="content_show" style="background:green;">${fileLink(r.signature_img_url,'Click View to Image')}</div>
                                        </div>

                                        <div class="partitions_inner">
                                            <label>Submission Date</label>
                                            <div class="content_show">${fmtDate(r.submission_date)}</div>
                                        </div>

                                        <div class="partitions_inner">
                                            <label>Terms Accepted</label>
                                            <div class="content_show">${r.terms_accepted ? 'Yes' : '—'}</div>
                                        </div>

                                    </div>
                            
                                </div>

                            </div>

                        </div>

                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Article Files</div>

                            <div class="content_container">

                                <!-- Download Full Article Paper Doc-->
                                <div class="content_inner">
                                    <div class="heading_p">Full Article Paper Doc</div>
                                    <div class="paper_dowmload">
                                        <div class="content_show">Download Full Article Paper Doc</div>
                                        <div class="button_d"><button style="color:#fff;"> ${fileLink(r.abstract_file_url, 'Download Doc')} </button></div>
                                    </div>
                                </div>

                                <!-- Download Full Article Paper PDF-->
                                <div class="content_inner">
                                    <div class="heading_p">Full Article Paper PDF</div>
                                    <div class="paper_dowmload">
                                        <div class="content_show">Download Full Article Paper PDF</div>
                                        <div class="button_d"><button style="color:#fff;"> ${fileLink(r.signed_manuscript_pdf_url,'Download PDF')} </button></div>
                                    </div>
                                </div>

                            </div>

                        </div>


                        
                        <div class="inner_fp mt-4"> 

                            <div class="ssid">Declarations</div>

                            <div class="content_container">
                                <div class="content_inner">
                                    <div class="paper_dowmload">
                                        <div class="content_show">${declarations}</div>
                                    </div>
                                </div>

                            </div>

                        </div>


                        `;

            }


            load();

        });
    </script>
@endsection
