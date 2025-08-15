<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    // protected $model=Order::class;
    public function definition()
    {
        return [
            'date'=>$this->faker->dateTime(),
            'total_price'=>$this->faker->randomFloat(2,10,1000),
            'is_executed'=>(bool) random_int(0,1),
            'type'=>(bool) random_int(0,1),
            'sell_point_id'=>rand(1,10)
        ];
    }
}
