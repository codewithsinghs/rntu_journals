{{-- frontend/partials/aim-scope-why.blade.php --}}
<div id="aimScopeLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<section class="aim_scope_section d-none" id="aimScopeSection">
    <div class="s__container_custom">
        <div class="row align-items-center gy-5">

            <div class="col-lg-6">
                <div class="journal_image_wrapper">
                    <div class="bg_journal" id="aimSectionImageWrap"></div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="aim_scope_content">
                    <span class="journal_tag d-none" id="aimTitle1"></span>
                    <h2 id="aimTitle2"></h2>
                    <p id="aimDescription"></p>
                    <h3 id="aimTitle3"></h3>
                    <p id="scopeDescription"></p>
                    <div class="quote_box d-none" id="quoteBoxWrap">
                        <div class="quote_line"></div>
                        <p id="quoteText"></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="why_rntu_section d-none" id="whyRntuSection">
    <div class="s__container_custom">

        <div class="section_top text-center">
            <span class="section_tag d-none" id="whyTitle1"></span>
            <h2 id="whyTitle2"></h2>
        </div>

        <div class="why_features" id="whyFeatures"></div>

        <div class="research_card d-none" id="supportCard">
            <div class="research_overlay"></div>
            <div class="research_content">
                <div><h3 id="supportHeading"></h3></div>
                <div class="research_bottom">
                    <h4 id="supportArticlesCount"></h4>
                    <h5 id="supportShortHeading"></h5>
                    <p id="supportDescription"></p>
                </div>
            </div>
        </div>

    </div>
</section>