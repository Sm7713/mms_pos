<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Category;
use App\Models\Order;
use App\MyHelper\ApiResponce;
use App\MyHelper\UserGenerator;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Exception;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Image;
use Ramsey\Uuid\Type\Integer;

class CardController extends Controller
{
    public function EditCard(Request $request){

    }

    public function createCards(Request $request){
        $validator=Validator::make($request->all(),[
            'order_id'=>'required|numeric|exists:orders,id',//requirder if
            'category_id'=>'required|exists:categories,u_id',
            'qun'=>'required|numeric',
            'mode'=>'required|numeric',
            'type'=>'required|boolean',
            'len_user'=>'required|Integer|max:12|min:4',
            'len_pass'=>'required|Integer|max:12|min:4'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());
        
        $data=[
            $request->category_id,
            $request->qun,
            $request->order_id,// append the lentgh of username and password
            $request->type,
            $request->len_user,
            $request->len_pass,
        ];

        if($request->mode==1){//user name only
           return $this->createCardUserName($data);
        }else if($request->mode==2){
            return $this->createCardUserNameAndPassword($data);
        }else{
            return $this->createCardsForAdmin($data);
        }

    }

    public function ShowCards(){
        if(RouterOsController::check_routeros_connection()){
            $cards=RouterOsController::$API->comm('/tool/user-manager/user/print',[
                '?.id'=>'*6'
            ]);
            // if(count($cards)===1){
            //     return response()->json('yes this is object');
            // }else{
            //     return false;
            // }
        //    $s= array_column($cards,'.id');
            return response()->json($cards);
        }
    }

    private function createCardsForAdmin($data){
        $category=Category::where('u_id',$data[0])->first();
        $qun=(int)$data[1];
        try{
            if(RouterOsController::check_routeros_connection()){
                for($i=0;$i<$qun;$i++){
                    $username= $data[3]? UserGenerator::randN($data[4]):UserGenerator::randNLC($data[4]);//write the lentgh from vue.js
                    $u_id=RouterOsController::$API->comm('/tool/user-manager/user/add',array(
                        'customer'=>'admin',
                        'username'=>$username,
                        'comment'=>'admin'
                    )); 
    
                    $c=RouterOsController::$API->comm('/tool/user-manager/user/create-and-activate-profile',array(
                        'customer'=>'admin',
                        'profile'=>$category->u_id, //category id in mikrotik
                        '.id'=>$u_id
                    ));                     
                }
                return ApiResponce::sendResponce(200,'Created Card Successfully');
            }
            ApiResponce::messageErorr();
        }catch(Exception $e){
            return ApiResponce::sendResponce(200,'Some Erorres',$e->getMessage());
        }
    }
    //change to private after testing
    private function createCardUserName($data){
        $order=Order::find($data[2]);
        $category=Category::where('u_id',$data[0])->first();
        $qun=(int)$data[1];
        try{
            if(RouterOsController::check_routeros_connection()){
                    for($i=0;$i<$qun;$i++){

                        $username=$data[3]? UserGenerator::randN($data[4]):UserGenerator::randNLC($data[4]);//write the lentgh from vue.js
                        $u_id=RouterOsController::$API->comm('/tool/user-manager/user/add',array(
                            'customer'=>'admin',
                            'username'=>$username,
                        )); 
        
                        $c=RouterOsController::$API->comm('/tool/user-manager/user/create-and-activate-profile',array(
                            'customer'=>'admin',
                            'profile'=>$category->u_id, //category id in mikrotik
                            '.id'=>$u_id
                        )); 
                        $card=new Card();
                        $card->u_id=$u_id;
                        $card->username=$username;
                        $card->category()->associate($category);
                        $card->Order()->associate($order);
                        $card->save();
                        
                    }
                return ApiResponce::sendResponce(200,'Card Created Successfully');
            }
            ApiResponce::messageErorr();
        }catch(Exception $e){
            return ApiResponce::sendResponce(404,'Some Erorres',$e->getMessage());
        }
    }

    private function createCardUserNameAndPassword($data){
        $order=Order::find($data[2])->first();
        $category=Category::where('u_id',$data[0])->first();
        $qun=(int)$data[1];
        try{
            if(RouterOsController::check_routeros_connection()){
                for($i=0;$i<$qun;$i++){
                    $username=$data[3]? UserGenerator::randN($data[4]):UserGenerator::randNLC($data[4]);//write the lentgh from vue.js
                    $password= $data[3]? UserGenerator::randN($data[4]):UserGenerator::randNLC($data[4]);//write the lentgh of password from vue.js

                    $u_id=RouterOsController::$API->comm('/tool/user-manager/user/add',array(
                        'customer'=>'admin',
                        'username'=>$username,
                        'password'=>$password
                    )); 
    
                    $c=RouterOsController::$API->comm('/tool/user-manager/user/create-and-activate-profile',array(
                        'customer'=>'admin',
                        'profile'=>$category->u_id, //category id in mikrotik
                        '.id'=>$u_id
                    )); 
                    $card=new Card();
                    $card->u_id=$u_id;
                    $card->username=$username;
                    $card->password=$password;
                    $card->category()->associate($category);
                    $card->Order()->associate($order);
                    $card->save();
                    
                }
                return ApiResponce::sendResponce(200,'Created Card Successfully');
            }
            ApiResponce::messageErorr();
        }catch(Exception $e){
            return ApiResponce::sendResponce(404,'Some Erorres',$e->getMessage());
        }
    }
    //'where'=> ['.id','=','*2']

    public function cardWithCategory(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required',
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $card=Category::WhereHas('Cards',function($q) use($request){
            $q->where('id',$request->id);
        })->with('Cards')->first();

        return ApiResponce::sendResponce(200,'Retrived Card Successfully',$card);
    }
    public function getCards(){
        if(RouterOsController::check_routeros_connection()){
            $cards=RouterOsController::$API->comm('/tool/user-manager/user/print',array(
                'without-paging'=>null
            ));

            return ApiResponce::sendResponce(200,'Cards Retrived Successfully',$cards);
        }
        ApiResponce::messageErorr();
    }

    public function deleteCard(Request $request){
        $validator=Validator::make($request->all(),[
            'u_id'=>'required',
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $card=Card::where('u_id',$request->u_id)->first();
        if ($card) {
            if (RouterOsController::check_routeros_connection()) {
                $status = RouterOsController::$API->comm('/tool/user-manager/user/remove', array(
                    '.id' => $request->u_id,
                ));
            }
            return ApiResponce::sendResponce(200, 'Card Deleted Successfully');
        }
        if (RouterOsController::check_routeros_connection()) {
            $status = RouterOsController::$API->comm('/tool/user-manager/user/remove', array(
                '.id' => $request->u_id,
            ));
            return ApiResponce::sendResponce(200, 'Card Deleted Successfully');
        }
        return ApiResponce::messageErorr();
    }
    public function changeStatus(){
        if(RouterOsController::check_routeros_connection()){
            $status=RouterOsController::$API->comm('/tool/user-manager/user/set',array(
                '.id'=>'*2',
                'customer'=>'admin',
                'incomplete'=>'true'
            ));

            return response()->json($status);
        }
        ApiResponce::messageErorr();
    }

    public function generatePDF(){
        $dompdf=new Dompdf();
        $dompdf->loadHtml('<html><body></html></body>');
        $canvas=$dompdf->getCanvas();
        // $pdf=App::make('dompdf.wrapper');
        $add=['123','321','ewq','dwed','1hn','3ngf','ngf','nfnf','hngt3','bnfg','fdf','grfgd'];
        $image=public_path('03.jpg');
        $images=null;
        for($i=0;$i<count($add);$i++){
            $img=Image::make($image);
            $img->text($add[$i],260,50,function($font){
                $font->file(public_path('robotoregular.ttf'));
                $font->size(24);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
                $font->angle(0);
            });
            $base64Image=(string) $img->encode('data-url');
            $images .= '<img src="'.$base64Image.'" width="170px">';
        }
           $dompdf->loadHtml($images);
           $dompdf->render();
           return $dompdf->stream();
    }

    public function getSession(){
        if(RouterOsController::check_routeros_connection()){
            $session=RouterOsController::$API->comm('/tool/user-manager/session/print',array(
              '?user'=>'hima'
            ));
            return response()->json($session);
        }
    }

    public function sellCard(Request $request){
        $validator=Validator::make($request->all(),[
            'cards'=>'required',
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        if(count($request->cards)>1){
            for($i=0;$i<count($request->cards);$i++){
                $card=Card::find($request->cards[$i]);
                if(!$card->sell_status){
                    $card->sell_date=Carbon::now();
                    $card->sell_status=true;
                    $card->save();
                }else{
                    return ApiResponce::sendResponce(200,'Sorry The Card were sold before');
                }
            }
            return ApiResponce::sendResponce(200,'The Card were sold successfully');
        }else{
            $card=Card::find($request->cards[0]);
            if(!$card->sell_status){
                $card->sell_date=Carbon::now();
                $card->sell_status=true;
                $card->save();
                return ApiResponce::sendResponce(200,'The Card were sold successfully');
            }else{
                return ApiResponce::sendResponce(200,'Sorry The Card were sold before');
            }
        }
    }

    public function reportCards(){
        $cards=Card::whereBetween('sell_date',['2024-04-23 21:05:08','2024-04-24 12:00:32'])
        ->select('category_id',DB::raw('count(*) as card_count',))->groupBy('category_id')
        ->get();

        // $cards=Card::with(['category'=> function($q){
        //     $q->select('id','price');
        // }])->get();
        return ApiResponce::sendResponce(200,'Report About Cards',$cards);
    }

    public function changeStatusCard(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required',//requirder if
            'status'=>'required|boolean'
        ]);

        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());
        
            if(RouterOsController::check_routeros_connection()){
                if($request->status){
                    $test=RouterOsController::$API->comm('/tool/user-manager/user/enable',array(
                        'numbers'=>$request->id
                    ));
                }else{
                    $test=RouterOsController::$API->comm('/tool/user-manager/user/disable',array(
                        'numbers'=>$request->id
                    ));
                }

                return ApiResponce::sendResponce(200,'Status Of Card Changed');
            }
            ApiResponce::messageErorr();
    }
    public function getAllCardByUser(Request $request){
        $timeThreshold = Carbon::now()->subMinutes(30);
        $cards=Card::with('Category')->whereHas('Order.sellPoint.User',function($q) use($request){//write the id of the user related with sellpoint
            $q->where('id',$request->user()->id);
        })->latest()->get(); // Condition to check if sell date is greater than 30 minutes ago

        return ApiResponce::sendResponce(200,'Get All Cards By User (sell point)',$cards);
    }

    public function getLogsByCard($user){
        if(RouterOsController::check_routeros_connection()){
            $getlog = RouterOsController::$API->comm("/tool/user-manager/session/print", array(
                "?user" =>$user,
            ));
            $log = array_reverse($getlog);
            $TotalReg = count($getlog);
            if($TotalReg<1){
                return ApiResponce::sendResponce(404,'Not Any Logs');
            }
            return ApiResponce::sendResponce(200,'Logs Card',$log);
        }
    return ApiResponce::messageErorr();
    }

    public function activeUsersCards(){
        if(RouterOsController::check_routeros_connection()){
            $gethotspotactive = RouterOsController::$API->comm("/ip/hotspot/active/print");
            $TotalReg = count($gethotspotactive);
            return ApiResponce::sendResponce(200,'Active Users',[$TotalReg,$gethotspotactive]);
        }
        return ApiResponce::messageErorr();
    }
}
