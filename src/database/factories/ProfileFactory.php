<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'username' => $this->faker->userName(),
            'postcode' => $this->faker->postcode(),
            'address' => $this->faker->prefecture() . $this->faker->city() . $this->faker->streetAddress(),
            'building_name' => $this->faker->optional()->secondaryAddress(),
            'image_path' => 'img/profiles/default.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 