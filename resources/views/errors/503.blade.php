@php
$layout = request()->is('admin/*')
? 'layouts.admin'
: 'layouts.app';
@endphp

@extends($layout)

@section('title', '404 - Page Not Found')

@section('content')
<div class="container text-center py-5">
    <h1 class="display-1 fw-bold">503</h1>
    <h3>Service Unavailable</h3>
    <p class="text-muted">
        Sorry, our website is temporarily unavailable due to maintenance.
        Please try again in a few minutes.
    </p>
</div>
@endsection