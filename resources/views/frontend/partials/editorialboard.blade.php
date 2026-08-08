{{-- resources/views/frontend/editorial.blade.php --}}

<div class="s__container_custom" id="editorialBoardApp"
     data-journal-param="{{ $journalParam }}"
     data-api-base="{{ url('/api/public/editorial-board') }}">


    <div id="boardLoading" class="text-center py-4">Loading editorial board...</div>


    <div id="boardEmpty" class="text-center py-4 d-none">
        No editorial board members available.
    </div>


    <div id="boardContent"></div>

</div>

<script src="{{ asset('assets/js/editorial-board.js') }}"></script>