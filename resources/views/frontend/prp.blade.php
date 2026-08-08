@extends('layouts.app')

@section('content')


 <section class="s__container_custom">
    
 @include('frontend.partials.prp')

</section>

@endSection


@section('scripts')
    <script src="{{ asset('assets/js/frontend/prp.js') }}"></script>
@endsection
