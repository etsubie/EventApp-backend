<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(6), 
            'description' => $this->faker->paragraph(),
            'category_id' => Category::factory(), 
            'location' => $this->faker->address(),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 year'), 
            'end_date' => $this->faker->dateTimeBetween('+1 week', '+1 year'), 
            'ticket_price' => $this->faker->randomFloat(2, 10, 1000), 
            'capacity' => $this->faker->numberBetween(1, 1000), 
            // 'imgUrl' => $this->faker->imageUrl(), // Generates a random image URL
        ];
    }
}
