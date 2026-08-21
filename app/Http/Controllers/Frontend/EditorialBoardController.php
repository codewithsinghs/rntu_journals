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

            $roleOrder = EditorialBoardRole::where('status', 1)
                ->when($journal, function ($query) use ($journal) {
                    $query->where('journal_id', $journal->id);
                })
                ->orderBy('id')
                ->pluck('role')
                ->unique()
                ->values()
                ->toArray();

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

            return response()->json([
                'status' => true,
                'data' => [
                    'role_order' => $roleOrder,
                    'roles' => $roles,
                ],
                // TEMPORARY DEBUG — remove once working
                '_debug' => [
                    'journal_param_received' => $journalParam,
                    'journal_resolved' => $journal ? $journal->id : null,
                    'role_rows_total_in_table' => EditorialBoardRole::count(),
                    'role_rows_matching_status_1' => EditorialBoardRole::where('status', 1)->count(),
                    'member_rows_active' => EditorialBoard::where('is_active', true)->count(),
                    'role_order_result' => $roleOrder,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch editorial board.',
                // TEMPORARY DEBUG — remove once working
                '_debug_error' => $e->getMessage(),
                '_debug_line' => $e->getLine(),
            ], 500);
        }
    }
}