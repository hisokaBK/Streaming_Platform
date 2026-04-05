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
        return $this->hasMany(Stream::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
