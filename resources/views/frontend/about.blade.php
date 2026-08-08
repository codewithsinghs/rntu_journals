@extends('layouts.app')

@section('content')
<<<<<<< HEAD
    @include('frontend.partials.AllPageHeader')
    @include('frontend.partials.aboutjournal')
    @include('frontend.partials.aboutwhysection')
@endSection
=======


@include('frontend.partials.aboutjournal')
@include('frontend.partials.aboutwhysection')

@endsection


@section('scripts')
<script src="{{ asset('assets/js/frontend/about.js') }}"></script>
@endsection
>>>>>>> main
