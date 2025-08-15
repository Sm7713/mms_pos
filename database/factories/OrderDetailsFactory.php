<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'total_price'=>random_int(1000,15000),
            'qun'=>rand(10,150),
            'descrption'=>$this->faker->sentence(),
            'order_id'=>rand(1,10),
            'category_id'=>1
        ];
    }
}
