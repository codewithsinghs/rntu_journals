<div class="announcement-bar">

    <div class="announcement-heading_a">
        <img src="{{ asset('/storage/home_page/annu_icon.png') }}">
        &nbsp; Announcements
    </div>

    <div class="announcement-content">

        {{-- First Set --}}
        @forelse($announcements as $announcement)
            <div class="announcement-item">
                <a href="{{ $announcement->url }}" target="_blank">
                    📢 {{ $announcement->name }}
                </a>
            </div>
        @empty
            <div class="announcement-item">
                No announcements available.
            </div>
        @endforelse

        {{-- Duplicate Set --}}
        @foreach ($announcements as $announcement)
            <div class="announcement-item">
                <a href="{{ $announcement->url }}" target="_blank">
                    📢 {{ $announcement->name }}
                </a>
            </div>
        @endforeach

    </div>

</div>
