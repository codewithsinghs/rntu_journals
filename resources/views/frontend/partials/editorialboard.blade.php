@php
    // $editorialBoard and $editorialBoardRoleOrder are injected by EditorialBoardComposer
    $get = fn($role) => $editorialBoard->get($role, collect());
@endphp

<div class="s__container_custom">

    @if($editorialBoard->isEmpty())

        <div class="text-center py-4">
            No editorial board members available.
        </div>

    @else

        {{-- Editor-in-Chief --}}
        @if($get('Editor-in-Chief')->isNotEmpty())
            <section class="editorial-section">
                <div class="section-title">Editor-in-Chief</div>

                @foreach($get('Editor-in-Chief') as $member)
                    <div class="editor-card single-card">

                        @if($member->profile_image)
                            <img src="{{ Storage::url($member->profile_image) }}"
                                 alt="{{ $member->name }}"
                                 class="editor-photo">
                        @endif

                        <h3>{{ $member->name }}</h3>
                        @if($member->designation)<p>{{ $member->designation }}</p>@endif
                        @if($member->department)<p>{{ $member->department }}</p>@endif
                        @if($member->institute)<p>{{ $member->institute }}</p>@endif
                        @if($member->university_or_org)<p>{{ $member->university_or_org }}</p>@endif
                        @if($member->city)<p>{{ $member->city }}</p>@endif
                        @if($member->email)<p>Email: {{ $member->email }}</p>@endif

                        <div class="links">
                            @if($member->orcid_url)<a href="{{ $member->orcid_url }}" target="_blank" rel="noopener">ORCID</a>@endif
                            @if($member->scopus_url)<a href="{{ $member->scopus_url }}" target="_blank" rel="noopener">Scopus</a>@endif
                            @if($member->web_of_science_url)<a href="{{ $member->web_of_science_url }}" target="_blank" rel="noopener">Web of Science</a>@endif
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- Managing Editor & Executive Editor (side-by-side) --}}
        @if($get('Managing Editor')->isNotEmpty() || $get('Executive Editor')->isNotEmpty())
            <section class="grid-two">

                @foreach(['Managing Editor', 'Executive Editor'] as $role)
                    @if($get($role)->isNotEmpty())
                        <div class="editorial-section">
                            <div class="section-title">{{ $role }}</div>

                            @foreach($get($role) as $member)
                                <div class="editor-card">

                                    @if($member->profile_image)
                                        <img src="{{ Storage::url($member->profile_image) }}"
                                             alt="{{ $member->name }}"
                                             class="editor-photo">
                                    @endif

                                    <h3>{{ $member->name }}</h3>
                                    @if($member->designation)<p>{{ $member->designation }}</p>@endif
                                    @if($member->department)<p>{{ $member->department }}</p>@endif
                                    @if($member->institute)<p>{{ $member->institute }}</p>@endif
                                    @if($member->university_or_org)<p>{{ $member->university_or_org }}</p>@endif
                                    @if($member->city)<p>{{ $member->city }}</p>@endif
                                    @if($member->email)<p>Email: {{ $member->email }}</p>@endif

                                    <div class="links">
                                        @if($member->scopus_url)<a href="{{ $member->scopus_url }}" target="_blank" rel="noopener">Scopus</a>@endif
                                        @if($member->web_of_science_url)<a href="{{ $member->web_of_science_url }}" target="_blank" rel="noopener">Web of Science</a>@endif
                                        @if($member->orcid_url)<a href="{{ $member->orcid_url }}" target="_blank" rel="noopener">ORCID</a>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach

            </section>
        @endif

        {{-- Editors / Associate Editors / Members (3-up grids) --}}
        @foreach(['Editors', 'Associate Editors', 'Members'] as $role)
            @if($get($role)->isNotEmpty())
                <section class="editorial-section">
                    <div class="section-title">{{ $role }}</div>

                    <div class="grid-three">
                        @foreach($get($role) as $member)
                            <div class="editor-card-border">

                                @if($member->profile_image)
                                    <img src="{{ Storage::url($member->profile_image) }}"
                                         alt="{{ $member->name }}"
                                         class="editor-photo">
                                @endif

                                <h3>{{ $member->name }}</h3>
                                @if($member->designation)<p>{{ $member->designation }}</p>@endif
                                @if($member->department)<p>{{ $member->department }}</p>@endif
                                @if($member->institute)<p>{{ $member->institute }}</p>@endif
                                @if($member->university_or_org)<p>{{ $member->university_or_org }}</p>@endif
                                @if($member->city)<p>{{ $member->city }}</p>@endif

                                <div class="links">
                                    @if($member->scopus_url)<a href="{{ $member->scopus_url }}" target="_blank" rel="noopener">Scopus</a>@endif
                                    @if($member->web_of_science_url)<a href="{{ $member->web_of_science_url }}" target="_blank" rel="noopener">Web of Science</a>@endif
                                    @if($member->orcid_url)<a href="{{ $member->orcid_url }}" target="_blank" rel="noopener">ORCID</a>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

    @endif

</div> {{-- /s__container_custom --}}