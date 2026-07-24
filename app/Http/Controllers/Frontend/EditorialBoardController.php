<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\EditorialBoard;
use Illuminate\Support\Facades\Log;

class EditorialBoardController extends Controller
{
    // ── Public: list active members, grouped by role ───────────────────
   public function index()
    {
        return view('frontend.editorial');
    }
}