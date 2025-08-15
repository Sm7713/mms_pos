<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaintainerResource;
use App\Models\Maintainer;
use App\Models\User;
use App\MyHelper\ApiResponce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class MaintainerController extends Controller
{
    public function __invoke(Request $request)
    {
        $Maintainer=User::WhereHas('Maintainer')->with('Maintainer')->get();
        return ApiResponce::sendResponce(200,'Get All Maintainers Successfully',MaintainerResource::collection($Maintainer));
    }
    // add maintainer 
    public function registerMaintainer(Request $request){
        $validator=Validator::make($request->all(),[
            'F_name'=>'required|string|max:15',
            'M_name'=>'required|string|max:15',
            'L_name'=>'required|string|max:15',
            'email'=>'required|email|max:255|unique:'.User::class,
            'password'=>['required',Password::defaults()],
            'img'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'phone'=>'required|integer|min:9',
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

        $maintatiner=new Maintainer();
        $maintatiner->is_active=$request->is_active;
        $maintatiner->user_id=$user->id;
        $maintatiner->save();

        // add username and password to mikrotik

        $data['token']=$user->createToken('mikrotik')->plainTextToken;
        $data['name']=$user->F_name;
        $data['email']=$user->email;
        return ApiResponce::sendResponce(200,'Maintainer Account Created Successfully',$data);//$data
    }

    // delete maintainer
    public function deleteMaintainer(Request $request){
        
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.User::class,
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $maintainer=Maintainer::whereHas('User',function($q) use ($request){
            $q->where('id',$request->id);
        })->first();

        if(!$maintainer){
            return ApiResponce::sendResponce(200,'Not Found This Subscriber');
        }
        $maintainer->delete();
        $maintainer->User()->delete();
        return ApiResponce::sendResponce(200,'Subscriber Deleted Successfully');
    }

    //update maintatiner

    //change status of maintainer

    public function changeStatusMaintainer(Request $request){

        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.User::class,
            'status'=>'required|boolean'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $main=Maintainer::whereHas('User',function($q) use ($request){
            $q->where('user_id',$request->id);
        })->first();
        if($main){
            $main->is_active=$request->status;
            $main->save();
            return ApiResponce::sendResponce(200,'The Status Of Maintainer Are Changed',null);
        }
        return ApiResponce::sendResponce(404,'Not Found The Maintainer',null);
    }
}
