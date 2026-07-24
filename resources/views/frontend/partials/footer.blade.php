<footer class="D_footer">

    <div class="inner_footer">

        {{-- Col 1: About --}}
        <div class="coll_f">
            <div class="heading">RNTU Journals</div>
            <p>{!! $content->footer_about_description ?? '' !!}</p>
        </div>

        {{-- Col 2: Useful Links --}}
        <div class="coll_f">
            <div class="heading">Useful Links</div>
            <ul>
                @forelse($usefulLinks as $link)
                    <li>
                        <a href="{{ $link->url }}"
                           target="{{ $link->target ?? '_self' }}"
                           style="color:inherit; text-decoration:none;">
                            {{ $link->label }}
                        </a>
                    </li>
                @empty
                    <li>No links available.</li>
                @endforelse
            </ul>
        </div>

        {{-- Col 3: Journal Policies --}}
        <div class="coll_f">
            <div class="heading">Journal Policies</div>
            <ul>
                @forelse($journalPolicies as $link)
                    <li>
                        <a href="{{ $link->url }}"
                           target="{{ $link->target ?? '_self' }}"
                           style="color:inherit; text-decoration:none;">
                            {{ $link->label }}
                        </a>
                    </li>
                @empty
                    <li>No policies available.</li>
                @endforelse
            </ul>
        </div>

        {{-- Col 4: Contact --}}
        <div class="coll_f">
            <div class="heading">Contact Us</div>
            <ul style="padding:0;">

                @if($settings?->address)
                <ol style="padding:0;">
                    <div class="contact_item">
                        <div class="contact_icon">
                            <img src="{{ asset('/storage/home_page/footer_address.png') }}" alt="Address">
                        </div>
                        <div class="contact_text">
                            <h4>Address</h4>
                            <p>{{ $settings->address }}</p>
                        </div>
                    </div>
                </ol>
                @endif

                @if($settings?->email)
                <ol style="padding:0;">
                    <div class="contact_item">
                        <div class="contact_icon">
                            <img src="{{ asset('/storage/home_page/footer_email.png') }}" alt="Email">
                        </div>
                        <div class="contact_text">
                            <h4>Email</h4>
                            <a href="mailto:{{ $settings->email }}" style="color:inherit;">
                                {{ $settings->email }}
                            </a>
                        </div>
                    </div>
                </ol>
                @endif

                @if($settings?->phone)
                <ol style="padding:0;">
                    <div class="contact_item">
                        <div class="contact_icon">
                            <img src="{{ asset('/storage/home_page/footer_phone.png') }}" alt="Phone">
                        </div>
                        <div class="contact_text">
                            <h4>Phone</h4>
                            <a href="tel:{{ $settings->phone }}" style="color:inherit;">
                                {{ $settings->phone }}
                            </a>
                        </div>
                    </div>
                </ol>
                @endif

                @if($settings?->website_url)
                <ol style="padding:0;">
                    <div class="contact_item">
                        <div class="contact_icon">
                            <img src="{{ asset('/storage/home_page/footer_website.png') }}" alt="Website">
                        </div>
                        <div class="contact_text">
                            <h4>Website</h4>
                            <a href="{{ $settings->website_url }}" style="color:inherit;" target="_blank">
                                {{ $settings->website_name ?? 'RNTU Journals' }}
                            </a>
                        </div>
                    </div>
                </ol>
                @endif

            </ul>
        </div>

    </div>

    {{-- ===== BOTTOM BAR ===== --}}
    <div class="out_footer">

        <div class="visitors">
            <img src="{{ asset('/storage/home_page/visitor.png') }}" alt="Visitors">
            <p>Website Visitor : <span id="visitor-count">12563</span></p>
        </div>

        <p>Copyright {{ date('Y') }} <span>{{ $settings->website_name ?? 'RNTU Journal' }}</span>. All Rights Reserved.</p>

        <div class="img_f">

            @if($settings?->facebook_url)
            <a href="{{ $settings->facebook_url }}" aria-label="Facebook" target="_blank" rel="noopener">
                <span><i class="fa-brands fa-facebook-f"></i></span>
            </a>
            @endif

            @if($settings?->instagram_url)
            <a href="{{ $settings->instagram_url }}" aria-label="Instagram" target="_blank" rel="noopener">
                <span><i class="fa-brands fa-instagram"></i></span>
            </a>
            @endif

            @if($settings?->twitter_url)
            <a href="{{ $settings->twitter_url }}" aria-label="Twitter" target="_blank" rel="noopener">
                <span><i class="fa-brands fa-twitter"></i></span>
            </a>
            @endif

            @if($settings?->youtube_url)
            <a href="{{ $settings->youtube_url }}" aria-label="YouTube" target="_blank" rel="noopener">
                <span><i class="fa-brands fa-youtube"></i></span>
            </a>
            @endif

            @if($settings?->linkedin_url)
            <a href="{{ $settings->linkedin_url }}" aria-label="LinkedIn" target="_blank" rel="noopener">
                <span><i class="fa-brands fa-linkedin-in"></i></span>
            </a>
            @endif

        </div>

        {{-- Bottom Links --}}
        <ul>
            @forelse($bottomLinks as $link)
                <li>
                    <a href="{{ $link->url }}"
                       target="{{ $link->target ?? '_self' }}"
                       style="color:inherit; text-decoration:none;">
                        {{ $link->label }}
                    </a>
                </li>
            @empty
                <li><a href="/privacy-policy" style="color:inherit;text-decoration:none;">Privacy Policy</a></li>
                <li><a href="/terms-of-services" style="color:inherit;text-decoration:none;">Terms of Services</a></li>
                <li><a href="/disclaimer" style="color:inherit;text-decoration:none;">Disclaimer</a></li>
            @endforelse
        </ul>

    </div>

</footer>