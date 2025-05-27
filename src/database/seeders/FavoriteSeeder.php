<?php

namespace Database\Seeders;

use App\Models\Favorite;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run()
    {
        $favorites = [
            [
                'user_id' => 1,
                'item_id' => 3,
                'created_at' => '2025-05-21 12:25:20',
                'updated_at' => '2025-05-21 12:25:20',
            ],
            [
                'user_id' => 1,
                'item_id' => 6,
                'created_at' => '2025-05-21 12:25:42',
                'updated_at' => '2025-05-21 12:25:42',
            ],
            [
                'user_id' => 1,
                'item_id' => 4,
                'created_at' => '2025-05-26 03:35:02',
                'updated_at' => '2025-05-26 03:35:02',
            ],
            [
                'user_id' => 4,
                'item_id' => 7,
                'created_at' => '2025-05-26 06:08:11',
                'updated_at' => '2025-05-26 06:08:11',
            ],
            [
                'user_id' => 3,
                'item_id' => 7,
                'created_at' => '2025-05-26 06:09:39',
                'updated_at' => '2025-05-26 06:09:39',
            ],
            [
                'user_id' => 1,
                'item_id' => 7,
                'created_at' => '2025-05-26 06:10:53',
                'updated_at' => '2025-05-26 06:10:53',
            ],
        ];

        foreach ($favorites as $favorite) {
            Favorite::create($favorite);
        }
    }
} 