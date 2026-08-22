<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\EditorialBoard;
use App\Models\EditorialBoardRole;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditorialBoardController extends Controller
{
    public function index(Request $request)
    {
        $journalParam = $request->route('journal');

        return view('frontend.editorial', [
            'journalParam' => $journalParam,
        ]);
    }

public function boardData(Request $request, $journalParam = null)
{
    try {
        $journal = null;

        if ($journalParam) {
            $journal = Journal::where('slug', $journalParam)
                ->orWhere('id', $journalParam)
                ->first();
        }

        // Role order now driven by the `sequence` column on editorial_board_roles
        $roleOrder = EditorialBoardRole::where('status', 1)
            ->when($journal, function ($query) use ($journal) {
                $query->where('journal_id', $journal->id);
            })
            ->orderBy('sequence')
            ->orderBy('id') // tiebreaker for equal/duplicate sequence values
            ->pluck('role')
            ->unique()
            ->values()
            ->toArray();

        // Members ordered by their own `sequence` column within each role
        $members = EditorialBoard::where('is_active', true)
            ->when($journal, function ($query) use ($journal) {
                $query->where('journal_id', $journal->id);
            })
            ->orderBy('sequence')
            ->orderBy('name')
            ->get()
            ->groupBy('role');

        $roles = collect($roleOrder)->mapWithKeys(function ($role) use ($members) {
            $list = $members->get($role, collect())->map(function ($member) {
                return [
                    'name' => $member->name,
                    'designation' => $member->designation,
                    'department' => $member->department,
                    'institute' => $member->institute,
                    'university_or_org' => $member->university_or_org,
                    'city' => $member->city,
                    'email' => $member->email,
                    'profile_image_url' => $member->profile_image
                        ? Storage::url($member->profile_image)
                        : null,
                    'orcid_url' => $member->orcid_url,
                    'scopus_url' => $member->scopus_url,
                    'web_of_science_url' => $member->web_of_science_url,
                ];
            })->values();

            return [$role => $list];
        });

        $hasMembers = $roles->contains(fn ($list) => $list->isNotEmpty());

        return response()->json([
            'status' => true,
            'data' => [
                'role_order' => $roleOrder,
                'roles' => $roles,
                'has_members' => $hasMembers,
            ],
        ]);
    } catch (\Exception $e) {
        Log::error('EditorialBoard boardData error', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch editorial board.',
        ], 500);
    }
}
}