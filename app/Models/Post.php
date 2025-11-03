<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'content',
        'media_path',
    ];

    // public function getMediaPathAttribute($value)
    // {
    //     return $value ? url($value) : null;
    // }

    /**
     * Accessor to return full media URL
     */
    public function getMediaPathAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // If stored in public path, generate full URL
        return asset($value);
    }

    /**
     * Relationships
     */

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

}
