   <section class="why-section">
        <div class="container s__container_custom">

            <!-- Left Image -->
            <div class="why-image">
                @if($aboutContent->why_section_image)
                <img src="{{ asset('storage/' . $aboutContent->why_section_image) }}" alt="Researchers">
                @endif
            </div>

            <!-- Right Content -->
            <div class="why-content">
            
                 @if($aboutContent->why_badge)
                <span class="journal_tag">
                    {{ $aboutContent->why_badge }}
                </span>
                @endif
                <h2>{{ $aboutContent->why_heading }}</h2>

                <div class="why-content-box">

                    <p>
                       {!! $aboutContent->why_description_1 !!}
                    </p>

                    <p>
                       {!! $aboutContent->why_description_2 !!}
                    </p>

                    <!-- <a href="#" class="publish-btn">
                        Publish Now ↗
                    </a> -->

                </div>

            </div>

        </div>
    </section>
