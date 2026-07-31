@extends('layouts.admin')

@section('content')
    <!-- Guidelines Page Contents -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Guidelines Page Contents
                </div>

                <!-- Data Load -->
                <div id="glLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                </div>

                <div id="glFormContainer" class="d-none">

                    <!-- Form -->
                    <form id="glForm" novalidate>
                        @csrf
                        <input type="hidden" id="glId">
                        <input type="hidden" id="glMethod" value="POST">

                        <!-- Instructions for Authors -->
                        <div class="inner_fp">

                            <div class="ssid">Instructions for Authors</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">AUTHOR GUIDELINES</span></label>
                                            <input type="text" class="content_show" id="author_badge" name="author_badge"
                                                placeholder="AUTHOR GUIDELINES" required>
                                            <div class="invalid-feedback" id="err_author_badge"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="author_heading"
                                                name="author_heading" placeholder="Instructions for Authors" required>
                                            <div class="invalid-feedback" id="err_author_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_author_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="author_description" name="author_description"></textarea>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Submission Process -->
                        <div class="inner_fp">

                            <div class="ssid">Submission Process</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">PROCESS</span></label>
                                            <input type="text" class="content_show" id="process_badge"
                                                name="process_badge" placeholder="PROCESS" required>
                                            <div class="invalid-feedback" id="err_process_badge"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="process_heading"
                                                name="process_heading"placeholder="Submission Process" required>
                                            <div class="invalid-feedback" id="err_process_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_process_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="process_description" name="process_description"></textarea>
                                        <div class="gl-ck-error" id="err_process_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- New Manuscript -->
                        <div class="inner_fp">

                            <div class="ssid">New Manuscript</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">MANUSCRIPT PREPARATION</span></label>
                                            <input type="text" class="content_show" id="manuscript_badge"
                                                name="manuscript_badge" placeholder="MANUSCRIPT PREPARATION" required>
                                            <div class="invalid-feedback" id="err_manuscript_badge"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="manuscript_heading"
                                                name="manuscript_heading" placeholder="New Manuscripts" required>
                                            <div class="invalid-feedback" id="err_manuscript_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_manuscript_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="manuscript_description" name="manuscript_description"></textarea>
                                        <div class="gl-ck-error" id="err_manuscript_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Formatting -->
                        <div class="inner_fp">

                            <div class="ssid">Formatting</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">DOCUMENT FORMAT REFERENCE</span></label>
                                            <input type="text" class="content_show" id="formatting_badge1"
                                                name="formatting_badge1" placeholder="DOCUMENT FORMAT REFERENCE" required>
                                            <div class="invalid-feedback" id="err_formatting_badge1"></div>
                                        </div>

                                        <!-- Badge 2 -->
                                        <div class="partitions_inner">
                                            <label>Badge 2 <span class="text-danger">*</span> <span class="gl-hint">e.g.
                                                    IEEE STYLE</span></label>
                                            <input type="text" class="content_show" id="formatting_badge2"
                                                name="formatting_badge2" placeholder="IEEE STYLE" required>
                                            <div class="invalid-feedback" id="err_formatting_badge2"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="formatting_heading"
                                                name="formatting_heading" placeholder="Formatting" required>
                                            <div class="invalid-feedback" id="err_formatting_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_formatting_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="formatting_description" name="formatting_description"></textarea>
                                        <div class="gl-ck-error" id="err_formatting_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Page Layout -->
                        <div class="inner_fp">

                            <div class="ssid">Page Layout</div>

                            <div class="content_container">

                                <div class="content_inner">


                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">PAGE LAYOUT</span></label>
                                            <input type="text" class="content_show" id="layout_badge1"
                                                name="layout_badge1" placeholder="PAGE LAYOUT" required>
                                            <div class="invalid-feedback" id="err_layout_badge1"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="layout_heading"
                                                name="layout_heading" placeholder="New Manuscripts" required>
                                            <div class="invalid-feedback" id="err_layout_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_layout_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="layout_description" name="layout_description"></textarea>
                                        <div class="gl-ck-error" id="err_layout_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Acknowledgement -->
                        <div class="inner_fp">

                            <div class="ssid">Acknowledgement</div>

                            <div class="content_container">

                                <div class="content_inner">

                                    <div class="content_partitions">

                                        <!-- Badge -->
                                        <div class="partitions_inner">
                                            <label>Badge <span class="gl-hint">RNTU JOURNALS</span></label>
                                            <input type="text" class="content_show" id="acknowlegdement_badge1"
                                                name="acknowlegdement_badge1" placeholder="RNTU JOURNALS" required>
                                            <div class="invalid-feedback" id="err_acknowlegdement_badge1"></div>
                                        </div>

                                        <!-- Heading -->
                                        <div class="partitions_inner">
                                            <label>Heading <span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="acknowlegdement_heading"
                                                name="acknowlegdement_heading" placeholder="New Manuscripts" required>
                                            <div class="invalid-feedback" id="err_acknowlegdement_heading"></div>
                                        </div>

                                    </div>

                                    <!-- Description -->
                                    <div class="content_inner">
                                        <div class="heading_p">Description <span class="text-danger">*</span></div>
                                        <div id="ck_acknowlegdement_description" class="gl-ck-wrap"></div>
                                        <textarea class="content_show d-none" id="acknowlegdement_description" name="acknowlegdement_description"></textarea>
                                        <div class="gl-ck-error" id="err_acknowlegdement_description"></div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Button -->
                        <section class="term_con">
                            <div class="button_d">
                                <button type="button" class="green_d" id="glSaveBtn">
                                    <span id="glSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                                    <span id="glSaveBtnText">Save</span>
                                </button>
                            </div>
                        </section>

                    </form>

                </div>

            </div>
        </div>
    </section>

    <!-- Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="glToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="glToastIcon" style="color:white;"></span>
                    <div>
                        <div id="glToastTitle" class="fw-semibold" style="font-size:14px; color:white;"></div>
                        <div id="glToastMsg" class="opacity-75" style="font-size:13px; color:white;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="glToastBar"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/guidelines.js') }}"></script>
@endsection
