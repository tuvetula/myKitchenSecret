<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\Share_status;
use Illuminate\Database\Eloquent\Factories\Factory;

class Share_statusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Share_status::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => $this->faker->text(20)
        ];
    }
}
