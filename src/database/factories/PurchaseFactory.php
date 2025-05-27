<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    public function definition()
    {
        $paymentMethods = ['credit_card', 'convenience'];
        $statuses = ['pending', 'completed', 'cancelled'];

        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'amount' => function (array $attributes) {
                return Item::find($attributes['item_id'])->price;
            },
            'postcode' => $this->faker->postcode(),
            'address' => $this->faker->prefecture() . $this->faker->city() . $this->faker->streetAddress(),
            'building_name' => $this->faker->optional()->secondaryAddress(),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'status' => $this->faker->randomElement($statuses),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 