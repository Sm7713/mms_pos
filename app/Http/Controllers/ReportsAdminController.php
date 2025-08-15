<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Payment;
use App\Models\User;
use App\MyHelper\ApiResponce;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsAdminController extends Controller
{
    //report about sales peer days

    
    //report about
    
    //report sell point with them orders with date of sell and choose the type of the cards
    public function categoryOrderSellReport()
    {
        //add in this function the title from category table and the price from it
        $report = DB::table('cards')
            ->select(
                'category_id',
                'order_id',
                'sell_status',
                DB::raw('count(*) as total'),
                DB::raw('group_concat(sell_date) as sell_dates')
            )
            ->groupBy('category_id', 'order_id', 'sell_status')
            ->get();

        return ApiResponce::sendResponce(200,'Report of Cards',$report);
    }

    public function categoryOrderSellReportAdvance()
    {
        //add in this function the title from category table and the price from it
        
        // Build the query to include the additional relationships and filter by user ID
        $report = DB::table('cards')
            ->join('categories', 'cards.category_id', '=', 'categories.id')
            ->join('orders', 'cards.order_id', '=', 'orders.id')
            ->join('sell_points', 'orders.sell_point_id', '=', 'sell_points.id')
            ->join('users', 'sell_points.user_id', '=', 'users.id')
            ->select(
                'cards.category_id',
                'categories.title as category_title',
                'categories.price',
                'cards.order_id',
                'cards.sell_status',
                'orders.sell_point_id',
                'sell_points.user_id',
                'users.F_name as user_name', // assuming users table has a name field
                DB::raw('count(cards.id) as count_card'),
                DB::raw('sum(categories.price)as total_price'),
                DB::raw('group_concat(cards.sell_date) as sell_dates')
            )->groupBy(
                'cards.category_id',
                'cards.order_id',
                'cards.sell_status',
                'categories.title',
                'categories.price',
                'orders.sell_point_id',
                'sell_points.user_id',
                'users.F_name'
            )
            ->get();

        return ApiResponce::sendResponce(200, 'Report of Cards', $report);
    }
    //report About above function and add start date and end date for report

    //report about how many users active in month from mikrotik
    
    //report about 
    public function count_cards(){
        $today=Carbon::today();
        $count=Card::whereDate('sell_date',$today)->count();
        return ApiResponce::sendResponce(200,"Cards Sold To Day",$count);
    }

    public function price_cards(){
        $user=User::all();
        $count=count($user);
    return ApiResponce::sendResponce(200, 'Report of Cards', $count);
    }

    public function sum_payment(){
        $today=Carbon::today();
        $payment=Payment::whereDate('date',$today)->sum('amount');
        return ApiResponce::sendResponce(200,'sum Payment to day',$payment);
    }

}
