<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stream extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'stream_key',
        'room_name',
        'thumbnail',
        'started_at',
        'ended_at',
        'current_viewers',
        'recording_egress_id',
        'recording_status',
        'recording_started_at',
        'recording_ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'recording_started_at' => 'datetime',
        'recording_ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'stream_category');
    }

    public function video()
    {
        return $this->hasOne(Video::class);
    }
}
