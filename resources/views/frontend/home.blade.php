@extends('layouts.app')

@section('content')
    <!-- Hero Section -->

    <div class=" hero_section">

        <div class=" hero_container">

                @include('frontend.partials.header')
                @include('frontend.partials.journals')
                @include('frontend.partials.announcements')
                

        </div>

    </div>
    
@include('frontend.partials.aimandscope')
@include('frontend.partials.latestjournal')

@endSection