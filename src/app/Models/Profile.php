<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'postcode',
        'address',
        'building_name',
        'image_path',
    ];

    /**
     * このプロフィールの所有者を取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
