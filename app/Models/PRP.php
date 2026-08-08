<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PRP extends Model
{
    protected $table = 'prp';

    protected $fillable = [
        // ── PRPs Author ──────────────────────────────
        'author_badge',
        'author_heading',
        'author_description',

        // ── Process Submission ──────────────────────────────
        'process_badge',
        'process_heading',
        'process_description',

        // ── Manuscript ──────────────────────────────────────
        'manuscript_badge',
        'manuscript_heading',
        'manuscript_description',

        // ── Document Formatting ──────────────────────────────
        'formatting_badge1',
        'formatting_badge2',
        'formatting_heading',
        'formatting_description',

        // ── Page Layout ──────────────────────────────────────
        'layout_badge1',
        'layout_heading',
        'layout_description',

        // ── Acknowledgement ───────────────────────────────────
        'acknowlegdement_badge1',
        'acknowlegdement_heading',
        'acknowlegdement_description',
    ];

}
