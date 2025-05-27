<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name' => '腕時計',
                'brand' => 'test',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000.00,
                'condition' => '良好',
                'image_path' => 'img/items/1747804652_Armani+Mens+Clock.jpg',
                'category' => '["fashion","mens","accessories"]',
                'user_id' => 1,
                'status' => 'available',
                'created_at' => '2025-05-21 05:18:19',
                'updated_at' => '2025-05-21 05:18:19',
            ],
            [
                'name' => 'HDD',
                'brand' => 'test',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000.00,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'img/items/1747804769_HDD+Hard+Disk.jpg',
                'category' => '["electronics"]',
                'user_id' => 1,
                'status' => 'available',
                'created_at' => '2025-05-21 05:19:58',
                'updated_at' => '2025-05-21 05:19:58',
            ],
            [
                'name' => '玉ねぎ3束',
                'brand' => 'test',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300.00,
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'img/items/1747804956_iLoveIMG+d.jpg',
                'category' => '["kitchen"]',
                'user_id' => 2,
                'status' => 'available',
                'created_at' => '2025-05-21 05:23:13',
                'updated_at' => '2025-05-21 05:23:13',
            ],
            [
                'name' => '革靴',
                'brand' => 'test',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000.00,
                'condition' => '状態が悪い',
                'image_path' => 'img/items/1747804999_Leather+Shoes+Product+Photo.jpg',
                'category' => '["fashion","mens"]',
                'user_id' => 2,
                'status' => 'sold',
                'created_at' => '2025-05-21 05:24:08',
                'updated_at' => '2025-05-21 23:17:38',
            ],
            [
                'name' => 'ノートPC',
                'brand' => 'test',
                'description' => '高性能なノートパソコン',
                'price' => 45000.00,
                'condition' => '良好',
                'image_path' => 'img/items/1747805056_Living+Room+Laptop.jpg',
                'category' => '["electronics"]',
                'user_id' => 2,
                'status' => 'available',
                'created_at' => '2025-05-21 05:24:49',
                'updated_at' => '2025-05-21 05:24:49',
            ],
            [
                'name' => 'マイク',
                'brand' => 'test',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000.00,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'img/items/1747805221_Music+Mic+4632231.jpg',
                'category' => '["electronics"]',
                'user_id' => 3,
                'status' => 'available',
                'created_at' => '2025-05-21 05:27:43',
                'updated_at' => '2025-05-21 05:27:43',
            ],
            [
                'name' => 'ショルダーバッグ',
                'brand' => 'test',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500.00,
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'img/items/1747805269_Purse+fashion+pocket.jpg',
                'category' => '["fashion","ladies"]',
                'user_id' => 3,
                'status' => 'sold',
                'created_at' => '2025-05-21 05:28:38',
                'updated_at' => '2025-05-26 07:07:27',
            ],
            [
                'name' => 'タンブラー',
                'brand' => 'test',
                'description' => '使いやすいタンブラー',
                'price' => 500.00,
                'condition' => '状態が悪い',
                'image_path' => 'img/items/1747805434_Tumbler+souvenir.jpg',
                'category' => '["kitchen"]',
                'user_id' => 4,
                'status' => 'available',
                'created_at' => '2025-05-21 05:31:21',
                'updated_at' => '2025-05-21 05:31:21',
            ],
            [
                'name' => 'コーヒーミル',
                'brand' => 'test',
                'description' => '手動のコーヒーミル',
                'price' => 4000.00,
                'condition' => '良好',
                'image_path' => 'img/items/1747805518_Waitress+with+Coffee+Grinder.jpg',
                'category' => '["kitchen"]',
                'user_id' => 4,
                'status' => 'available',
                'created_at' => '2025-05-21 05:32:34',
                'updated_at' => '2025-05-21 05:32:34',
            ],
            [
                'name' => 'メイクセット',
                'brand' => 'test',
                'description' => '便利なメイクアップセット',
                'price' => 2500.00,
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'img/items/1747805560_外出メイクアップセット.jpg',
                'category' => '["ladies","cosmetics"]',
                'user_id' => 4,
                'status' => 'available',
                'created_at' => '2025-05-21 05:33:14',
                'updated_at' => '2025-05-21 05:33:14',
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
} 