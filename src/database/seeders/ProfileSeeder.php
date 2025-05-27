<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run()
    {
        $profiles = [
            [
                'user_id' => 1,
                'username' => 'tanaka',
                'postcode' => '111-1111',
                'address' => '東京都',
                'building_name' => '11-1',
                'image_path' => 'img/profiles/1747804583_スクリーンショット 2025-05-14 083156.png',
                'created_at' => '2025-05-21 05:16:58',
                'updated_at' => '2025-05-26 03:54:40',
            ],
            [
                'user_id' => 2,
                'username' => 'yamada',
                'postcode' => '222-2222',
                'address' => '京都府',
                'building_name' => '22-2',
                'image_path' => 'img/profiles/1747804882_スクリーンショット 2025-05-14 083302.png',
                'created_at' => '2025-05-21 05:21:42',
                'updated_at' => '2025-05-21 05:21:42',
            ],
            [
                'user_id' => 3,
                'username' => 'suzuki',
                'postcode' => '333-3333',
                'address' => '福岡県',
                'building_name' => '33-3',
                'image_path' => 'img/profiles/1747805173_スクリーンショット 2025-05-14 083204.png',
                'created_at' => '2025-05-21 05:26:46',
                'updated_at' => '2025-05-21 05:26:46',
            ],
            [
                'user_id' => 4,
                'username' => 'maeda',
                'postcode' => '444-4444',
                'address' => '青森県',
                'building_name' => '4',
                'image_path' => 'img/profiles/1747805392_スクリーンショット 2025-05-14 083314.png',
                'created_at' => '2025-05-21 05:30:21',
                'updated_at' => '2025-05-26 06:14:04',
            ],
        ];

        foreach ($profiles as $profile) {
            Profile::create($profile);
        }
    }
} 