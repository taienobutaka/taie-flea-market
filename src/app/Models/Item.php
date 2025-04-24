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
        'category',
        'condition',
        'image_path',
        'user_id'
    ];

    /**
     * 出品者とのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}