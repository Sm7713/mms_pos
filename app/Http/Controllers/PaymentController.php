<?php

namespace App\Http\Controllers;

use App\Http\Resources\PayemntResource;
use App\Models\Payment;
use App\Models\sellPoint;
use App\Models\User;
use App\MyHelper\ApiResponce;
use App\Notifications\PaymentPaidSellPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function getAllPaymentsByUser(Request $request){
        $payments=Payment::whereHas('sellPoint.User',function($query) use ($request){
            $query->where('id',$request->user()->id);
        })->latest()->get();

        if(!$payments){
            return ApiResponce::sendResponce(200,'Not Found Payment About You',null);
        }
        return ApiResponce::sendResponce(200,'Payments Retrived Successfully',PayemntResource::collection($payments));
    }
    //make payemt by the user sell point

    public function makePaymentByUser(Request $request){
        $validator=Validator::make($request->all(),[
            'amount'=>'required|numeric|min:6',
            'method'=>'required|string',
            'transfer_no'=>'required|unique:'.Payment::class,
            'exchange_co'=>'required|string'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $payment=new Payment();

        $payment->date=Carbon::now();
        $payment->amount=$request->amount;
        $payment->method=$request->method;
        $payment->exchange_co=$request->exchange_co;
        $payment->transfer_no=$request->transfer_no;
        $payment->sell_point_id=$request->user()->sellPoint->id;
        $payment->save();

        $admin=User::whereHas('setting')->first();
        $admin->notify(new PaymentPaidSellPoint($request->user()->F_name));

        return ApiResponce::sendResponce(201,'Payment Successfully',new PayemntResource($payment));
    }

    //get all payment with user sell point to the admin

    public function getAllPayments(){
        $payments=Payment::with('sellPoint.User')->latest()->get();

        if(!$payments){
            return ApiResponce::sendResponce(200,'Not Found Payments',null);
        }
        return ApiResponce::sendResponce(200,'All Payments',$payments);

        // return ApiResponce::sendResponce(200,'All Payments',PayemntResource::collection($payments));
    }

    //confirm the payment by the admin 
    public function confirmPayment(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|exists:'.Payment::class,
            'confirmed'=>'required|boolean'
        ]);

        if($validator->fails()) return response()->json($validator->errors(),404);

        $payment=Payment::find($request->id);
        if(!$payment){
           return ApiResponce::sendResponce(200,'Not Found Payment Here',null);
        }
        $payment->confirm=$request->confirmed;
        $payment->save();
        return ApiResponce::sendResponce(200,'The Payment Confirmed Successfully',new PayemntResource($payment));
    }

    public function deletePayment($id){
        $payment=Payment::find($id);
        if(!$payment->confirm){
            $payment->delete();
            return ApiResponce::sendResponce(200,'Your Payment Deleted Successfully',null);
        }
         return ApiResponce::sendResponce(404,"You Can't Delete This Payment",null);
    }

    public function reportByDay()
    {
        $payments = Payment::select(DB::raw('DATE(date) as date'), DB::raw('SUM(amount) as total_amount'))
                            ->groupBy(DB::raw('DATE(date)'))
                            ->get();

        return response()->json($payments);
    }

    /**
     * Get payments report by month.
     */
    public function reportByMonth()
    {
        $payments = Payment::select(DB::raw('YEAR(date) as year'), DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total_amount'))
                            ->groupBy(DB::raw('YEAR(date)'), DB::raw('MONTH(date)'))
                            ->get();

        return ApiResponce::sendResponce(200,'Payments Retrived Successfully',$payments);
    }

    /**
     * Get payments report by year.
     */
    public function reportByYear()
    {
        $payments = Payment::select(DB::raw('YEAR(date) as year'), DB::raw('SUM(amount) as total_amount'))
                            ->groupBy(DB::raw('YEAR(date)'))
                            ->get();

        return ApiResponce::sendResponce(200,'Report Payments By Year',$payments);
    }

    /**
     * Get payments report for a custom date range.
     */
    public function reportByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $payments = Payment::whereBetween('date', [$request->start_date, $request->end_date])
                            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(amount) as total_amount'))
                            ->groupBy(DB::raw('DATE(date)'))
                            ->get();

        return response()->json($payments);
    }

     /**
     * Get payments report by sell points.
     */
    public function reportBySellPoint()
    {
        //add user name model and sell point model
        $payments = Payment::select('sell_point_id', DB::raw('SUM(amount) as total_amount'))
                            ->groupBy('sell_point_id')
                            ->get();

        return ApiResponce::sendResponce(200,'Payment For All SellPoints',$payments);
    }

}
