<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        return [
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'status' => 'completed',
            'payment_method' => $this->faker->randomElement(['credit_card', 'convenience']),
            'amount' => $this->faker->numberBetween(100, 100000),
            'postcode' => $this->faker->postcode,
            'address' => $this->faker->address,
            'building_name' => $this->faker->optional()->secondaryAddress,
            'stripe_session_id' => 'test_session_' . $this->faker->uuid
        ];
    }
} 