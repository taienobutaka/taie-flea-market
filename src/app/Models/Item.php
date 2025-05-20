<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'brand',
        'description',
        'price',
        'condition',
        'category',
        'user_id',
        'image_path',
        'status'
    ];

    protected $casts = [
        'category' => 'array',
        'price' => 'integer'
    ];

    /**
     * 出品者とのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function isFavoritedBy($user)
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function isSold()
    {
        return $this->status === 'sold';
    }
}