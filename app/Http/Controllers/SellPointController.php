<?php

namespace App\Http\Controllers;

use App\Http\Resources\SellPointResource;
use App\Models\sellPoint;
use App\Models\User;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use App\Models\Owner;
use App\MyHelper\ApiResponce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SellPointController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\A
     */

     //add sell point
     public function addSellPoint(Request $request){
        //add user info
        $validator = Validator::make($request->all(), [
            // user fields
            'F_name' => 'required|string|max:15',
            'email' => 'required|email|max:255|unique:' . User::class,
            'password' => ['required', Password::defaults()],
            'max_amount'=>'required|numeric',
            'is_active'=>'required|boolean',
            // owner fields
            'owner_id' => 'required|exists:owners,id',
            //position
            'city' => 'required|string',
            'street'=>'required|string',
            'zone'=>'required|string'
        ]);
        
        if ($validator->fails()) {
            return ApiResponce::sendResponce(404, null, $validator->errors());
        }

        // Create the user
        $user = new User();
        $user->F_name = $request->F_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        // Create the sell point
        $sellPoint = new sellPoint();
        $sellPoint->max_amount = $request->max_amount;
        $sellPoint->is_active = $request->is_active;
        $sellPoint->owner_id = $request->owner_id;

        $user->sellPoint()->save($sellPoint);

        $position=new Position();
        $position->city=$request->city;
        $position->street=$request->street;
        $position->zone=$request->zone;
        $position->save();

        $sellPoint->Position()->associate($position);
        $sellPoint->save();

        // Return a successful response
        return ApiResponce::sendResponce(200, 'Sell point created successfully', $sellPoint);
     }
    public function __invoke(Request $request)
    {
        $sellPoints=User::WhereHas('sellPoint')->with('sellPoint')->get();
        return ApiResponce::sendResponce(200,'Get All Sell Points Successfully',SellPointResource::collection($sellPoints));
    }

    public function getSellPointData(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|exists:'.User::class,
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $sellPoint=sellPoint::WhereHas('User',function($q) use($request){
            $q->where('id',$request->id);
        })->with('User')->first();

        if(!$sellPoint){
            return ApiResponce::sendResponce(400,'Not Any Information About You');
        }
        return ApiResponce::sendResponce(200,'Retrived Successfully',$sellPoint);
    }
    public function changeStatusSellPoint(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|numeric',
            'status'=>'required|boolean'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $sellPoint=sellPoint::whereHas('User',function($q) use ($request){
            $q->where('user_id',$request->id);
        })->first();
        if($sellPoint){
            $sellPoint->is_active=$request->status;
            $sellPoint->save();
            return ApiResponce::sendResponce(200,'The Status Of Subscriber Are Changed',null);
        }
        return ApiResponce::sendResponce(404,'Not Found The Sell Point',null);
    }

    public function profileSellPoint(Request $request){
        $sellpoint=SellPoint::whereHas('User',function($q) use ($request){
            $q->where('id',$request->user()->id);
        })->with('User')->first();
        return ApiResponce::sendResponce(200,'Profile Sell Point',$sellpoint);
    }
    
    public function profileOwner(Request $request){
        $owner=Owner::whereHas('sellPoint',function($q) use ($request){
            $q->where('id',$request->user()->sellPoint->id);
        })->first();
        return ApiResponce::sendResponce(200,'Profile Owner',$owner);
    }
    //كشف حساب

    public function accountReports(Request $request){
       
        $sellPoint=sellPoint::whereHas('User',function($q) use ($request){
            $q->where('user_id',$request->user()->id);
        })->first();
        if($sellPoint==null){
            return ApiResponce::sendResponce(400,'Not Found','');
        }
            $combined = DB::table('orders')
            ->select(
                'orders.total_price as madeen',
                DB::raw('0 as daen'),
                DB::raw('"order" as type'),
                'orders.date as myorder'
            )
            ->where('orders.sell_point_id', $sellPoint->id)
            ->union(
                DB::table('payments')
                    ->select(
                        DB::raw('0 as madeen'),
                        'payments.amount as daen',
                        DB::raw('"payment" as type'),
                        'payments.date as myorder'
                    )
                    ->where('payments.sell_point_id', $sellPoint->id)
            )
            ->orderBy('myorder')
            ->get();
        return ApiResponce::sendResponce(200,'',$combined);
    }
    public function showOperationAccount(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.User::class,
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $sellPoint=sellPoint::whereHas('User',function($q) use($request) {
            $q->where('id',$request->id);
        });
    }

    //تقرير حول مبيعات نقطة بيع معينة

    // تغيير سقف المبلغ لنقطة بيع معينة
}
