<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition()
    {
        $categories = [
            ['fashion', 'mens', 'accessories'],
            ['electronics'],
            ['kitchen'],
            ['fashion', 'mens'],
            ['electronics'],
            ['fashion', 'ladies'],
            ['kitchen'],
            ['ladies', 'cosmetics']
        ];

        $conditions = ['良好', '目立った傷や汚れなし', 'やや傷や汚れあり', '状態が悪い'];
        $statuses = ['available', 'sold'];

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'brand' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(300, 45000),
            'condition' => $this->faker->randomElement($conditions),
            'image_path' => 'img/items/default.jpg',
            'category' => json_encode($this->faker->randomElement($categories)),
            'status' => $this->faker->randomElement($statuses),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 