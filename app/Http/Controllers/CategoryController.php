<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Category;
use App\Models\Order;
use App\MyHelper\ApiResponce;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\MyHelper\UserGenerator;
use Illuminate\Routing\Route;

use function PHPUnit\Framework\returnSelf;

class CategoryController extends Controller
{
    private function checkConnection()
    {
        if (RouterOsController::check_routeros_connection()) {
            return true;
        }
        return false;
    }
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:' . Category::class,
            'validaty' => 'required|string',
            'price' => 'required|numeric',
            'type' => 'required|boolean',
            // 'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'capacity' => 'required|string',
            'uptime' => 'required|string',
            // 'rate_limit'=>'required|string'
        ]);

        if ($validator->fails()) return ApiResponce::sendResponce(404, null, $validator->errors());
        // return $request;
        if ($this->checkConnection()) {

            // $image = $request->file('img'); //getClientOriginalExtension
            // $image_name = hexdec(time()) . '.' . $image->getClientOriginalExtension();
            // $request->img->move(public_path('images'), $image_name);
            // $img_url = 'images/' . $image_name;

            $test = RouterOsController::$API->comm('/tool/user-manager/profile/add', array(
                'name' => $request->title,
                'price' => $request->price,
                'owner' => 'admin', //select option in html
                'starts-at' => 'logon', //select option html
                'validity' => $request->validaty
            ));
            if (is_array($test)) return ApiResponce::sendResponce(404, 'Category Duplicated', $test);
            $addlimit = RouterOsController::$API->comm(' /tool/user-manager/profile/limitation/add', array(
                'download-limit' => $request->capacity,
                'name' => strtolower($request->title . '_' . $request->capacity),
                'owner' => 'admin',
                'uptime-limit' => $request->uptime
            ));
            // return $addlimit;
            $limited = RouterOsController::$API->comm('/tool/user-manager/profile/profile-limitation/add', array(
                'limitation' => $addlimit,
                'profile' => $test
            ));

            if (is_string($test)) {
                $store = new Category;
                $store->u_id = $test;
                $store->title = $request->title;
                // $store->image = $img_url;
                $store->price = $request->price;
                $store->limited = $request->capacity;
                $store->type = $request->type;
                $store->l_id=$addlimit;
                $store->validity = $request->validaty;
                $store->save();

                return ApiResponce::sendResponce(200, 'The Category Saved Successfully', $store);
            } else {
                return ApiResponce::sendResponce(404, $test, null);
            }
        }
        return ApiResponce::messageErorr();
    }

    public function disableCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'u_id' => 'required|string|exists:categories,u_id', //from mikrotik
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) return ApiResponce::sendResponce(404, null, $validator->errors());

        $category = Category::where('u_id', $request->u_id)->first();
        if ($category) {
            $category->status = $request->status;
            $category->save();
            return ApiResponce::sendResponce(200, 'The Status Of Category Changed Successfully', $category);
        }
        return ApiResponce::sendResponce(404, 'This Category Not Found !!!');
    }
    public function EditCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'u_id' => 'required|string', //from mikrotik
            'title' => 'required', //|unique:Category
            'validaty' => 'required|string',
            'price' => 'required|numeric',
            'type' => 'required|boolean',
            // 'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'capacity' => 'required|string',
            'uptime' => 'required|string',
        ]);

        if ($validator->fails()) return ApiResponce::sendResponce(404, null, $validator->errors());

        if ($this->checkConnection()) {

            // $image = $request->file('img'); //getClientOriginalExtension
            // $image_name = hexdec(time()) . '.' . $image->getClientOriginalExtension();
            // $request->img->move(public_path('images'), $image_name);
            // $img_url = 'images/' . $image_name;

            $update = Category::where('u_id', $request->u_id)->first();

            $update->title = $request->title; //complete other attrebutes
            $update->price = $request->price;
            $update->validity = $request->validaty;
            // $update->image = $img_url;
            $update->limited = $request->capacity;
            $update->type = $request->type;
            $update->save();

            if ($update) {
                // 
                $test = RouterOsController::$API->comm('/tool/user-manager/profile/set', array(
                    'numbers' => $request->u_id,
                    'name' => $request->title,
                    'price' => $request->price,
                    'owner' => 'admin', //select option in html
                    'starts-at' => 'logon', //select option html
                    'validity' => $request->validaty
                ));
                // return $test;
                // if ($test==null) return ApiResponce::sendResponce(404, 'Category Duplicated', $test);
                $addlimit = RouterOsController::$API->comm(' /tool/user-manager/profile/limitation/set', array(
                    'numbers' => $update->l_id,
                    'download-limit' => $request->capacity,
                    'name' => strtolower($request->title . '_' . $request->capacity),
                    'owner' => 'admin',
                    'uptime-limit' => $request->uptime
                ));
                // return $addlimit;
                // $limited = RouterOsController::$API->comm('/tool/user-manager/profile/profile-limitation/set', array(
                //     'limitation' => $addlimit,
                //     // 'profile' => $test
                // ));
            }

            if ($test == null) {
                return ApiResponce::sendResponce(200, 'The Category Updated Successfully', $update);
            }

            return ApiResponce::sendResponce(404, null, $test);
        }
        return ApiResponce::messageErorr();
    }

    //delete category
    public function DeleteCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:categories,u_id', //from mikrotik
        ]);

        if ($validator->fails()) return ApiResponce::sendResponce(404, null, $validator->errors());
        try {
            if ($this->checkConnection()) {
                $category = Category::where('u_id', $request->id)->first();

                if ($category) {
                    $test = RouterOsController::$API->comm('/tool/user-manager/profile/remove', array(
                        '.id' => $request->id,
                    ));
                    $category->delete();
                    return ApiResponce::sendResponce(200, 'The Category Deleted Successfully');
                }
                return ApiResponce::sendResponce(404, 'Not Found The Category Here');
            }
            return ApiResponce::messageErorr();
        } catch (Exception $e) {
            return ApiResponce::sendResponce(404, 'Error Fech Out of the range ' . $e->getMessage());
        }
    }

    public function ShowCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:categories,u_id', //from mikrotik
        ]);

        if ($validator->fails()) return ApiResponce::sendResponce(404, null, $validator->errors());

        $category = Category::where('u_id', $request->id)->first();

        if ($this->checkConnection()) {
            // $category=RouterOsController::$API->comm('/tool/user-manager/profile/print',array(
            //     '?.id'=>$request->id
            // ))[0];
            // $limitation=RouterOsController::$API->comm('/tool/user-manager/profile/profile-limitation/print',array(
            //     '?profile'=>$category->title
            // ))[0];
            // $limit=RouterOsController::$API->comm('/tool/user-manager/profile/limitation/print',array(
            //     '?name'=>$limitation['limitation']
            // ))[0];
            // $data=[
            //     $limitation,
            //     $limit
            // ];
            // $data=array_merge($limitation,$limit);
            // $combined=array_merge(array_filter($limitation,function($key,$value) use ($limit){
            //     return in_array($value,array_column($limit,'limitation'));
            // },ARRAY_FILTER_USE_BOTH),$limit);
            return ApiResponce::sendResponce(200, 'get A Specific Category', $category);
        }
        return ApiResponce::messageErorr();
    }

    public function getCategoriesDB()
    {
        $categories = Category::all()->where('status', 1);
        return ApiResponce::sendResponce(200, 'All Categories Retrived Successfully', $categories);
    }

    public function getCategoriesforAdmin()
    {
        $categories = Category::all();
        return ApiResponce::sendResponce(200, 'All Categories Retrived Successfully', $categories);
    }

    public function test_create_cards(Request $request)
    {
        if ($this->checkConnection()) {
            $validator = Validator::make($request->all(), [
                'number_of_cards' => 'required', //add uppcase and lowercase and numbers choose one or all of them
                'category_id' => 'required'
            ]);

            if ($validator->fails()) return response()->json($validator->errors(), 404);
            for ($i = 0; $i < 10; $i++) {
                $username = UserGenerator::randLC(4);
                $password = UserGenerator::randLC(4);
                $test = RouterOsController::$API->comm('/tool/user-manager/user/add', array(
                    'customer' => 'admin',
                    'username' => $username,
                    'password' => $password
                ));
                if (is_string($test)) {
                    $store = new Card;
                    $store->id = $test;
                    $store->username = $username;
                    $store->password = $password;
                    $store->category_id = $request->category_id;
                    // add the Relationship between cards and category 
                    $store->save();
                    //complete the code in this section
                }
                return response()->json($test);
            }
        } else {
            return ApiResponce::messageErorr();
        }
    }

    //change to public after testing    $length
    public function generateRandomString($qun, $cate, $order_id)
    {
        $category = Category::find($cate);
        $order = Order::find($order_id);
        try {
            if (RouterOsController::check_routeros_connection()) {
                for ($i = 0; $i < $qun; $i++) {
                    $username = UserGenerator::randN(8); //write the lentgh from vue.js
                    $u_id = RouterOsController::$API->comm('/tool/user-manager/user/add', array(
                        'customer' => 'admin',
                        'username' => $username
                    ));

                    $c = RouterOsController::$API->comm('/tool/user-manager/user/create-and-activate-profile', array(
                        'customer' => 'admin',
                        'profile' => $category->u_id, //category id in mikrotik
                        '.id' => $u_id
                    ));
                    $card = new Card();
                    $card->u_id = $u_id;
                    $card->username = $username;
                    $card->category()->associate($category);
                    $card->Order()->associate($order);
                    $card->save();
                }
                return ApiResponce::sendResponce(200, 'Created Card Successfully', $card);
            }
            return ApiResponce::messageErorr();
        } catch (Exception $e) {
            return ApiResponce::sendResponce(200, 'Some Erorres', $e->getMessage());
        }
        // for($i=0;$i<$qun;$i++){
        //     $store=new Card();
        //     $store->u_id=Str::random(2);
        //     $store->username=UserGenerator::randN(10);
        //     // $store->password=UserGenerator::randN(10);
        //     $store->category()->associate($cate);
        //     $store->Order()->associate($order);
        //     $store->save();
        //     // $store->category_id=$cate;
        //     // $store->order_id=$order_id;
        //     // $store->save();   
        // }

    }
}
