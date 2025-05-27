<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,      // ユーザーを最初に作成
            ProfileSeeder::class,   // プロフィールを作成
            ItemSeeder::class,      // 商品を作成
            FavoriteSeeder::class,  // お気に入りを作成
            CommentSeeder::class,   // コメントを作成
            PurchaseSeeder::class,  // 購入を作成
        ]);
    }
}
