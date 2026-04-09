<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function streams()
    {
        return $this->belongsToMany(Stream::class);
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class);
    }
}
