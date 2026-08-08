<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Guideline;
use App\Models\Journal;
use Illuminate\Support\Facades\Log;

class GuidelinesController extends Controller
{

    public function index(Journal $journal)
    {
        return view('frontend.guidelines', ['journal' => $journal]);
    }

    public function content($journalParam)
    {
        try {
            $journal = Journal::where('slug', $journalParam)
                ->orWhere('id', $journalParam)
                ->firstOrFail();

            $content = Guideline::where('journal_id', $journal->id)->latest()->first();

            return response()->json(['status' => true, 'data' => $content]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Journal not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch guidelines content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }
}