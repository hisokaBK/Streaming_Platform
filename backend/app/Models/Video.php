<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stream_id',
        'title',
        'description',
        'url',
        'duration',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'video_category');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
