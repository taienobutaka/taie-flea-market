<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run()
    {
        $comments = [
            [
                'user_id' => 2,
                'item_id' => 4,
                'content' => 'test',
                'created_at' => '2025-05-21 13:04:17',
                'updated_at' => '2025-05-21 13:04:17',
            ],
            [
                'user_id' => 1,
                'item_id' => 9,
                'content' => 'test',
                'created_at' => '2025-05-23 03:53:55',
                'updated_at' => '2025-05-23 03:53:55',
            ],
            [
                'user_id' => 1,
                'item_id' => 8,
                'content' => 'test',
                'created_at' => '2025-05-23 04:52:44',
                'updated_at' => '2025-05-23 04:52:44',
            ],
            [
                'user_id' => 3,
                'item_id' => 7,
                'content' => 'test',
                'created_at' => '2025-05-26 06:10:08',
                'updated_at' => '2025-05-26 06:10:08',
            ],
            [
                'user_id' => 1,
                'item_id' => 7,
                'content' => 'test',
                'created_at' => '2025-05-26 06:10:59',
                'updated_at' => '2025-05-26 06:10:59',
            ],
        ];

        foreach ($comments as $comment) {
            Comment::create($comment);
        }
    }
} 