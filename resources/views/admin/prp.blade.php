@extends('layouts.admin')

@section('content')
    <!-- PRP Page Contents -->
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    PRP Page Contents
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
    <script src="{{ asset('assets/js/admin/prp.js') }}"></script>
@endsection