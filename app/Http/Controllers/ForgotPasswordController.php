<?php

namespace App\Http\Controllers;

use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use App\Models\User;
use App\MyHelper\ApiResponce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request){
        $validator=Validator::make($request->all(),[
            'email'=>'required|email|exists:'.User::class,
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        ResetCodePassword::where('email',$request->email)->delete();

        $data['email']=$request->email;
        $data['code']=mt_rand(100000,999999);

        $codeData=ResetCodePassword::create($data);

        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));

        return ApiResponce::sendResponce(200,trans('passwords.sent'));
    }

    public function codeCheck(Request $request){
        $validator=Validator::make($request->all(),[
            'code' => 'required|string|exists:reset_code_passwords',
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());
        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response(['message' => trans('passwords.code_is_expire')], 422);
        }

        return response([
            'code' => $passwordReset->code,
            'message' => trans('passwords.code_is_valid')
        ], 200);
    }

    public function resetPassword(Request $request){
        $validator=Validator::make($request->all(),[
            'code'=>'required|exists:'.ResetCodePassword::class,
            'password'=>'required_with:password_confirmation|min:6|same:password_confirmation',
            'password_confirmation'=>'required|min:6',
        ]);
        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());
        
        // if($request->password==$request->password_confirmation)
        // return "true";
        // else
        // return "False";
        $passwordReset=ResetCodePassword::firstWhere('code',$request->code);

        if($passwordReset->created_at > now()->addHour()){
            $passwordReset->delete();
            return ApiResponce::sendResponce(422,trans('passwords.code_is_expire'));
        }

        $user=User::firstWhere('email',$passwordReset->email);

        $user->password=Hash::make($request->password);
        $user->save();

        $passwordReset->delete();

        return ApiResponce::sendResponce(200,'Password Has Been Successfully Reset');
    }
}
