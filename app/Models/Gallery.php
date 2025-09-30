<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'comic_id',
        'title',
        'source_url',
        'image_path',
    ];
}
