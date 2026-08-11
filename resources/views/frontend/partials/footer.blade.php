<footer class="D_footer">

    <div class="inner_footer">

        {{-- Col 1: About --}}
        <div class="coll_f">
            <div class="heading">RNTU Journals</div>
            <p id="footerAboutDescription"></p>
        </div>

        {{-- Col 2: Useful Links --}}
        <div class="coll_f">
            <div class="heading">Useful Links</div>
            <ul id="footerUsefulLinks">
                <li>Loading...</li>
            </ul>
        </div>

        {{-- Col 3: Journal Policies --}}
        <div class="coll_f">
            <div class="heading">Journal Policies</div>
            <ul id="footerJournalPolicies">
                <li>Loading...</li>
            </ul>
        </div>

        {{-- Col 4: Contact --}}
        <div class="coll_f">
            <div class="heading">Contact Us</div>
            <ul style="padding:0;" id="footerContact"></ul>
        </div>

    </div>

    {{-- ===== BOTTOM BAR ===== --}}
    <div class="out_footer">

        <!-- <div class="visitors">
            <img src="{{ asset('/storage/home_page/visitor.png') }}" alt="Visitors">
            <p>Website Visitor : <span id="visitor-count">12563</span></p>
        </div> -->

        <div class="visitors">
            <img src="{{ asset('/storage/home_page/visitor.png') }}" alt="Visitors">
            <p>Website Visitor : <span id="visitor-count">0</span></p>
        </div>

        <p>Copyright {{ date('Y') }} <span id="footerWebsiteName">RNTU Journal</span>. All Rights Reserved.</p>

        <div class="img_f" id="footerSocialLinks"></div>

        {{-- Bottom Links --}}
        <ul id="footerBottomLinks">
            <li><a href="/privacy-policy" style="color:inherit;text-decoration:none;">Privacy Policy</a></li>
            <li><a href="/terms-of-services" style="color:inherit;text-decoration:none;">Terms of Services</a></li>
            <li><a href="/disclaimer" style="color:inherit;text-decoration:none;">Disclaimer</a></li>
        </ul>

    </div>

</footer>