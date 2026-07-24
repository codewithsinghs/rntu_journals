<div class="s__container_custom">

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

            <div class="arc-filter-label">
                View by
            </div>

            <div class="arc-filter-content">

                <button class="arc-view-btn active">
                    By Volume
                </button>

                <button class="arc-view-btn">
                    By Year
                </button>

            </div>

        </div>

        <div class="arc-filter-box">

            <div class="arc-filter-label">
                Sort by
            </div>

            <div class="arc-filter-content">

                <select id="sortSelect" class="arc-select">
                    <option value="newest">
                        Newest First
                    </option>

                    <option value="oldest">
                        Oldest First
                    </option>
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

    {{-- YEAR BLOCKS --}}
    @forelse ($issues as $year => $issuesInYear)

        <div class="arc-year-block">

            <div class="arc-year-header">

                <span class="arc-toggle">▼</span>

                <span class="arc-year">
                    {{ $year }}
                </span>

                <span class="arc-badge">
                    {{ $issuesInYear->count() }} {{ Str::plural('Issue', $issuesInYear->count()) }}
                </span>

            </div>

            <div class="arc-year-content">

                <div class="arc-grid">

                    @foreach ($issuesInYear as $issue)
                        <a href="{{ route('current-issues', $issue->uuid) }}" class="arc-card"
                            data-title="Volume {{ $issue->volume->volume ?? '-' }} Issue {{ $issue->issue }} Year {{ $issue->year }}"
                            data-date="{{ $issue->created_at->format('Y-m-d') }}">

                            <div class="arc-card-icon"><img src="{{ asset('storage/book_icon.png') }}"></div>

                            <div class="arc-card-info">
                                <h4>Volume {{ $issue->volume->volume ?? '-' }}, Issue {{ $issue->issue }}, Year
                                    {{ $issue->year }}</h4>
                                <p>Published:
                                    {{ $issue->published_date ? \Carbon\Carbon::parse($issue->published_date)->format('d M Y') : '-' }}
                                </p>
                            </div>

                            <div class="arc-arrow">›</div>

                        </a>
                    @endforeach

                </div>

            </div>

        </div>

    @empty

        <div class="text-center py-4">No issues published yet.</div>

    @endforelse

</div>





<script>
    document.querySelectorAll('.arc-year-header')
        .forEach(header => {

            header.addEventListener('click', () => {

                const content =
                    header.nextElementSibling;

                const icon =
                    header.querySelector('.arc-toggle');

                content.classList.toggle('arc-hide');

                icon.innerHTML =
                    content.classList.contains('arc-hide') ?
                    '▶' :
                    '▼';

            });

        });


    document.getElementById('searchInput')
        .addEventListener('keyup', function() {

            let value =
                this.value.toLowerCase();

            document
                .querySelectorAll('.arc-card')
                .forEach(card => {

                    let title =
                        card.dataset.title.toLowerCase();

                    card.style.display =
                        title.includes(value) ?
                        'flex' :
                        'none';

                });

        });


    document.getElementById('sortSelect')
        .addEventListener('change', function() {

            document
                .querySelectorAll('.arc-grid')
                .forEach(grid => {

                    let cards = [...grid.querySelectorAll('.arc-card')];

                    cards.sort((a, b) => {

                        let d1 =
                            new Date(a.dataset.date);

                        let d2 =
                            new Date(b.dataset.date);

                        return this.value === 'newest' ?
                            d2 - d1 :
                            d1 - d2;
                    });

                    cards.forEach(card =>
                        grid.appendChild(card)
                    );

                });

        });
</script>
