<!DOCTYPE html>
<html>

<head>
    <title>@yield('title', 'RNTU Journals')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Essentials -->
    <title>{{ $meta->title }}</title>
    <meta name="description" content="{{ $meta->description }}">
    <meta name="keywords" content="{{ is_array($meta->keywords) ? implode(',', $meta->keywords) : $meta->keywords }}">

    <!-- Robots -->
    <meta name="robots" content="index, follow">

    <!-- Canonical -->
    <link rel="canonical" href="{{ url()->current() ?? 'https://rntujournals.aisect.org/' }}">


    <!-- Citation Metadata -->
    {{-- <meta name="citation_title" content="{{ optional($article->title ?? 'Default Article Title') }}">
    <meta name="citation_author" content="{{ optional($article->author ?? 'Unknown Author') }}">
    <meta name="citation_author_institution" content="{{ optional($article->institution ?? 'RNTU') }}">
    <meta name="citation_publication_date" content="{{ optional($article->published_at->format('Y/m/d') ?? '2026/07/24') }}">
    <meta name="citation_journal_title" content="RNTU Journal of Science">
    <meta name="citation_volume" content="{{ optional($article->volume ?? '1' }}">
    <meta name="citation_issue" content="{{ optional($article->issue ?? '1' }}">
    <meta name="citation_firstpage" content="{{ optional($article->first_page ?? '1' }}">
    <meta name="citation_lastpage" content="{{ optional($article->last_page ?? '10' }}">
    <meta name="citation_doi" content="{{ optional($article->doi ?? '10.1234/rntu.default' }}">
    <meta name="citation_issn" content="1234-5678">
    <meta name="citation_pdf_url"        content="{{ optional($article->pdf ? asset('storage/pdfs/' . $article->pdf) : url('/default.pdf') }}"> --}}

    <!-- Open Graph (Social Sharing) -->
    {{-- <meta property="og:title" content="{{ optional($article->title ?? 'RNTU Journal Article') }}">
    <meta property="og:description" content="{{ optional($article->abstract ?? 'Read the latest RNTU research article') }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}"> --}}


    {{-- <meta property="og:image"
        content="{{ optional($article->cover_image ? asset('storage/images/' . $article->cover_image)) : asset('images/default-cover.jpg') }}"> --}}

    <!-- Twitter Cards -->
    {{-- <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ optional($article->title ?? 'RNTU Journal Article') }}">
    <meta name="twitter:description" content="{{ optional($article->abstract ?? 'Read the latest RNTU research article') }}"> --}}
    {{-- <meta name="twitter:image"
        content="{{ optional($article->cover_image ? asset('storage/images/' . $article->cover_image) : asset('images/default-cover.jpg') }}"> --}}

    <link rel="stylesheet" href="{{ asset('assets/css/frontend/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/frontend/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    @include('frontend.partials.topbar')

    <main>
        @yield('content')
    </main>

    @include('frontend.partials.footer')

    {{-- Global Scripts --}}
    <script src="{{ asset('assets/js/frontend/main.js') }}"></script>

</body>

</html>
