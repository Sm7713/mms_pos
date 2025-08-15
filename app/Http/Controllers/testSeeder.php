<?php

namespace App\Http\Controllers;

use App\Models\sellPoint;
use App\Models\User;
use Illuminate\Http\Request;

class testSeeder extends Controller
{
    public function test(){
        $user=sellPoint::with('orders.order_details')->get();
        return response()->json($user);
    }

    public function getPayments(){
        $payment=sellPoint::with('orders.payments')->get();
        return response()->json($payment);
    }
}
