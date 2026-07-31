@php
$layout = request()->is('admin/*')
? 'layouts.admin'
: 'layouts.app';
@endphp

@extends($layout)

@section('title', '404 - Page Not Found')

@section('content')
<div class="container text-center py-5">
    <h1 class="display-1 fw-bold">401</h1>
    <h3>Unauthorized Access</h3>
    <p class="text-muted">
        Sorry, you are not authorized to access this page.
    </p>

</div>
@endsection