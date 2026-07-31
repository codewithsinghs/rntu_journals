@php
$layout = request()->is('admin/*')
? 'layouts.admin'
: 'layouts.app';
@endphp

@extends($layout)

@section('title', '404 - Page Not Found')

@section('content')
<div class="container text-center py-5">

    <h1 style="font-size: 80px;">404</h1>
    <h2>Page Not Found</h2>
    <p>
        Sorry, the page you are looking for does not exist or has been moved.
    </p>

</div>
@endsection