<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PRP extends Model
{
    protected $table = 'prp';

    protected $fillable = [
        'author_heading',
        'author_description',
    ];

}
