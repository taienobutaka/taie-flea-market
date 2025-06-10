<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Comment;
use App\Models\Chat;
use App\Models\Purchase;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // ユーザーを作成
        $users = [
            [
                'id' => 1,
                'username' => '田中太郎',
                'email' => 'tanaka@gmail.com',
                'password' => Hash::make('11111111'),
                'email_verified_at' => '2025-06-10 14:12:20',
                'created_at' => '2025-06-10 14:12:11',
                'updated_at' => '2025-06-10 14:12:20',
            ],
            [
                'id' => 2,
                'username' => '山田二郎',
                'email' => 'yamada@gmail.com',
                'password' => Hash::make('22222222'),
                'email_verified_at' => '2025-06-10 14:20:46',
                'created_at' => '2025-06-10 14:20:38',
                'updated_at' => '2025-06-10 14:20:46',
            ],
            [
                'id' => 3,
                'username' => '鈴木花子',
                'email' => 'suzuki@gmail.com',
                'password' => Hash::make('33333333'),
                'email_verified_at' => '2025-06-10 14:29:12',
                'created_at' => '2025-06-10 14:29:07',
                'updated_at' => '2025-06-10 14:29:12',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // プロフィールを作成
        $profiles = [
            [
                'id' => 1,
                'user_id' => 1,
                'username' => 'tanaka',
                'postcode' => '111-1111',
                'address' => '京都府',
                'building_name' => '11-1',
                'image_path' => 'img/profiles/1749564776_スクリーンショット 2025-05-14 083156.png',
                'created_at' => '2025-06-10 14:13:24',
                'updated_at' => '2025-06-10 14:13:24',
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'username' => 'yamada',
                'postcode' => '222-2222',
                'address' => '東京都',
                'building_name' => '22-2',
                'image_path' => 'img/profiles/1749565271_スクリーンショット 2025-05-14 083302.png',
                'created_at' => '2025-06-10 14:21:49',
                'updated_at' => '2025-06-10 14:21:49',
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'username' => 'suzuki',
                'postcode' => '333-3333',
                'address' => '大阪府',
                'building_name' => '33-3',
                'image_path' => 'img/profiles/1749565784_スクリーンショット 2025-05-14 083204.png',
                'created_at' => '2025-06-10 14:30:27',
                'updated_at' => '2025-06-10 14:30:27',
            ],
        ];

        foreach ($profiles as $profileData) {
            Profile::create($profileData);
        }

        // 商品を作成
        $items = [
            [
                'id' => 1,
                'name' => '腕時計',
                'brand' => 'test',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000.00,
                'condition' => '良好',
                'image_path' => 'img/items/1749564892_Armani+Mens+Clock.jpg',
                'category' => '["fashion","mens"]',
                'user_id' => 1,
                'status' => 'available',
                'created_at' => '2025-06-10 14:15:52',
                'updated_at' => '2025-06-10 14:15:52',
            ],
            [
                'id' => 2,
                'name' => 'HDD',
                'brand' => 'test',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000.00,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'img/items/1749564975_HDD+Hard+Disk.jpg',
                'category' => '["electronics"]',
                'user_id' => 1,
                'status' => 'available',
                'created_at' => '2025-06-10 14:16:54',
                'updated_at' => '2025-06-10 14:16:54',
            ],
            [
                'id' => 3,
                'name' => '玉ねぎ3束',
                'brand' => 'test',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300.00,
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'img/items/1749565026_iLoveIMG+d.jpg',
                'category' => '["kitchen"]',
                'user_id' => 1,
                'status' => 'available',
                'created_at' => '2025-06-10 14:17:49',
                'updated_at' => '2025-06-10 14:17:49',
            ],
            [
                'id' => 4,
                'name' => '革靴',
                'brand' => 'test',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000.00,
                'condition' => '状態が悪い',
                'image_path' => 'img/items/1749565078_Leather+Shoes+Product+Photo.jpg',
                'category' => '["fashion","mens"]',
                'user_id' => 1,
                'status' => 'available',
                'created_at' => '2025-06-10 14:18:39',
                'updated_at' => '2025-06-10 14:18:39',
            ],
            [
                'id' => 5,
                'name' => 'ノートPC',
                'brand' => 'test',
                'description' => '高性能なノートパソコン',
                'price' => 45000.00,
                'condition' => '良好',
                'image_path' => 'img/items/1749565130_Living+Room+Laptop.jpg',
                'category' => '["electronics"]',
                'user_id' => 1,
                'status' => 'available',
                'created_at' => '2025-06-10 14:19:38',
                'updated_at' => '2025-06-10 14:19:38',
            ],
            [
                'id' => 6,
                'name' => 'マイク',
                'brand' => 'test',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000.00,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'img/items/1749565370_Music+Mic+4632231.jpg',
                'category' => '["electronics"]',
                'user_id' => 2,
                'status' => 'available',
                'created_at' => '2025-06-10 14:23:50',
                'updated_at' => '2025-06-10 14:23:50',
            ],
            [
                'id' => 7,
                'name' => 'ショルダーバッグ',
                'brand' => 'test',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500.00,
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'img/items/1749565442_Purse+fashion+pocket.jpg',
                'category' => '["fashion","ladies"]',
                'user_id' => 2,
                'status' => 'available',
                'created_at' => '2025-06-10 14:24:56',
                'updated_at' => '2025-06-10 14:24:56',
            ],
            [
                'id' => 8,
                'name' => 'タンブラー',
                'brand' => 'test',
                'description' => '使いやすいタンブラー',
                'price' => 500.00,
                'condition' => '状態が悪い',
                'image_path' => 'img/items/1749565518_Tumbler+souvenir.jpg',
                'category' => '["kitchen"]',
                'user_id' => 2,
                'status' => 'available',
                'created_at' => '2025-06-10 14:26:11',
                'updated_at' => '2025-06-10 14:26:11',
            ],
            [
                'id' => 9,
                'name' => 'コーヒーミル',
                'brand' => 'test',
                'description' => '手動のコーヒーミル',
                'price' => 4000.00,
                'condition' => '良好',
                'image_path' => 'img/items/1749565579_Waitress+with+Coffee+Grinder.jpg',
                'category' => '["kitchen"]',
                'user_id' => 2,
                'status' => 'available',
                'created_at' => '2025-06-10 14:27:09',
                'updated_at' => '2025-06-10 14:27:09',
            ],
            [
                'id' => 10,
                'name' => 'メイクセット',
                'brand' => 'test',
                'description' => '便利なメイクアップセット',
                'price' => 2500.00,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'img/items/1749565642_外出メイクアップセット.jpg',
                'category' => '["ladies","cosmetics"]',
                'user_id' => 2,
                'status' => 'available',
                'created_at' => '2025-06-10 14:28:05',
                'updated_at' => '2025-06-10 14:28:05',
            ],
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }

        // テスト用ユーザーアカウント情報を表示
        echo "\n=== テスト用ユーザーアカウント ===\n";
        echo "田中太郎 (tanaka@gmail.com) - パスワード: 11111111\n";
        echo "山田二郎 (yamada@gmail.com) - パスワード: 22222222\n";
        echo "鈴木花子 (suzuki@gmail.com) - パスワード: 33333333\n";
        echo "================================\n\n";
    }
}
