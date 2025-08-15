<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'city'=>$this->faker->city(),
            'street'=>$this->faker()->streetName(),
            'zone'=>$this->faker()->streetAddress()
        ];
    }
}
