<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Gallery extends Model
{
    protected $fillable = [
        'title',
        'source_url',
        'image_path',
        'like_count',
        'share_count',
    ];

    protected $casts = [
        'like_count' => 'integer',
        'share_count' => 'integer',
    ];

    public function likes()
    {
        return $this->belongsToMany(User::class, 'gallery_likes')->withTimestamps();
    }
    
        public function images()
        {
            return $this->hasMany(GalleryImage::class);
        }


    
}
