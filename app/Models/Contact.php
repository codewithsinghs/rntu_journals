<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contact';

    protected $fillable = [
        // Contact Block 1
        'contact_badge',
        'contact_heading1',
        'contact_detail1',

        // Contact Block 2
        'contact_heading2',
        'contact_detail2',

        // Contact Block 3
        'contact_heading3',
        'contact_detail3',
    ];
}