@extends('layouts.admin')

@section('content')
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                <div class="heading">
                    Settings
                </div>

                <div id="hbcLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:28px;height:28px;" role="status"></div>
                    <p class="text-muted mt-2 mb-0" style="font-size:14px;">Loading…</p>
                </div>


                <div id="hbcFormContainer" class="d-none">

                    <form id="hbcForm" enctype="multipart/form-data" novalidate>

                        @csrf

                        <input type="hidden" id="hbcId">
                        <input type="hidden" id="hbcMethod" value="POST">


                        <!-- Aim & Scope -->

                        <div class="inner_fp">

                            <div class="ssid">Aim & Scope</div>

                            <div class="content_container">
                                <div class="content_inner">
                                    <div class="content_partitions">

                                        <div class="partitions_inner">
                                            <label>Badge / Eyebrow Label</label>
                                            <input type="text" class="content_show" id="aim_and_scope_title_1"
                                                name="aim_and_scope_title_1" placeholder="RNTU JOURNAL" >
                                            <div class="invalid-feedback" id="err_aim_and_scope_title_1"></div>
                                        </div>

                                        <div class="partitions_inner">
                                            <label>Section Heading<span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="aim_and_scope_title_2"
                                                name="aim_and_scope_title_2" placeholder="Aim and Scope" required>
                                            <div class="invalid-feedback" id="err_aim_and_scope_title_2"></div>
                                        </div>

                                        <div class="partitions_inner mar_part">
                                            <label>Scope Sub-Heading<span class="text-danger">*</span></label>
                                            <input type="text" class="content_show" id="aim_and_scope_title_3"
                                                name="aim_and_scope_title_3" placeholder="Scope of Publication" required>
                                            <div class="invalid-feedback" id="err_aim_and_scope_title_3"></div>
                                        </div>

                                    </div>
                                </div>


                                <div class="content_inner">
                                    <label>Aim & Scope Description<span class="text-danger">*</span></label>
                                    <div id="ck_aim_and_scope_description" class="ckeditor-wrapper"></div>
                                    <textarea id="aim_and_scope_description" name="aim_and_scope_description" class="content_show d-none"></textarea>
                                    <div class="error-red" id="err_aim_and_scope_description"></div>
                                </div>

                                <div class="content_inner">
                                    <label>Scope of Publication Description<span class="text-danger">*</span></label>
                                    <div id="ck_scope_of_publication_description" class="ckeditor-wrapper"></div>
                                    <textarea id="scope_of_publication_description" name="scope_of_publication_description" class="content_show d-none"></textarea>
                                    <div class="error-red" id="err_scope_of_publication_description"></div>
                                </div>


                                <div class="content_inner">
                                    <label>University Highlight Quote</label>
                                    <div id="ck_university_highlight_quote"
                                        class="ckeditor-wrapper ckeditor-wrapper--short"></div>
                                    <textarea id="university_highlight_quote" name="university_highlight_quote" class="content_show d-none"></textarea>
                                </div>




                                <div class="content_inner">

                                    <label>Aim Section Image</label>
                                    <input type="file" id="aim_section_image" name="aim_section_image"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" class="content_show">
                                    <div class="invalid-feedback" id="err_aim_section_image"></div>
                                    <div id="currentImagePreview" class="mt-2 d-none">
                                        <small class="text-muted">Current image:</small><br>
                                        <img id="currentImageThumb" src=""
                                            style="width:15%;border-radius:6px;border:1px solid #dee2e6;object-fit:cover;">
                                    </div>

                                </div>

                            </div>
                        </div>
                </div>

                <!-- Why RNTU Stats -->

                <div class="inner_fp mt-4">

                    <div class="ssid">Why RNTU Stats</div>

                    <div class="content_container">
                        <div class="content_inner">
                            <div class="content_partitions">

                                <div class="partitions_inner">
                                    <label>Stats Section Heading</label>
                                    <input type="text" class="content_show" id="why_rntu_title_1" name="why_rntu_title_1"
                                        placeholder="Why Choose RNTU Journals?">
                                    <div class="invalid-feedback" id="err_why_rntu_title_1"></div>
                                </div>


                                <div class="partitions_inner">
                                    <label>Stats Sub-Heading</label>
                                    <input type="text" class="content_show" id="why_rntu_title_2"
                                        name="why_rntu_title_2" placeholder="Trusted by researchers worldwide">
                                </div>

                            </div>

                        </div>

                        <div class="content_inner">


                            @foreach ([['why_rntu_years', 'why_rntu_years_label', 'Years'], ['why_rntu_articles', 'why_rntu_articles_label', 'Articles'], ['why_rntu_journals', 'why_rntu_journals_label', 'Journals'], ['why_rntu_readers', 'why_rntu_readers_label', 'Readers'], ['why_rntu_access', 'why_rntu_access_label', 'Access']] as [$val, $lbl, $name])
                                <label>{{ $name }}</label>
                                <input type="text" class="content_show" id="{{ $val }}"
                                    name="{{ $val }}" placeholder="{{ $name }} value" required>
                                <div class="invalid-feedback" id="err_{{ $val }}"></div>
                                <input type="text" class="content_show" id="{{ $lbl }}"
                                    name="{{ $lbl }}" placeholder="{{ $name }} label" required>
                                <div class="invalid-feedback" id="err_{{ $lbl }}"></div>
                            @endforeach

                        </div>
                    </div>
                </div>

                <!-- Support Section -->
                <div class="inner_fp mt-4">

                    <div class="ssid">Support Section</div>

                    <div class="content_container">

                        <div class="content_inner">

                            <div class="content_partitions">

                                <div class="partitions_inner">
                                    <label>Section Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="support_section_heading"
                                        name="support_section_heading" required>
                                    <div class="invalid-feedback" id="err_support_section_heading"></div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Articles Count <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="support_articles_count"
                                        name="support_articles_count" required>
                                    <div class="invalid-feedback" id="err_support_articles_count"></div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Short Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="support_short_heading"
                                        name="support_short_heading" required>
                                    <div class="invalid-feedback" id="err_support_short_heading"></div>
                                </div>

                            </div>

                        </div>

                        <div class="content_inner">
                            <label>Section Description <span class="text-danger">*</span></label>
                            <div id="ck_support_section_description" class="ckeditor-wrapper ckeditor-wrapper--short">
                            </div>
                            <textarea id="support_section_description" name="support_section_description" rows="2"
                                class="content_show d-none"></textarea>
                            <div class="error-red" id="err_support_section_description"></div>
                        </div>

                    </div>

                </div>


                <!-- Latest Journal -->
                <div class="inner_fp mt-4">

                    <div class="ssid">Latest Journal</div>

                    <div class="content_container">

                        <div class="content_inner">

                            <div class="content_partitions">

                                <div class="partitions_inner">
                                    <label>Badge / Eyebrow</label>
                                    <input type="text" class="content_show" id="latest_journal_title"
                                        name="latest_journal_title" placeholder="LATEST ISSUES">
                                    <div class="invalid-feedback" id="err_latest_journal_title"></div>
                                </div>

                                <div class="partitions_inner">
                                    <label>Section Heading <span class="text-danger">*</span></label>
                                    <input type="text" class="content_show" id="latest_journal_heading"
                                        name="latest_journal_heading" placeholder="Latest Journal Issues" required>
                                    <div class="invalid-feedback" id="err_latest_journal_heading"></div>
                                </div>

                            </div>

                        </div>

                        <div class="content_inner">

                            <label>Section Description <span class="text-danger">*</span></label>
                            <div id="ck_latest_journal_description" class="ckeditor-wrapper ckeditor-wrapper--short">
                            </div>
                            <textarea id="latest_journal_description" name="latest_journal_description" rows="2"
                                class="content_show d-none"></textarea>
                            <div class="error-red" id="err_latest_journal_description"></div>

                        </div>

                    </div>

                </div>


                <!-- Footer Section -->
                <div class="inner_fp mt-4">

                    <div class="ssid">Footer Section</div>

                    <div class="content_container">

                        <div class="content_inner">

                            <label>Footer Description <span class="text-danger">*</span></label>
                            <div id="ck_footer_about_description" class="ckeditor-wrapper ckeditor-wrapper--short"></div>
                            <textarea id="footer_about_description" name="footer_about_description" rows="2" class="content_show d-none"></textarea>
                            <div class="error-red" id="err_footer_about_description"></div>

                        </div>

                    </div>

                </div>

                <!-- Button -->
                <section class="term_con">

                    <div class="button_d">
                        <button type="submit" class="green_d" id="hbcSaveBtn">
                            <span id="hbcSaveSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>

                            <span id="hbcSaveBtnText">
                                Save
                            </span>
                        </button>
                    </div>

                </section>

                </form>

            </div>

        </div>

        </div>

    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/homebasiccontent.js') }}"></script>
@endsection
