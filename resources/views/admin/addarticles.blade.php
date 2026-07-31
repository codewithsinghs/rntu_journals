@extends('layouts.admin')

@section('content')
    <main class="main-content">

        <!-- Create Article -->
        <section class="inner_p">
            <div class="content_top_wrapper">
                <div class="p_cards">

                    <div class="heading">
                        Create Article
                    </div>

                    <form id="aaForm" novalidate>

                        <div class="inner_fp">

                            <div class="content_container mt-0">

                                <!-- Detail of Author -->
                                <div class="content_inner">
                                    <div class="heading_p">Detail of Author</div>

                                    <div class="content_partitions">

                                        <!-- Author / PI Full Name -->
                                        <div class="partitions_inner">
                                            <label>Author / PI Full Name</label>
                                            <input type="text" class="content_show" name="full_name"id="full_name">
                                            <div class="validation-error-addarticle" data-error-for="full_name"></div>
                                        </div>

                                        <!-- Mobile No. -->
                                        <div class="partitions_inner">
                                            <label>Mobile No.</label>
                                            <input type="text" class="content_show" name="mobile_no" id="mobile_no">
                                            <div class="validation-error-addarticle" data-error-for="mobile_no"></div>
                                        </div>

                                        <!-- Email Address -->
                                        <div class="partitions_inner">
                                            <label>Email Address</label>
                                            <input type="email" class="content_show" name="email" id="email">
                                            <div class="validation-error-addarticle" data-error-for="email"></div>
                                        </div>

                                        <!-- Affiliating Institute -->
                                        <div class="partitions_inner mar_part">
                                            <label>Affiliating Institute</label>
                                            <input type="text" class="content_show" name="affiliating_institute"
                                                id="affiliating_institute">
                                            <div class="validation-error-addarticle" data-error-for="affiliating_institute">
                                            </div>
                                        </div>

                                        <!-- Department -->
                                        <div class="partitions_inner mar_part">
                                            <label>Department</label>
                                            <input type="text" class="content_show" name="department" id="department">
                                            <div class="validation-error-addarticle" data-error-for="department"></div>
                                        </div>

                                        <!-- ORCID ID -->
                                        <div class="partitions_inner mar_part">
                                            <label>ORCID ID</label>
                                            <input type="text" class="content_show" name="orcid_id" id="orcid_id">
                                            <div class="validation-error-addarticle" data-error-for="orcid_id"></div>
                                        </div>

                                    </div>

                                    <!-- Enter Affiliating Institute Address -->
                                    <div class="content_inner" style="margin: 0;">
                                        <label>Enter Affiliating Institute Address</label>
                                        <textarea class="content_show" name="affiliating_institute_address" id="affiliating_institute_address" rows="2"></textarea>
                                        <div class="validation-error-addarticle"
                                            data-error-for="affiliating_institute_address"></div>
                                    </div>

                                </div>

                                <!-- Name / Title of Article -->
                                <div class="content_inner">
                                    <div class="heading_p">Name / Title of Article </div>
                                    <input type="text" class="content_show" name="manuscript_title"
                                        id="manuscript_title">
                                    <div class="validation-error-addarticle" data-error-for="manuscript_title"></div>
                                </div>

                                <!-- Journals -->
                                <div class="content_inner">
                                    <div class="heading_p">Journals</div>
                                    <select class="content_show" name="journal_id" id="journal_id">
                                        <option value="">Loading journals…</option>
                                    </select>
                                    <div class="validation-error-addarticle" data-error-for="journal_id"></div>
                                </div>

                                <!-- Abstract of Article -->
                                <div class="content_inner">
                                    <div class="heading_p">Abstract of Article</div>
                                    <textarea class="content_show" name="abstract_summary" id="abstract_summary" rows="5"></textarea>
                                    <div class="validation-error-addarticle" data-error-for="abstract_summary"></div>
                                </div>

                                <!-- Keywords -->
                                <div class="content_inner">
                                    <div class="heading_p">Keywords</div>
                                    <input type="text" class="content_show" id="keywordInput"
                                        placeholder="Type a keyword and press Enter">

                                    <div class="content_inner">
                                        <div class="content_partitions" id="keywordTags"> </div>
                                    </div>

                                    <div class="validation-error-addarticle" data-error-for="keywords"></div>
                                </div>

                                <!-- References (optional) -->
                                <div class="content_inner">
                                    <div class="heading_p">References (optional)</div>
                                    <textarea class="content_show" name="references" id="references" rows="3"></textarea>
                                    <div class="validation-error-addarticle" data-error-for="references"></div>
                                </div>

                                <!-- Detail of Co-Author -->
                                <div class="content_inner" style="position: relative;">
                                    <div class="heading_p">Detail of Co-Author</div>
                                    <button type="button" class="edit-btn" id="addCoAuthorBtn"
                                        style="position: absolute;right: 0;">+ Add Co-Author</button>
                                    <div id="coAuthorsWrap"></div>
                                    <div class="content_show">You can ADD Only 10 Co-Author for ADD Click on Button to ADD
                                    </div>
                                </div>

                                <!-- Recommended Reviewers -->
                                <div class="content_inner" style="position: relative;">
                                    <div class="heading_p">Recommended Reviewers (optional)</div>
                                    <button type="button" class="edit-btn" id="addReviewerBtn"
                                        style="position: absolute;right: 0;">+ Add Reviewer</button>
                                    <div id="reviewersWrap"></div>
                                    <div class="content_show">You can ADD Only 5 Reviewers for ADD Click on Button to ADD
                                    </div>
                                </div>


                                <!-- Corresponding Author Signature -->
                                <div class="content_inner">
                                    <div class="heading_p">Corresponding Author Signature</div>

                                    <div class="content_partitions">

                                        <!-- Name of Corresponding Author -->
                                        <div class="partitions_inner">
                                            <label>Name of Corresponding Author</label>
                                            <input type="text" class="content_show" name="author_signature"
                                                id="author_signature">
                                            <div class="validation-error-addarticle" data-error-for="author_signature">
                                            </div>
                                        </div>

                                        <!-- Signature -->
                                        <div class="partitions_inner">
                                            <label>Signature</label>
                                            <input type="file" class="content_show" name="signature_file"
                                                id="signature_file" accept=".jpg,.jpeg,.png">
                                            <div class="validation-error-addarticle" data-error-for="signature_file">
                                            </div>
                                        </div>

                                        <!-- Date -->
                                        <div class="partitions_inner mar_part">
                                            <label>Date</label>
                                            <input type="date" class="content_show" name="submission_date"
                                                id="submission_date">
                                            <div class="validation-error-addarticle" data-error-for="submission_date">
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <!-- Article Paper -->
                                <div class="content_inner">
                                    <div class="heading_p">Article Paper</div>

                                    <div class="content_partitions">

                                        <!-- Article PDF -->
                                        <div class="partitions_inner">
                                            <label>Upload Article PDF</label>
                                            <input type="file" class="content_show" name="signed_manuscript_pdf"
                                                id="signed_manuscript_pdf" accept=".pdf">
                                            <div class="validation-error-addarticle"
                                                data-error-for="signed_manuscript_pdf"></div>
                                        </div>

                                        <!-- Article Doc -->
                                        <div class="partitions_inner">
                                            <label>Upload Article Doc</label>
                                            <input type="file" class="content_show" name="abstract_file"
                                                id="abstract_file" accept=".pdf,.doc,.docx">
                                            <div class="validation-error-addarticle" data-error-for="abstract_file"></div>
                                        </div>

                                    </div>

                                </div>

                                <div class="content_inner">
                                    <div class="heading_p">Declarations </div>

                                    <div class="form-check mb-2"
                                        style=" display: flex; justify-content: start; align-items: center; gap: 20px;">
                                        <input class="form-check-input" type="checkbox" value="original"
                                            name="declarations[]" id="decl_original" style="padding: 10px;">
                                        <label style="font-size:13px;margin: 0;" for="decl_original">This work is original
                                            and has not been published elsewhere.</label>
                                    </div>

                                    <div class="form-check mb-2"
                                        style=" display: flex; justify-content: start; align-items: center; gap: 20px;">
                                        <input class="form-check-input" type="checkbox" value="not_under_review"
                                            name="declarations[]" id="decl_not_under_review" style="padding: 10px;">
                                        <label style="font-size:13px;margin: 0;" for="decl_not_under_review">This
                                            manuscript is not under review elsewhere.</label>
                                    </div>

                                    <div class="form-check mb-2"
                                        style=" display: flex; justify-content: start; align-items: center; gap: 20px;">
                                        <input class="form-check-input" type="checkbox" value="all_approved"
                                            name="declarations[]" id="decl_all_approved" style="padding: 10px;">
                                        <label style="font-size:13px;margin: 0;" for="decl_all_approved">All co-authors
                                            have approved this submission.</label>
                                    </div>

                                    <div class="form-check mb-2"
                                        style=" display: flex; justify-content: start; align-items: center; gap: 20px;">
                                        <input class="form-check-input" type="checkbox" value="ethical_approval"
                                            name="declarations[]" id="decl_ethical_approval" style="padding: 10px;">
                                        <label style="font-size:13px;margin: 0;" for="decl_ethical_approval">Required
                                            ethical approvals have been obtained.</label>
                                    </div>

                                    <div class="form-check mb-2"
                                        style=" display: flex; justify-content: start; align-items: center; gap: 20px;">
                                        <input class="form-check-input" type="checkbox" value="data_accurate"
                                            name="declarations[]" id="decl_data_accurate" style="padding: 10px;">
                                        <label style="font-size:13px;margin: 0;" for="decl_data_accurate">All data
                                            presented is accurate to the best of my knowledge.</label>
                                    </div>

                                    <div class="validation-error-addarticle" data-error-for="declarations"></div>

                                    <div class="form-check"
                                        style=" display: flex; justify-content: start; align-items: center; gap: 20px;">
                                        <input class="form-check-input" type="checkbox" name="terms_accepted"
                                            id="terms_accepted" value="1" style="padding: 10px;">
                                        <label style="font-size:13px;margin: 0" for="terms_accepted">I accept the terms
                                            and instructions.</label>
                                    </div>

                                    <div class="validation-error-addarticle" data-error-for="terms_accepted"></div>

                                </div>


                                <section class="term_con">
                                    <div class="button_d"><button type="submit" class="green_d" id="aaSubmitBtn">Submit
                                            Article</button></div>
                                </section>

                            </div>

                        </div>

                    </form>

                </div>
            </div>
        </section>


    </main>

    <!-- Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="aaToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="aaToastMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/addarticles.js') }}"></script>
@endsection
