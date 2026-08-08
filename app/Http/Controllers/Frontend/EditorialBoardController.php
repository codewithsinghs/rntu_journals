<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\EditorialBoard;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditorialBoardController extends Controller
{
    protected array $roleOrder = [
        'Editor-in-Chief',
        'Managing Editor',
        'Executive Editor',
        'Advisory Board',
        'Editors',
        'Associate Editors',
        'Members',
    ];

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

            $members = EditorialBoard::where('is_active', true)
                ->when($journal, function ($query) use ($journal) {
                    $query->where('journal_id', $journal->id);
                })
                ->orderBy('sequence')
                ->orderBy('name')
                ->get()
                ->groupBy('role');

            $roles = collect($this->roleOrder)->mapWithKeys(function ($role) use ($members) {
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

            return response()->json([
                'status' => true,
                'data' => [
                    'role_order' => $this->roleOrder,
                    'roles' => $roles,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch editorial board (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch editorial board.'], 500);
        }
    }
}