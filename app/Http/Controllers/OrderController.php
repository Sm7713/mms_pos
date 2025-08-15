<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\ReturnBill;
use App\Notifications\OrderCreated;
use App\Notifications\ReturnPayment;
use App\Notifications\SellPointRequestCards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Card;
use App\Models\Category;
use App\Models\orderDetails;
use App\Models\Payment;
use App\Models\sellPoint;
use App\Models\User;
use App\MyHelper\ApiResponce;
use App\MyHelper\UserGenerator;
use Facade\FlareClient\Api;
use Illuminate\Support\Facades\Auth;
use LDAP\Result;
use PhpParser\Node\Expr\Cast\Double;
use PHPUnit\Framework\Constraint\Count;

class OrderController extends Controller
{

    public function __invoke(Request $request)
    {
        $Orders=Order::with('sellPoint.User')->latest()->get();
        return ApiResponce::sendResponce(200,'Get All Sell Points Successfully',OrderResource::collection($Orders));
    }
    public function makeOrder(Request $request){
        $validator=Validator::make($request->all(),[
            'pro.*.qun'=>'required|integer|max:60',
            'pro.*.category_id'=>'required|integer',
            'pro.*.total_price'=>'required|numeric',
            'total_price'=>'required|numeric',
            'type_order'=>'required|boolean',
            'amount'=>'required_if:type_order,1&required_if:amount,numeric&same:total_price',
            'method'=>'required_if:type_order,1|required_if:method,string',
            'exchange_co'=>'required_if:type_order,1|required_if:method,string',
            'trans_number'=>'required_if:type_order,1|required_if:method,integer'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        if(($request->total_price>$request->user()->sellPoint->max_amount)&&($request->type_order==0))
        return ApiResponce::sendResponce(404,'Your Request More than Your Max Amount');
        $order=new Order();
        $order->date=now();
        $sellPoint=$request->user()->sellPoint;
        $order->total_price=$request->total_price;// from vue.js get the result of all prices;
        $order->sell_point_id=$request->user()->sellPoint->id; // get the id of user -> sell point
        $order->type=$request->type_order;
        $order->save();
        for($i=0;$i<count($request->pro);$i++){
            $details=new orderDetails();
            $details->qun=$request->pro[$i]['qun'];
            $details->total_price=$request->pro[$i]['total_price'];
            $details->category_id=$request->pro[$i]['category_id'];
            $details->descrption=$request->pro[$i]['desc'];
            $order->Order_Details()->save($details);
        }
        if($order->type==1){
            // $methods=['Al-omqy','Al-busairy','Bin-Dwal','Al-qutaiby'];
            $payment=new Payment();
            $payment->amount=$request->amount;
            $payment->date=now();
            $payment->method=$request->method;
            $payment->transfer_no=$request->trans_number;
            $payment->exchange_co=$request->exchange_co;
            // $request->user()->se->Payment()->save($payment);
            $payment->sell_point_id=$order->sell_point_id;
            $payment->save();
        }
        $admin=User::whereHas('setting')->first();
        $admin->notify(new SellPointRequestCards($request->user()->F_name));
        return ApiResponce::sendResponce(200,'The Order Saved Successfully');
    }

    public function orderDetails(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.Order::class,
        ]);
        
        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $detalis=orderDetails::with('Category')->whereHas('Order',function($q) use ($request){
                $q->where('id',$request->id);
            })->get();

        if(count($detalis)==0){
            return ApiResponce::sendResponce(200,'Not Found Any Order Detalis in This Order',null);
        }
        return ApiResponce::sendResponce(200,'Order Detalis Retrived Successfully',$detalis);
    }
    public function markAsRead(Request $request){
        $marked=$request->user()->notifications->markAsRead();
        if($marked){

            return ApiResponce::sendResponce(200,'All Notifications About You Marked');
        }
        return ApiResponce::sendResponce(200,'Failed To Mark All Notifications');
    }
    public function deleteOrder($id){
        $order=Order::find($id);
        if(!$order->is_executed){
            $order->delete();
            return ApiResponce::sendResponce(200,'Your Order With Details Deleted Successfully',null);
        }
         return ApiResponce::sendResponce(404,"You Can\'t Delete This Order",null);
    }

    public function makeCardsWithOrder(Request $request){//->where('qun','>=','1')
        $order=Order::with('order_details')->find($request->id);
        // return $order;
        $user=User::whereHas('sellPoint.Orders',function($q) use ($order){
            $q->where('id',$order->id);//change 38 to user->id
        })->first();
        // return $user_id;
        // $yes=UserGenerator::randLC(4);
        // return ApiResponce::sendResponce(200,$yes);
        if(!$order->is_executed){
            foreach($order->order_details as $d){
                    $generate=new CategoryController;
                    $generate->generateRandomString($d->qun,$d->category_id,$order->id);
                }
                $order->is_executed=true;
                $order->save();
                
                $user->notify(new OrderCreated($order));
            return ApiResponce::sendResponce(200,'The Cards Created Successfully');
        }else{
            return ApiResponce::sendResponce(200,'The Order Has been Executed');
        }
    }

    public function retriveCards(Request $request){
        $validator=Validator::make($request->all(),[
            'data'=>'required'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $totalPric=0;
        for($i=0;$i<count($request->data);$i++){
            $card=Card::where('id',$request->data[$i])->first();
            // $cate=Category::where('id',$card->category_id)->first();
            // $card=Card::join('categories','cards.category_id','=','categories.id')->select('categories.price','cards.order_id')->where('cards.id','=',$request->data[$i])->first();
            // return $card;
            $price=Category::whereHas('Cards',function($q) use ($card){
                $q->where('id',$card->id);
            })->first()->price;
            $totalPric+=(double)$price;
            // return $totalPric;
            $card->order_id=null;
            $card->save();
        }
        $return=new ReturnBill();
        $return->date=now();
        $return->total_amount=$totalPric;
        $return->sell_point_id=$request->user()->sellPoint->id;
        $return->save();
        $request->user()->notify(new ReturnPayment('Your Amount After Retrived Cards ',$return->total_amount));
        return ApiResponce::sendResponce(200,'The Cards Deattach with your Order');
    }

    // get all order if not executed before to the admin

    public function getAllOrders(){
        $orders=Order::all();

        if(!$orders){
            return ApiResponce::sendResponce(200,"Not Found Any Order",null);
        }
        return ApiResponce::sendResponce(200,"All Orders Retrived Successfully",$orders);
    }

    public function getOrdersToSellPoint(Request $request){
        $orders=Order::whereHas('sellPoint.User',function($q) use($request){
            $q->where('id',$request->user()->id);
        })->latest()->get();

        if(!$orders){
            return ApiResponce::sendResponce(404,'Not Any Orders Here');
        }
        return ApiResponce::sendResponce(200,'All Orders By Sell Point',$orders);
    }
}
