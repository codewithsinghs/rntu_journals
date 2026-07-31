@extends('layouts.app')

@section('content')


<section class="s__container_custom">
    <div class="rntu-contact-wrapper">

        @include('frontend.partials.contactdetails')
    </div>
</section>

@endSection
@section('scripts')
<script src="{{ asset('assets/js/frontend/contact.js') }}"></script>
@endsection