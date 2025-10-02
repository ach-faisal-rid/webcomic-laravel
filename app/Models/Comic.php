<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comic extends Model
{
    protected $fillable = [
        'title',
        'author',
        'pdf_path',
    ];

     // Kalau nanti kita mau generate URL langsung
    public function getPdfUrlAttribute()
    {
        return asset('storage/' . $this->pdf_path);
    }
}
