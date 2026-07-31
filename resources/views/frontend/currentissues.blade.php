@extends('layouts.app')

@section('content')


<section class="s__container_custom">    
          @include('frontend.partials.currentissues')
</section>

@endSection
@section('scripts')
<script src="{{ asset('assets/js/frontend/currentissues.js') }}"></script>
@endsection