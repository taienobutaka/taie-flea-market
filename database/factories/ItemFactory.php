<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'brand' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'price' => $this->faker->numberBetween(100, 100000),
            'condition' => $this->faker->randomElement(['new', 'like_new', 'good', 'fair']),
            'category' => json_encode($this->faker->randomElements(['fashion', 'electronics', 'interior', 'ladies', 'mens', 'cosmetics', 'books', 'games', 'sports', 'kitchen', 'handmade', 'accessories', 'toys', 'baby'], 2)),
            'user_id' => User::factory(),
            'image_path' => 'img/items/default.jpg',
            'status' => 'active'
        ];
    }
} 