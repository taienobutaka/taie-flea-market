<?php

namespace Database\Seeders;

use App\Models\Purchase;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run()
    {
        $purchases = [
            [
                'user_id' => 1,
                'item_id' => 4,
                'amount' => 4000.00,
                'postcode' => '111-1111',
                'address' => '東京都',
                'building_name' => '11-1',
                'payment_method' => 'credit_card',
                'status' => 'pending',
                'created_at' => '2025-05-21 23:16:04',
                'updated_at' => '2025-05-21 23:16:04',
            ],
            [
                'user_id' => 4,
                'item_id' => 7,
                'amount' => 3500.00,
                'postcode' => '444-4444',
                'address' => '青森県',
                'building_name' => '4',
                'payment_method' => 'convenience',
                'status' => 'pending',
                'created_at' => '2025-05-26 07:07:09',
                'updated_at' => '2025-05-26 07:07:09',
            ],
        ];

        foreach ($purchases as $purchase) {
            Purchase::create($purchase);
        }
    }
} 