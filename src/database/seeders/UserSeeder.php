<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 既存のユーザーデータを再現
        $users = [
            [
                'username' => '田中太郎',
                'email' => 'tanaka@gmail.com',
                'password' => Hash::make('11111111'),
                'email_verified_at' => '2025-05-21 05:13:10',
                'created_at' => '2025-05-21 05:13:04',
                'updated_at' => '2025-05-21 05:13:10',
            ],
            [
                'username' => '山田二郎',
                'email' => 'yamada@gmail.com',
                'password' => Hash::make('22222222'),
                'email_verified_at' => '2025-05-21 05:20:35',
                'created_at' => '2025-05-21 05:20:31',
                'updated_at' => '2025-05-21 05:20:35',
            ],
            [
                'username' => '鈴木花子',
                'email' => 'suzuki@gmail.com',
                'password' => Hash::make('33333333'),
                'email_verified_at' => '2025-05-21 05:25:22',
                'created_at' => '2025-05-21 05:25:19',
                'updated_at' => '2025-05-21 05:25:22',
            ],
            [
                'username' => '前田紀子',
                'email' => 'maeda@gmail.com',
                'password' => Hash::make('44444444'),
                'email_verified_at' => '2025-05-21 05:29:17',
                'created_at' => '2025-05-21 05:29:13',
                'updated_at' => '2025-05-21 05:29:17',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
} 