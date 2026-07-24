<div class="journal-wrapper">

    @forelse($journals->take(2) as $journal)

    <div class="journal-card">

        {{-- Cover Image --}}
        <div class="journal-image">
            @if($journal->cover_image)
                <img src="{{ Storage::url($journal->cover_image) }}"
                     alt="{{ $journal->title }}">
            @else
                <div class="journal-cover-placeholder"></div>
            @endif
        </div>

        {{-- Body --}}
        <div class="journal-content">

            <h2 style="font-size:24px;">{{ $journal->title }}</h2>

            @if($journal->description)
                <p>{!! $journal->description !!}</p>
            @endif

            {{-- Fields Covered --}}
            @php
                $fields = collect($journal->fields_covered ?? [])->take(6);
            @endphp

            @if($fields->isNotEmpty())
                <div class="fields-box">
                    <div class="fields-title">
                        {{ $journal->title_2 ?: 'Fields Covered' }}
                    </div>

                    <div class="fields-grid">
                        @foreach($fields as $i => $field)
                            <div class="field-item">
                                <span class="number">{{ $i + 1 }}</span>
                                <span>{{ $field }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="journal-buttons">

                <a href="{{ $journal->view_all_issues_link ?? '#' }}"
                   class="primary-btn">
                    {{ $journal->view_all_issues_label ?: 'View All Issues' }}
                </a>

                @if($journal->explore_journals_link)
                    <!-- <a href="{{ $journal->explore_journals_link }}" -->
                     <a href="{{ url($journal->explore_journals_link) }}"
                       class="secondary-link">

                        <div class="icon-circle">
                            <img src="{{ asset('storage/home_page/explore_icon.png') }}"
                                 alt="Explore"
                                 onerror="this.style.display='none';">
                        </div>

                        {{ $journal->explore_journals_label ?: 'Explore Journals' }}

                    </a>
                @endif

            </div>

        </div> {{-- /journal-content --}}

    </div> {{-- /journal-card --}}

    @empty

        <div class="text-center py-4">
            No journals available.
        </div>

    @endforelse

</div> {{-- /journal-wrapper --}}