{{-- resources/views/frontend/journal-detail.blade.php --}}

<!-- JOURNAL SECTION -->
<div class="s__container_custom mt-5" id="journalDetailApp" data-journal-id="{{ $journalId }}" data-api-base="{{ url('/api/public/journals') }}" data-storage-base="{{ Storage::url('') }}">

    <!-- TOP HEADING -->
    <div class="journal_top text-center mb-2">
        <h2>Journal Description</h2>
    </div>

    <!-- Loading state -->
    <div id="journalLoading" class="text-center py-4">Loading journal...</div>

    <!-- Error state -->
    <div id="journalError" class="text-center py-4 text-danger d-none">Journal not found.</div>

    <!-- Journal content (filled by JS) -->
    <div class="journal-card d-none" id="journalCard" style="align-items: center;">

        <div class="journal-image">
            <img id="journalCoverImage" src="" alt="">
        </div>

        <div class="journal-content">

            <p class="text_wrap_3" id="journalDescription"></p>

            <div class="fields-box">
                <div class="fields-grid" id="journalFieldsGrid">
                    <!-- fields injected dynamically -->
                </div>
            </div>

        </div>

    </div>

    <!-- Aim and Scope -->
    <section class="editorial-section s__container_custom mt-5 d-none" id="aimScopeSection">
        <div class="section-title" id="aimScopeTitle">Aim and Scope</div>
        <div class="editor-card single-card">
            <p style="line-height: 1.5;" id="aimScopeContent"></p>
        </div>
    </section>

</div>

<!-- Articles -->
<div class="s__container_custom mt-5" id="current_issues">

    <div class="text-center">
        <span class="journal_tag">Article</span>
    </div>

    <h2 class="current-title text-center">Latest & Most Viewed</h2>

    <!-- Loading state -->
    <div id="articlesLoading" class="text-center py-4">Loading articles...</div>

    <!-- Articles Grid -->
    <div class="articles-grid" id="articlesGrid"></div>

    <!-- Empty state -->
    <div class="text-center py-4 d-none" id="articlesEmpty">No articles available for this journal yet.</div>

    <!-- Pagination -->
    <div class="pagination-wrapper mt-4 d-flex justify-content-center" id="paginationWrapper"></div>

</div>
