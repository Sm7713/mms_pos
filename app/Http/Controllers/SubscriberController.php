<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriberResource;
use App\Models\Category;
use App\Models\Subscriber;
use App\Models\User;
use App\MyHelper\ApiResponce;
use Exception;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SubscriberController extends Controller
{
    public function __invoke(Request $request)
    {
        $Subscribers=User::WhereHas('Subscriber')->with('Subscriber')->get();
        return ApiResponce::sendResponce(200,'Get All Subscribers Successfully',SubscriberResource::collection($Subscribers));
    }

    public function getSpecificSubscriber(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.Subscriber::class,
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $subscriper=User::whereHas('Subscriber',function($q) use ($request){
            $q->where('id',$request->id);
        })->first();

        if(!$subscriper){
            return ApiResponce::sendResponce(200,'Not Found Any Information About Subscriber');
        }
        return ApiResponce::sendResponce(200,'Subscriber Retrived Successfully',$subscriper);
    }

    //create  or add
    public function registerSubscriber(Request $request){
        $validator=Validator::make($request->all(),[
            'F_name'=>'required|string|max:15',
            'M_name'=>'required|string|max:15',
            'L_name'=>'required|string|max:15',
            'email'=>'required|email|max:255|unique:'.User::class,
            'password'=>['required',Password::defaults()],
            'img'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'phone'=>'required|integer|min:9',
            'username_net'=>'required',
            'password_net'=>'required',
            'category_id'=>'required|string|exists:categories,u_id',
            'connection_type'=>'required|string',
            'is_active'=>'required|boolean'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());
        
        $image=$request->file('img');//getClientOriginalExtension
        $image_name=hexdec(time()).'.'.$image->getClientOriginalExtension();
        $request->img->move(public_path('images'),$image_name);
        $img_url='images/'.$image_name;

        $user=new User();
        $user->F_name=$request->F_name;
        $user->M_name=$request->M_name;
        $user->L_name=$request->L_name;
        $user->img=$img_url;
        $user->phone=$request->phone;
        $user->email=$request->email;
        $user->password=Hash::make($request->password);
        $user->save();

        // add username and password to mikrotik
        $data=[
            $request->category_id,
            $request->username_net,
            $request->password_net,
            $request->connection_type,
            $request->is_active,
            $user->id,
        ];
        $d=$this->createCardUserName($data);
        if($d->status() == 404) {
            $user->delete();
            return ApiResponce::sendResponce(404,"",$d);
        }else{
            $data['token']=$user->createToken('mikrotik')->plainTextToken;
            $data['name']=$user->F_name;
            $data['email']=$user->email;
            return ApiResponce::sendResponce(200,'Subscriber Account Created Successfully',$data);//$data
        }
    }

    private function createCardUserName($data){
        $category=Category::where('u_id',$data[0])->first();
        try{
            if(RouterOsController::check_routeros_connection()){
                        $username=$data[1];//write the lentgh from vue.js
                        $password=$data[2]?$data[2]:null;
                        $u_id=RouterOsController::$API->comm('/tool/user-manager/user/add',array(
                            'customer'=>'admin',
                            'username'=>$username,
                            'password'=>$password
                        )); 
        if(is_array($u_id))return response()->json($u_id,404);
                        $c=RouterOsController::$API->comm('/tool/user-manager/user/create-and-activate-profile',array(
                            'customer'=>'admin',
                            'profile'=>$category->u_id, //category id in mikrotik
                            '.id'=>$u_id
                        )); 
                    
                $sub=new Subscriber();
                $sub->username=$username;
                $sub->password=$password;
                $sub->u_id=$u_id;
                $sub->category_id=$category->id;
                $sub->connection_type=$data[3];
                $sub->is_active=$data[4];
                $sub->user_id=$data[5];
                $sub->save();
                return ApiResponce::sendResponce(200,'Created Subscriber Successfully',$sub);
            }
            ApiResponce::messageErorr();
        }catch(Exception $e){
            return ApiResponce::sendResponce(200,'Some Erorres',$e->getMessage());
        }
    }

    //cahnge the status of subscriber
    public function changeStatusSubscriber(Request $request){

        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.User::class,
            'status'=>'required|boolean'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $sub=Subscriber::whereHas('User',function($q) use ($request){
            $q->where('user_id',$request->id);
        })->first();
        if($sub){
            if(RouterOsController::check_routeros_connection()){
                if($request->status){
                    $test=RouterOsController::$API->comm('/tool/user-manager/user/enable',array(
                        'numbers'=>$sub->u_id
                    ));
                }else{
                    $test=RouterOsController::$API->comm('/tool/user-manager/user/disable',array(
                        'numbers'=>$sub->u_id
                    ));
                }
                $sub->is_active=$request->status;
                $sub->save();
                return ApiResponce::sendResponce(200,'The Status Of Subscriber Are Changed',null);
            }
            ApiResponce::messageErorr();
        }
        return ApiResponce::sendResponce(404,'Not Found The Subscriber',null);
    }


    //update username and password for mikrotik

    public function editSubscriberInfo(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.Subscriber::class,
            'username_net'=>'required',
            'password_net'=>'required',
            'category_id'=>'required|string|exists:categories,u_id',
            'connection_type'=>'required|string',
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $subscriper=Subscriber::find($request->id);
        $category=Category::where('u_id',$request->category_id)->first();

        if(!$subscriper){
            return ApiResponce::sendResponce(200,'Not Found Any Information About Subscriber');
        }
        if(RouterOsController::check_routeros_connection()){

                    $username=$request->username_net;//write the lentgh from vue.js
                    $password=$request->password_net?$request->password_net:null;
                    $u_id=RouterOsController::$API->comm('/tool/user-manager/user/set',array(
                        'customer'=>'admin',
                        'username'=>$username,
                        'password'=>$password
                    )); 
            if(is_array($u_id))return ApiResponce::sendResponce(404,'Error Occured',$u_id);
                    $c=RouterOsController::$API->comm('/tool/user-manager/user/clear-profiles',array(
                        'numbers'=>$username
                    )); 
                    $c=RouterOsController::$API->comm('/tool/user-manager/user/create-and-activate-profile',array(
                        'customer'=>'admin',
                        'profile'=>$category->u_id, //category id in mikrotik
                        '.id'=>$u_id
                    )); 
                
                    $subscriper::update([
                        'username'=>$username,
                        'password'=>$password,
                        'connection_type'=>$request->connection_type,
                        'category_id'=>$category->id
                    ]);
            return ApiResponce::sendResponce(200,'Updated Subscriber Successfully',$subscriper);
        }
        return ApiResponce::messageErorr();        
    }

    //delete Subscriber
    public function deleteSubscriber(Request $request){
        
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.User::class,
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $subscriper=Subscriber::whereHas('User',function($q) use ($request){
            $q->where('id',$request->id);
        })->first();

        if(!$subscriper){
            return ApiResponce::sendResponce(200,'Not Found This Subscriber');
        }
        if(RouterOsController::check_routeros_connection()){
            $test=RouterOsController::$API->comm('/tool/user-manager/user/remove',array(
                'numbers'=>$subscriper->u_id
            ));
        }
        $subscriper->delete();
        $subscriper->User()->delete();
        return ApiResponce::sendResponce(200,'Subscriber Deleted Successfully');
    }

    public function getLogsBySubscriber($user){
        if(RouterOsController::check_routeros_connection()){
            $getlog = RouterOsController::$API->comm("/tool/user-manager/session/print", array(
                "?user" =>$user,
            ));
            $log = array_reverse($getlog);
            $TotalReg = count($getlog);
            if($TotalReg<1){
                return ApiResponce::sendResponce(404,'Not Any Logs');
            }
            return ApiResponce::sendResponce(200,'Logs Subscriber',$log);
        }
    return ApiResponce::messageErorr();
    }

    public function generalInfo(Request $request){

        $subscriper=Subscriber::whereHas('User',function($q) use ($request){
            $q->where('id',$request->user()->id);
        })->with('User')->first();

        if(!$subscriper){
            return ApiResponce::sendResponce(200,'Not Found Any Information About Subscriber');
        }
        return ApiResponce::sendResponce(200,'Subscriber Retrived Successfully',$subscriper);
    }
    //from mikrotik get the avaliable capacity of download from specific user 

    public function test(){
        if(RouterOsController::check_routeros_connection()){
            
            $getlog = RouterOsController::$API->comm("/tool/user-manager/profile/limitation/print", array(
            ));
            return $getlog;
            // $log = array_reverse($getlog);
            // $TotalReg = count($getlog);
            // if($TotalReg<1){
            //     return ApiResponce::sendResponce(404,'Not Any Logs');
            // }
            // return ApiResponce::sendResponce(200,'Logs Subscriber',$log);
        }
        return ApiResponce::messageErorr();
    }
    //get the session for the user from mikrotik
}
