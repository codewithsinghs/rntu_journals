@extends('layouts.app')

@section('content')
    <section class="dynamic-page">
        <div class="container py-5">
            <div id="pageLoading" class="text-center py-5">Loading…</div>
            <div id="pageError" class="text-center py-5" style="display:none;">Page not found.</div>

            <article id="pageContent" style="display:none;">
                <h1 id="pgTitle" class="mb-4"></h1>
                <div id="pgBody"></div>
            </article>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/frontend/page.js') }}" id="pageScript" data-slug="{{ $slug }}"></script>
@endsection