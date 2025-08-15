<?php
 namespace App\MyHelper;

 class ApiResponce{

    static function sendResponce($code=200,$msg=null,$data=null){
        $responce=[
            'status'=>$code,
            'msg'=>$msg,
            'data'=>$data
        ];
        return response()->json($responce,$code);
    }

    static function messageErorr($code=404,$msg="Not Connection Between Mikrotik And iConnect",$data=null){
        $responce=[
            'status'=>$code,
            'msg'=>$msg,
            'data'=>$data
        ];
        return response()->json($responce,$code);
    }
 }