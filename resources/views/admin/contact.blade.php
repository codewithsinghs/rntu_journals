@extends('layouts.admin')

@section('content')
    {{-- Page Start --}}
    <section class="inner_p">

        <div class="content_top_wrapper">

            <div class="p_cards" style="padding: 20px;">

                <div class="heading" style="margin-bottom: 20px;">
                    Contact Page
                </div>

                <div id="ctcLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                </div>

                <div id="ctcFormContainer" class="d-none">
                    <form id="ctcForm" novalidate>
                        @csrf
                        <input type="hidden" id="ctcId">
                        <input type="hidden" id="ctcMethod" value="POST">

                        {{-- Page Badge --}}
                        <div class="inner_fp">

                            <div class="ssid">Page Badge</div>

                            <div class="content_container">

                                <!-- Badge / Eyebrow -->
                                <div class="content_inner">
                                    <div class="heading_p">Badge / Eyebrow <span class="text-danger">*</span></div>
                                    <input type="text" class="content_show" id="contact_badge" name="contact_badge"
                                        placeholder="CONTACT US" required>
                                    <div class="invalid-feedback" id="err_contact_badge"></div>
                                </div>

                            </div>

                        </div>

                        {{-- Contact Block 1 --}}
                        <div class="inner_fp mt-4">

                            <div class="ssid">Contact Block 1</div>

                            <div class="content_container">

                                <!-- Heading -->
                                <div class="content_inner">
                                    <div class="heading_p">Heading <span class="text-danger">*</span> </div>
                                    <input type="text" class="content_show" id="contact_heading1" name="contact_heading1"
                                        required>
                                    <div class="invalid-feedback" id="err_contact_heading1"></div>
                                </div>

                                <!-- Detail -->
                                <div class="content_inner">
                                    <div class="heading_p">Detail <span class="text-danger">*</span></div>
                                    <div id="ck_contact_detail1" class="ctc-ck-wrap"></div>
                                    <textarea class="d-none content_show" id="contact_detail1" name="contact_detail1"></textarea>
                                    <div class="error-red" id="err_contact_detail1"></div>
                                </div>

                            </div>

                        </div>

                        {{-- Contact Block 2 --}}
                        <div class="inner_fp mt-4">

                            <div class="ssid">Contact Block 2</div>

                            <div class="content_container">

                                <!-- Heading -->
                                <div class="content_inner">
                                    <div class="heading_p">Heading <span class="text-danger">*</span> </div>
                                    <input type="text" class="content_show" id="contact_heading2" name="contact_heading2"
                                        required>
                                    <div class="invalid-feedback" id="err_contact_heading2"></div>
                                </div>

                                <!-- Detail -->
                                <div class="content_inner">
                                    <div class="heading_p">Detail <span class="text-danger">*</span></div>
                                    <div id="ck_contact_detail2" class="ctc-ck-wrap"></div>
                                    <textarea class="d-none content_show" id="contact_detail2" name="contact_detail2"></textarea>
                                    <div class="error-red" id="err_contact_detail2"></div>
                                </div>

                            </div>

                        </div>

                        {{-- Contact Block 3 --}}
                        <div class="inner_fp mt-4">

                            <div class="ssid">Contact Block 3</div>

                            <div class="content_container">

                                <!-- Heading -->
                                <div class="content_inner">
                                    <div class="heading_p">Heading <span class="text-danger">*</span> </div>
                                    <input type="text" class="content_show" id="contact_heading3" name="contact_heading3"
                                        required>
                                    <div class="invalid-feedback" id="err_contact_heading3"></div>
                                </div>

                                <!-- Detail -->
                                <div class="content_inner">
                                    <div class="heading_p">Detail <span class="text-danger">*</span></div>
                                    <div id="ck_contact_detail3" class="ctc-ck-wrap"></div>
                                    <textarea class="d-none content_show" id="contact_detail3" name="contact_detail3"></textarea>
                                    <div class="error-red" id="err_contact_detail3"></div>
                                </div>

                            </div>

                            {{-- Button --}}
                            <section class="term_con">
                                <div class="button_d">
                                    <button type="button" class="green_d" id="ctcSaveBtn">
                                        <span id="ctcSaveSpinner"
                                            class="spinner-border spinner-border-sm d-none me-1"></span>
                                        <span id="ctcSaveBtnText">Save</span>
                                    </button>
                                </div>
                            </section>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </section>

    {{-- Toast --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="ecToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span id="ecToastIcon" style="color:white;"></span>
                    <div>
                        <div id="ecToastTitle" class="fw-semibold" style="font-size:14px; color:white;"></div>
                        <div id="ecToastMsg" class="opacity-75" style="font-size:13px; color:white;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            <div style="height:3px;width:100%;background:rgba(255,255,255,0.3);border-radius:0 0 6px 6px;">
                <div id="ecToastBarInner"
                    style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width 4s linear;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/contact.js') }}"></script>
@endsection 
