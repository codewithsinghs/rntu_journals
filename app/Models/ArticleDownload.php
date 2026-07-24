<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleDownload extends Model
{
    protected $fillable = [
        'submit_article_id',
        'ip_address',
    ];

    public function article()
    {
        return $this->belongsTo(SubmitArticle::class, 'submit_article_id');
    }
}