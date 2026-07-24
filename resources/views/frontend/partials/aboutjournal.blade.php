   <section class="journal-section">
        <div class="container s__container_custom">

            <!-- Left Content -->
            <div class="about_journal">
                @if($aboutContent->about_badge)
                <span class="journal_tag">{{ $aboutContent->about_badge }}</span>
                 @endif

                <h2>{{ $aboutContent->about_heading }}</h2>

                <p>{!! $aboutContent->about_description_1 !!}</p>

                <p>{!! $aboutContent->about_description_2 !!}</p>


            </div>

            <!-- Right Images -->
            <div class="journal-images">
                @if($aboutContent->about_section_img1)
                <img src="{{ asset('storage/' . $aboutContent->about_section_img1) }}" alt="Journal Cover">
                @endif
                @if($aboutContent->about_section_img2)
                <img src="{{ asset('storage/' . $aboutContent->about_section_img2) }}" alt="Journal Cover">
                @endif
            </div>

        </div>
    </section>