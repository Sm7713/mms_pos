<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Position;
use App\Models\sellPoint;
use App\Models\User;
use App\MyHelper\ApiResponce;
use App\Notifications\NewSellPoint;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function register(Request $request){
        $validator=Validator::make($request->all(),[
            //user
            'F_name'=>'required|string|max:15',
            // 'M_name'=>'required|string|max:15',//disable
            // 'L_name'=>'required|string|max:15',//disable
            'img'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email'=>'required|email|max:255|unique:'.User::class,
            'password'=>['required',Password::defaults()],
            //owner
            'of_name'=>'required|string|max:15',
            'om_name'=>'required|string|max:15',
            'ol_name'=>'required|string|max:15',
            'ophone'=>'required|max:15',
            'oemail'=>'required|email|max:255|unique:owners,email',
            'opersonal_card'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            //postion
            'city'=>'required|string',
            'street'=>'required|string',
            'zone'=>'required|string'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        //user
        $user=new User();
        $user->F_name=$request->F_name;
        $user->M_name=$request->M_name;
        $user->L_name=$request->L_name;
        $user->img=$request->img;
        $user->email=$request->email;
        $user->password=Hash::make($request->password);
        $user->save();
        //sellPoint
        $sellPoint=new sellPoint();
        $user->sellPoint()->save($sellPoint);
        //Owner
        $owner=new Owner();
        $owner->f_name=$request->of_name;
        $owner->m_name=$request->om_name;
        $owner->l_name=$request->ol_name;
        $owner->email=$request->oemail;
        $owner->phone_1=$request->ophone;
        // $owner->phone_2=$request->owner['phone2']?$request->owner['phone2']:null;
        $owner->personal_card=$request->opersonal_card;
        $owner->save();

        $sellPoint->Owner()->associate($owner);
        $sellPoint->save();

        //position
        $position=new Position();
        $position->city=$request->city;
        $position->street=$request->street;
        $position->zone=$request->zone;
        $position->save();

        $sellPoint->Position()->associate($position);
        $sellPoint->save();

        $data['token']=$user->createToken('mikrotik')->plainTextToken;
        $data['name']=$user->F_name;
        $data['email']=$user->email;
        $data['type']='Sell Point';
        
        $admin=User::whereHas('setting')->first();
        $admin->notify(new NewSellPoint($user->F_name));

        return ApiResponce::sendResponce(200,'Sell Point Account Created Successfully',$data);//$data
    }
    
    public function login(Request $request){
        $validator=Validator::make($request->all(),[
            'email'=>'required|email|max:255',
            'password'=>['required',Password::defaults()]
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        if(Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
            $user=Auth::user();
            if(count($user->tokens)===0){
                $data['token']=$user->createToken('mikrotik')->plainTextToken;
                $data['name']=$user->F_name;
                $data['email']=$user->email;
                $data['type']=$this->typeUser();
                return ApiResponce::sendResponce(200,'User Logged In Successfully',$data);
            }else{
                $data['token']=$user->createToken('mikrotik')->plainTextToken;
                $data['name']=$user->F_name;
                $data['email']=$user->email;
                $data['type']=$this->typeUser();
                return ApiResponce::sendResponce(200,'User are Logged In Successfully',$data);
            }
        }else{
            return ApiResponce::sendResponce(401,'User Credentials doesn\'t exist');
        }

    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponce::sendResponce(200,'Logged out Successfully');
    }

    public function auth(Request $request){
        return $request->user()->F_name;
        // return ApiResponce::sendResponce(200,"Hello My Freind");
    }

    private function typeUser(){
        $user=Auth::user();
        if($user->id && $user->sellPoint)
        return 'Sell Point';
        elseif($user->id && $user->Subscriber)
        return 'Subscriber';
        elseif ($user->id && $user->setting)
        return 'Admin';
        else
        return 'Maintainus';
    }
}
