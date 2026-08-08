@extends('layouts.admin')

@section('content')
<!-- About Page Contents -->
<section class="inner_p">
    <div class="content_top_wrapper">
        <div class="p_cards">

            <div class="heading">
                About Page Contents
            </div>

            <!-- Data Loading -->
            <div id="abcLoading" class="text-center py-5">
                <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
            </div>

            <div id="abcFormContainer" class="d-none">

                <!-- From -->
                <form id="abcForm" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="abcId">
                    <input type="hidden" id="abcMethod" value="POST">

                    <!-- Section Top -->

                    <div class="inner_fp">

                        <div class="ssid">About Section</div>

                        <div class="content_container">

                            <div class="content_inner">
                                <div class="content_partitions">

                                    <!-- Badge / Eyebrow Label -->
                                    <div class="partitions_inner">
                                        <label>Badge / Eyebrow Label</label>
                                        <input type="text" class="content_show" id="about_badge" name="about_badge"
                                            placeholder="ABOUT">
                                        <div class="invalid-feedback" id="err_about_badge"></div>
                                    </div>

                                    <!-- Main Heading -->
                                    <div class="partitions_inner">
                                        <label>Main Heading <span class="text-danger">*</span></label>
                                        <input type="text" class="content_show" id="about_heading"
                                            name="about_heading" placeholder="RNTU Journals" required>
                                        <div class="invalid-feedback" id="err_about_heading"></div>
                                    </div>

                                </div>
                            </div>

                            <!-- Description Top -->
                            <div class="content_inner">
                                <div class="heading_p">Description Top <span class="text-danger">*</span></div>
                                <div id="ck_about_description_1" class="ckeditor-wrapper"></div>
                                <textarea class="content_show d-none" rows="2" id="about_description_1" name="about_description_1"></textarea>
                                <div class="error-red" id="err_about_description_1"></div>
                            </div>

                            <!-- Description Bottom -->
                            <div class="content_inner">
                                <div class="heading_p">Description Bottom</div>
                                <div id="ck_about_description_2" class="ckeditor-wrapper"></div>
                                <textarea class="content_show d-none" id="about_description_2" name="about_description_2"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <!-- Image 1-->
                                    <div class="content_inner">
                                        <div class="heading_p">Image<span class="hbc-hint">JPG/PNG/WEBP — max 2MB</span>
                                        </div>
                                        <input type="file" class="form-control form-control-sm"
                                            id="about_section_img1" name="about_section_img1"
                                            accept="image/jpg,image/jpeg,image/png,image/webp">
                                        <div class="invalid-feedback" id="err_about_section_img1"></div>
                                        <div id="previewAboutImg1" class="hbc-img-preview d-none">
                                            <div class="text-muted">Current:</div>
                                            <img id="thumbAboutImg1" src="" alt="">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <!-- Image 2-->
                                    <div class="content_inner">
                                        <div class="heading_p">Image<span class="hbc-hint">JPG/PNG/WEBP — max 2MB</span>
                                        </div>
                                        <input type="file" class="form-control form-control-sm"
                                            id="about_section_img2" name="about_section_img2"
                                            accept="image/jpg,image/jpeg,image/png,image/webp">
                                        <div class="invalid-feedback" id="err_about_section_img2"></div>
                                        <div id="previewAboutImg2" class="hbc-img-preview d-none">
                                            <div class="text-muted">Current:</div>
                                            <img id="thumbAboutImg2" src="" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Section Second -->

                    <div class="inner_fp">

                        <div class="ssid">Second Two</div>

                        <div class="content_container">

                            <div class="content_inner">
                                <div class="content_partitions">

                                    <!-- Badge / Eyebrow Label -->
                                    <div class="partitions_inner">
                                        <label>Badge / Eyebrow Label</label>
                                        <input type="text" class="content_show" id="why_badge" name="why_badge"
                                            placeholder="PUBLISHING">
                                        <div class="invalid-feedback" id="err_why_badge"></div>
                                    </div>

                                    <!-- Main Heading -->
                                    <div class="partitions_inner">
                                        <label>Main Heading <span class="text-danger">*</span></label>
                                        <input type="text" class="content_show" id="why_heading"
                                            name="why_heading" placeholder="Why Researchers Trust Us" required>
                                        <div class="invalid-feedback" id="err_why_heading"></div>
                                    </div>

                                </div>
                            </div>

                            <!-- Description Top -->
                            <div class="content_inner">
                                <div class="heading_p">Description Top <span class="text-danger">*</span></div>
                                <div id="ck_why_description_1" class="ckeditor-wrapper"></div>
                                <textarea class="content_show d-none" rows="2" id="why_description_1" name="why_description_1"></textarea>
                                <div class="error-red" id="err_why_description_1"></div>
                            </div>

                            <!-- Description Bottom -->
                            <div class="content_inner">
                                <div class="heading_p">Description Bottom</div>
                                <div id="ck_why_description_2" class="ckeditor-wrapper"></div>
                                <textarea class="content_show d-none" id="why_description_2" name="why_description_2"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <!-- Image -->
                                    <div class="content_inner">
                                        <div class="heading_p">Image<span class="hbc-hint">JPG/PNG/WEBP — max
                                                2MB</span></div>
                                        <input type="file" class="form-control form-control-sm"
                                            id="why_section_image" name="why_section_image"
                                            accept="image/jpg,image/jpeg,image/png,image/webp">
                                        <div class="invalid-feedback" id="err_why_section_image"></div>
                                        <div id="previewWhyImg" class="hbc-img-preview d-none">
                                            <div class="text-muted">Current:</div>
                                            <img id="thumbWhyImg" src="" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <section class="term_con">

                                <!-- Approve Abstract -->
                                <div class="button_d">
                                    <button type="button" class="green_d" id="abcSaveBtn">
                                        <span id="abcSaveSpinner"
                                            class="spinner-border spinner-border-sm d-none me-1"></span>
                                        <span id="abcSaveBtnText">Save</span>
                                    </button>
                                </div>

                            </section>

                        </div>

                    </div>

                </form>

            </div>

        </div>
    </div>
<!-- Toast Container -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080; margin-top: 70px;">
            <div id="ecToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-start gap-2">
                        <span id="ecToastIcon" style="color:white;"></span>
                        <div>
                            <div id="ecToastTitle" class="fw-semibold" style="color:white;"></div>
                            <div id="ecToastMsg" style="font-size:13px;color:white;"></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-progress" style="height:3px; background:rgba(255,255,255,.4);">
                    <div id="ecToastBarInner" style="height:100%; background:#fff; width:100%;"></div>
                </div>
            </div>
        </div>
</section>
@endsection



@section('scripts')
<script src="{{ asset('assets/js/admin/aboutcontent.js') }}"></script>
@endsection