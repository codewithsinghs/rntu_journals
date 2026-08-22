<!DOCTYPE html>
<html>

<head>

    <title>RNTU Journal</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Boostrp 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 7 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin/CKEditor.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/mainstyle.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/admin/submitarticle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/popup&tabs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/table.css') }}">

    {{-- <link rel="stylesheet" href="{{ asset('assets/css/admin/style_hidden.css') }}"> --}}

    @yield('head')
</head>

<body>

    <div class="main-container">

        <!-- Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Main Content -->
        <div class="main-content">

            <!-- Header -->
            @include('admin.partials.header')

            @yield('content')

        </div>

    </div>

    @yield('modals')

    <!--  bootstrap@5.3.3 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.all.min.js"></script>
    <!-- Scripts -->
    <script src="{{ asset('assets/js/admin/main.js') }}"></script>
    <script src="{{ asset('assets/js/ckeditor.js') }}"></script>

</body>

</html>
