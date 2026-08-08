@extends('layouts.app')

@section('content')
<<<<<<< HEAD
    @include('frontend.partials.AllPageHeader')

    <section class="s__container_custom">
        @include('frontend.partials.currentissues')
    </section>
    
@endSection
=======


<section class="s__container_custom">    
          @include('frontend.partials.currentissues')
</section>

@endSection
@section('scripts')
<script src="{{ asset('assets/js/frontend/currentissues.js') }}"></script>
@endsection
>>>>>>> main
