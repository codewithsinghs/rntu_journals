@extends('layouts.app')

@section('content')


<section class="s__container_custom">    
          @include('frontend.partials.articles')
</section>

@endSection

@section('scripts')
<script src="{{ asset('assets/js/frontend/articles.js') }}"></script>
@endsection