@extends('layouts.app')

@section('content')
<<<<<<< HEAD
    @include('frontend.partials.AllPageHeader')

    <section class="s__container_custom">
        @include('frontend.partials.journal-detail')
    </section>
@endSection
=======


<section class="s__container_custom">    
          @include('frontend.partials.journal-detail')
</section>

@endSection

@section('scripts')
<script src="{{ asset('assets/js/frontend/journal-detail.js') }}"></script>
@endsection
>>>>>>> main
