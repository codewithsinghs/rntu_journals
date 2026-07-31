@extends('layouts.admin')

@section('content')
    {{-- View By ID Hidden --}}
    <div class="d-none" id="saShowPage" data-id="{{ $id }}">
        <div id="saShowSubtitle"></div>
    </div>

    {{-- Html Start --}}
    <section class="inner_p">
        <div class="content_top_wrapper">
            <div class="p_cards">

                {{-- Heading --}}
                <div class="heading">
                    Article Details
                </div>

                <div id="saShowBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/admin/showarticles.js') }}"></script>
@endsection
