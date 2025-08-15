<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class sellPointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_active'=>(bool) random_int(0,1),
            'max_amount'=>$this->faker->randomFloat(2,10,1000),
            'user_id'=> rand(1,20)
        ];
    }
}
