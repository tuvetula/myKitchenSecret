<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->text(20),
            'content' => $this->faker->paragraph(3),
            'preparation_time' => $this->faker->numberBetween(5,120),
            'baking_time' => $this->faker->numberBetween(0,300),
            'author_comment' => $this->faker->text(200),
            'picture' => null,
            'share_status_id' => $this->faker->numberBetween(0,3),
            'user_id' => $this->faker->numberBetween(1,50),
        ];
    }
}
