<?php

namespace App\Http\Controllers;

use App\Models\RouterOS;
use Exception;
use Illuminate\Http\Request;
use App\MyHelper\RouterosAPI;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Artisan;
use PhpParser\Node\Stmt\TryCatch;

class RouterOsController extends Controller
{
    public static $API;
    // private static $env= Artisan::call('config:cache');
        
        

    public function test_api(){
        try{
            return response()->json([
                'success'=>true,
                'message'=>'Welcome in Mikrotik Project'
            ]);
        }catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>'Error Fech data Router API'
            ]);
        }
    } 

    public function connection(Request $request){
    try{
        $API=new RouterosAPI;
        $con=$API->connect($request['ip_address'],$request['login'],$request['password']);
        // session_start();
        // $_SESSION['ip_address']=$request['ip_address'];
        // $_SESSION['login']=$request['login'];
        // $_SESSION['password']=$request['password'];
        Artisan::call('config:cache');
        if($con && $request['ip_address']!== env('IP_ADDRESS')){
            self::test_env($request->all());    
            return true;
        }
 
        return true;
    }catch(Exception $e){
        return response()->json([
            'success'=>false,
            'message'=>'Error Fech data Router API'+ $e
        ]);
    }
    } 

    public function store_routeros($data){
        $API=new RouterosAPI();

        $conn=$API->connect($data['ip_address'],$data['login'],$data['password']);
        if(!$conn) return response()->json(['error'=>true,
        'message'=>'Routeros not connected ...'],404);

       $store=new RouterOS;
       $store->identity=$API->comm('/system/identity/print');
    //    var_dump($store->identity);
    //    die;
       $store->ip_address=$data['ip_address'];
       $store->login=$data['login'];
       $store->password=$data['password'];
       $store->connect=$conn;
       $store->save();
       
       return response()->json([
        'success'=>true,
        'message'=>'Routeros Has been Saved in Database'
       ]);
    }
    
    public function routeros_connection(Request $request){
        try{
        $req_data=[
            'ip_address'=> $request->ip_address,
            'login'=> $request->login,
            'password'=> $request->password
        ];

        $routeros_db=RouterOS::where('ip_address',$req_data['ip_address'])->get();

       if(count($routeros_db)>0){
          // return response()->json(['connect to Routeros Mikrotik']);
          if($this->check_routeros_connection($request->all())):
            return response()->json([
                'connect'=>true,
                'message'=>'Routeros have a connection from database'
            ]);
        else:
            echo "Routeros not connected ... ";
            endif;
        }else{
            $this->store_routeros($request->all());
       }
        // return response()->json([
        //     'success'=>true,
        //     'message'=>'Routeros Data Has Been Saved To Database'
        // ]);
           
        }catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>'Error Fech data Router API'
            ]);
        }
    }

    public static function check_routeros_connection(){
        try{
            // session_start();
            Artisan::call('config:cache');
            self::$API=new RouterosAPI;
            $conn=self::$API->connect(env('IP_ADDRESS'),env('ROUTEROS_USERNAME'),env('ROUTEROS_PASSWORD'));
            if(!$conn)return false;
            

            return true;
        }catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>'Error Fech data Router API'
            ]);
        }
    }

    public function save_db(Request $request){
        try{  
           
            if(self::check_routeros_connection()){
                $test= $this->API->comm('/tool/user-manager/database/save',array(
                    'name'=>$request->name_db,
                ));
                
                // $this->API->write($test);
                // $ip=$this->API->read();
                // // $array=$test->parseResponse($ip);
                // var_dump($ip);
                // die;
                
            }else{
                return response()->json([
                    'success'=>false,
                    'msg'=>'Please Check connection to Mikrotik'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'success'=>false,
                'msg'=> $e->getMessage()
            ]);
        }
    }

    public function test_env($data){
        Artisan::call('config:cache');
        // Artisan::call('config:clear');
        file_put_contents(base_path('.env'),str_replace("IP_ADDRESS=".env('IP_ADDRESS'),"IP_ADDRESS=".$data->ip_address,file_get_contents(base_path('.env'))));
        Artisan::call('config:cache');
        return response()->json([
            'ip_address'=>$_ENV['IP_ADDRESS'],
            'user_name'=>$_ENV['ROUTEROS_USERNAME'],
            'password'=>$_ENV['ROUTEROS_PASSWORD']
        ]);
    }
}
