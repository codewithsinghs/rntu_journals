<section class="latest_journal_section">

    <div class="s__container_custom">

        <!-- TOP HEADING -->
        <div class="journal_top text-center">

            @if ($content && $content->latest_journal_title)
                <span class="journal_tag">
                    {{ $content->latest_journal_title }}
                </span>
            @endif

            @if ($content && $content->latest_journal_heading)
                <h2>
                    {{ $content->latest_journal_heading }}
                </h2>
            @endif


            @if ($content && $content->latest_journal_description)
                <p>
                    {!! $content->latest_journal_description !!}
                </p>
            @endif


        </div>

        <!-- MAIN CONTENT -->
        <div class="row gy-5">

            <!-- LEFT SIDE -->
            <div class="col-lg-6">

                <div class="journal_wrapper">

                    <div class="journal_heading">
                        <h3>Journals</h3>
                    </div>

                    @forelse (($journals ?? collect())->take(2) as $journal)
                        <div class="journal_card">

                            <div class="journal_img">
                                @if ($journal->cover_image)
                                    <img src="{{ Storage::url($journal->cover_image) }}" alt="{{ $journal->title }}">
                                @else
                                    <img src="{{ asset('assets/home_page/latest_1.png') }}" alt="{{ $journal->title }}">
                                @endif
                            </div>

                            <div class="journal_content">

                                <h4>{{ $journal->title }}</h4>

                                <ul>
                                    <li>E-ISSN : {{ $journal->e_issn }} P-ISSN : {{ $journal->p_issn }}</li>
                                    <li>Volume : {{ $journal->volume }} | Issue : {{ $journal->issue }}</li>
                                    <li>Latest Volume : {{ $journal->latest_volume }}</li>
                                    <li>Indexing & Impact Factor : {{ $journal->indexing_impact_factor }}</li>
                                </ul>

                            </div>

                        </div>
                    @empty
                        <div class="text-center py-4">No journals available.</div>
                    @endforelse

                </div>

            </div>

            <!-- RIGHT SIDE -->

            <div class="col-lg-6">

                <div class="issues_wrapper">

                    <!-- TOP -->
                    <div class="journal_heading">
                        <h3>Issues</h3>
                        <!-- <a href="currentissues.html">View All ↗</a> -->
                    </div>

                    <!-- FILTER BUTTONS -->
                    <div class="issue_tabs">
                        <button class="tab-btn active" onclick="openCity(event,'tab-latest')">Latest</button>
                        @foreach ($articlesByYear as $year => $articles)
                            <button class="tab-btn"
                                onclick="openCity(event,'tab-{{ $year }}')">{{ $year }}</button>
                        @endforeach
                    </div>

                    <!-- LATEST TAB -->
                    <div id="tab-latest" class="w3-container city" style="display:block">
                        @forelse ($latestArticles as $article)
                            <div class="issue_item">
                                <div class="issue_date">
                                    <h4>{{ \Carbon\Carbon::parse($article->created_at)->format('d') }}</h4>
                                    <span>{{ \Carbon\Carbon::parse($article->created_at)->format('M') }}</span>
                                </div>
                                <div class="issue_content">
                                    <h5>
                                        <a href="{{ url('/article/' . $article->uuid) }}" class="link_connect">
                                            {{ $article->manuscript_title }}
                                        </a>
                                    </h5>
                                    <p>{{ $article->full_name }}</p>
                                </div>
                            </div>
                        @empty
                            <p>No articles approved yet.</p>
                        @endforelse
                    </div>

                    <!-- YEAR TABS -->
                    @foreach ($articlesByYear as $year => $articles)
                        <div id="tab-{{ $year }}" class="w3-container city" style="display:none">
                            @foreach ($articles as $article)
                                <div class="issue_item">
                                    <div class="issue_date">
                                        <h4>{{ \Carbon\Carbon::parse($article->created_at)->format('d') }}</h4>
                                        <span>{{ \Carbon\Carbon::parse($article->created_at)->format('M') }}</span>
                                    </div>
                                    <div class="issue_content">
                                        <h5>
                                            <a href="{{ url('/article/' . $article->id) }}" class="link_connect">
                                                {{ $article->manuscript_title }}
                                            </a>
                                        </h5>
                                        <p>{{ $article->full_name }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                </div><!-- /.issues_wrapper -->

            </div><!-- /.col-lg-6 -->
        </div>

    </div>

    </div>

</section>
