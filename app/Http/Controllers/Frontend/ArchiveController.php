<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{

    public function show(Journal $journal)
    {
        return view('frontend.archives', compact('journal'));
    }


    public function archivesData($id)
    {
        try {
            $journal = Journal::findOrFail($id);

            $issues = $journal->issues()
                ->with('volume')
                ->where('issues.status', 'published')
                ->whereHas('volume', function ($query) {
                    $query->where('status', 'published');
                })
                ->orderByDesc('year')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('year')
                ->sortKeysDesc()
                ->map(function ($issuesInYear) {
                    return $issuesInYear->map(function ($issue) {
                        return [
                            'uuid' => $issue->uuid,
                            'volume' => $issue->volume->volume ?? '-',
                            'issue' => $issue->issue,
                            'year' => $issue->year,
                            'published_date' => $issue->published_date
                                ? Carbon::parse($issue->published_date)->format('d M Y')
                                : null,
                            'created_at' => $issue->created_at->format('Y-m-d'),
                        ];
                    })->values();
                });

            return response()->json([
                'status' => true,
                'data' => [
                    'journal' => [
                        'id' => $journal->id,
                        'title' => $journal->title,
                    ],
                    'issues' => $issues,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Journal not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch archives (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch archives.'], 500);
        }
    }
}
