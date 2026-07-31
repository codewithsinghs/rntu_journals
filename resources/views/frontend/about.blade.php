@extends('layouts.app')

@section('content')


@include('frontend.partials.aboutjournal')
@include('frontend.partials.aboutwhysection')

@endsection


@section('scripts')
<script src="{{ asset('assets/js/frontend/about.js') }}"></script>
@endsection