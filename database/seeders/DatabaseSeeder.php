<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\orderDetails;
use App\Models\Position;
use App\Models\sellPoint;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //  User::factory(30)->create();
        //  sellPoint::factory(30)->create();
        //  Order::factory(30)->create();
        //  orderDetails::factory(30)->create();
         Payment::factory(30)->create();
        // Position::factory(30)->create();
        // $this->call(OrderSeed::class);
    }
}
