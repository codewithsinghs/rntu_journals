@extends('layouts.app')

@section('content')

    <div id="currentIssuesApp"
         data-issue-uuid="{{ $issueUuid }}"
         data-api-base="{{ url('/api/public/issues') }}"
         data-articles-route-base="{{ url('/article') }}">

        <div class="current-banner">

            <div class="current-icon">
                <i class="fa-solid fa-file-lines"></i>
            </div>

            <div class="current-content">
                <h1 id="issueHeading">Loading...</h1>

                <p>
                    <i class="fa-regular fa-calendar"></i>
                    Published: <span id="issuePublishedDate">-</span>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    DOI :
                    <a href="#">https://doi.org/10.54392/ijrmt263</a>
                </p>
            </div>

        </div>

        <div class="s__container_custom">

            <span class="journal_tag">CURRENTS</span>

            <h2 class="current-title">Articles</h2>

            <!-- Loading state -->
            <div id="articlesLoading" class="text-center py-4">Loading articles...</div>

            <!-- Empty state -->
            <div id="articlesEmpty" class="text-center py-4 d-none">No published articles yet in this issue.</div>

            <div class="articles-grid" id="articlesGrid"></div>

            <div class="pagination-wrap d-flex justify-content-center py-4" id="paginationWrapper"></div>

        </div>

    </div>

    <script src="{{ asset('assets/js/current-issues.js') }}"></script>

@endsection