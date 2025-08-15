<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Payment;
use App\MyHelper\ApiResponce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportsSellPointController extends Controller
{
    public function categoryOrderSellReportUser(Request $request)
    {
        // Get the user ID from the request
        $userId = $request->user()->id;

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
            )
            ->where('users.id', '=', $userId) // Filter by user ID
            ->groupBy(
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

    public function categoryOrderSellReportRange(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        // Get the user ID from the request
        $userId = $request->user()->id;

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
            )
            ->where('users.id', '=', $userId)
            ->whereBetween('date', [$request->start_date, $request->end_date]) // Filter by user ID
            ->groupBy(
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

    public function reportByDay(Request $request)
    {

        $payments = Payment::select(DB::raw('DATE(date) as date'), DB::raw('SUM(amount) as total_amount'))
                            ->where('sell_point_id', $request->user()->sellPoint->id)
                            ->groupBy(DB::raw('DATE(date)'))
                            ->get();

        return response()->json($payments);
    }

    public function reportByMonth(Request $request)
    {

        $payments = Payment::select(DB::raw('YEAR(date) as date'), DB::raw('MONTH(date) as date'), DB::raw('SUM(amount) as total_amount'))
                            ->where('sell_point_id', $request->user()->sellPoint->id)
                            ->groupBy(DB::raw('YEAR(date)'), DB::raw('MONTH(date)'))
                            ->get();

        return ApiResponce::sendResponce(200, 'Payments Retrieved Successfully', $payments);
    }

    public function reportAll(Request $request){
        $payments = Payment::select(DB::raw('amount as total_amount'),DB::raw('date'))->where('sell_point_id', $request->user()->sellPoint->id)
        ->get();

        return ApiResponce::sendResponce(200, 'Payments Retrieved Successfully', $payments);
    }

    public function reportByYear(Request $request)
    {
        $payments = Payment::select(DB::raw('YEAR(date) as date'), DB::raw('SUM(amount) as total_amount'))
                            ->where('sell_point_id', $request->user()->sellPoint->id)
                            ->groupBy(DB::raw('YEAR(date)'))
                            ->get();

        return ApiResponce::sendResponce(200, 'Report Payments By Year', $payments);
    }

    public function reportByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $payments = Payment::where('sell_point_id', $request->user()->sellPoint->id)
                            ->whereBetween('date', [$request->start_date, $request->end_date])
                            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(amount) as total_amount'))
                            ->groupBy(DB::raw('DATE(date)'))
                            ->get();

        return response()->json($payments);
    }

    public function reportBySellPoint(Request $request)
    {
        $payments = Payment::select('sell_point_id', DB::raw('SUM(amount) as total_amount'))
                            ->where('sell_point_id', $request->user()->sellPoint->id)
                            ->groupBy('sell_point_id')
                            ->get();

        return ApiResponce::sendResponce(200, 'Payment For All SellPoints', $payments);
    }

    public function salesReports(Request $request){
        $card=Card::select(DB::raw('DATE(sell_date) as date'), DB::raw('count(card.id) as total_amount'))
        ->where('sell_point_id', $request->user()->sellPoint->id)
        ->groupBy(DB::raw('DATE(date)'))
        ->get();
    }


}
