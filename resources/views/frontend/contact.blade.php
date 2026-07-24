@extends('layouts.app')

@section('content')
@include('frontend.partials.AllPageHeader')

<section class="s__container_custom">
    <div class="rntu-contact-wrapper">

        @include('frontend.partials.contactdetails')
    </div>
</section>

@endSection