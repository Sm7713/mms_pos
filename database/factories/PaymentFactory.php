<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $methods=['Al-omqy','Al-busairy','Bin-Dwal','Al-qutaiby'];
        return [
            'date'=>$this->faker->dateTime(),
            'amount'=>$this->faker->randomFloat(2,10,1000),
            'confirm'=>(bool) random_int(0,1),
            'transfer_no'=> $this->faker->numberBetween(10000000,99999999999),
            'method'=> $methods[random_int(0,3)],
            'sell_point_id'=> rand(1,20)
        ];
    }
}
