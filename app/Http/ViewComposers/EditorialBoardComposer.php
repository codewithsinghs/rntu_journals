<?php

namespace App\Http\ViewComposers;

use App\Models\EditorialBoard;
use App\Models\Journal;
use Illuminate\View\View;
use Illuminate\Support\Facades\Request;

class EditorialBoardComposer
{
    public function compose(View $view)
    {
        // Get {journal} from the current route (slug or id)
        $journalParam = Request::route('journal');

        $journal = Journal::where('slug', $journalParam)
            ->orWhere('id', $journalParam)
            ->first();

        $members = EditorialBoard::where('is_active', true)
            ->when($journal, function ($query) use ($journal) {
                $query->where('journal_id', $journal->id);
            })
            ->orderBy('sequence')
            ->orderBy('name')
            ->get()
            ->groupBy('role');

        // Fixed display order — falls back gracefully if a role has no members
        $roleOrder = [
            'Editor-in-Chief',
            'Managing Editor',
            'Executive Editor',
            'Editors',
            'Associate Editors',
            'Members',
        ];

        $view->with('editorialBoard', $members);
        $view->with('editorialBoardRoleOrder', $roleOrder);
        $view->with('journal', $journal);
    }
}