<!DOCTYPE html>
<html>

<head>
    <title>RNTU Journals</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Essentials -->
    <title>{{ $meta->title }}</title>
    <meta name="description" content="{{ $meta->description }}">
    <meta name="keywords" content="{{ is_array($meta->keywords) ? implode(',', $meta->keywords) : $meta->keywords }}">

    <meta name="robots" content="index, follow">

    <!-- Canonical -->
    <link rel="canonical" href="{{ url()->current() ?? 'https://rntujournals.aisect.org/' }}">
<<<<<<< HEAD

    <!-- CSS -->
=======
>>>>>>> main
    <link rel="stylesheet" href="{{ asset('assets/css/frontend/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/frontend/style.css') }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    @if (request()->is('/'))
    <div class=" hero_section">

        <div class=" hero_container">

            @include('frontend.partials.header')

            @include('frontend.partials.journals')
            @include('frontend.partials.announcements')


        </div>
    </div>
    @endif

    @if (!request()->is('/'))
    @include('frontend.partials.allpageheader')
    @endif

    <main>
        @yield('content')
    </main>

    @include('frontend.partials.footer')

</body>
 <script src="{{ asset('assets/js/frontend/menu.js') }}"></script>
<script src="{{ asset('assets/js/frontend/home.js') }}"></script>
<script src="{{ asset('assets/js/frontend/footer.js') }}"></script>
@yield('scripts')
</html>
