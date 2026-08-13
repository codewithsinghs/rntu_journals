{{-- resources/views/frontend/archives.blade.php --}}

<div class="s__container_custom" id="archivesApp"
 data-journal="{{ $journal->slug }}"
     data-journal-id="{{ $journal->id }}"
     data-api-base="{{ url('/api/public/journals') }}">

    <!-- Breadcrumb -->
    <div class="arc-breadcrumb">
        <a href="{{ url('/') }}">Home /</a>
        <a href="{{ route('journal-details', $journal) }}">{{ $journal->title }} /</a>
        <span>Archives</span>
    </div>

    <!-- Header -->
    <div class="arc-header">

        <div>
            <h1 class="arc-title">Archives</h1>
            <p class="arc-subtitle">
                Browse all volumes and issues of the journals
            </p>
        </div>

        <div class="arc-illustration">
            <img src="{{ asset('storage/archives_icom.png') }}">
        </div>

    </div>

    <!-- Filters -->
    <div class="arc-filter-row">

        <div class="arc-filter-box">
            <div class="arc-filter-label">View by</div>
            <div class="arc-filter-content">
                <button class="arc-view-btn active">By Volume</button>
                <button class="arc-view-btn">By Year</button>
            </div>
        </div>

        <div class="arc-filter-box">
            <div class="arc-filter-label">Sort by</div>
            <div class="arc-filter-content">
                <select id="sortSelect" class="arc-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>
        </div>

        <div class="arc-search-box">
            <input type="text" id="searchInput" placeholder="Search Volumes">
            <button>
                <img src="{{ asset('storage/search_icon.png') }}" alt="">
            </button>
        </div>

    </div>

    <!-- Loading state -->
    <div id="archivesLoading" class="text-center py-4">Loading archives...</div>

    <!-- Empty state -->
    <div id="archivesEmpty" class="text-center py-4 d-none">No issues published yet.</div>

    <!-- Year blocks (filled by JS) -->
    <div id="yearBlocksContainer"></div>

</div>

