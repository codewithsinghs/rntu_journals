<!DOCTYPE html>
<html>

<head>
    <title>CMS Admin</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- CDN -->
    <!-- Boostrp 5 css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />

    <link rel="stylesheet" href="{{ asset('assets/css/admin/CKEditor.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/mainstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/popup&tabs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/table.css') }}">


    @yield('head')
</head>

<body>

    <div class="main-container">

        @include('admin.partials.sidebar')

        <div class="main-content">

            @include('admin.partials.header')

            <div class="content-area">
                @yield('content')
            </div>

        </div>

    </div>

    @yield('modals')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    <script src="{{ asset('assets/js/admin/sidebar.js') }}"></script>
    <script src="{{ asset('assets/js/ckeditor.js') }}"></script>

</body>

</html>
