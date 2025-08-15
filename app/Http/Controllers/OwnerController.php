<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\sellPoint;
use App\MyHelper\ApiResponce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    // Add Owner function
    public function addOwner(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'f_name' => 'required|string',
            'm_name' => 'required|string',
            'l_name' => 'required|string',
            'email' => 'required|email|unique:'.Owner::class,
            'phone' => 'required',
            'personal_card' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());
        // Validate the request data

        $image=$request->file('personal_card');//getClientOriginalExtension
        $image_name=hexdec(time()).'.'.$image->getClientOriginalExtension();
        $request->personal_card->move(public_path('images'),$image_name);
        $img_url='images/'.$image_name;

        // Create a new owner record
        $owner = new Owner();
        $owner->f_name = $request->input('f_name');
        $owner->m_name = $request->input('m_name');
        $owner->l_name = $request->input('l_name');
        $owner->email = $request->input('email');
        $owner->phone_1 = $request->input('phone');
        // Save the personal card image

        $owner->personal_card=$img_url;
        $owner->save();

        return ApiResponce::sendResponce(201,'Owner added successfully',$owner);
    }

    // Get all owners function
    public function getAllOwners()
    {
        $owners = Owner::all();

        return ApiResponce::sendResponce(200,'All Owners',$owners);
    }

    // Get determined owner function
    public function getOwner($id)
    {
        $owner = Owner::find($id);

        if (!$owner) {
            return ApiResponce::sendResponce(404,'Owner not found');
        }

        return ApiResponce::sendResponce(200,'Owner Retrived Successfully',$owner);
    }

    // Delete owner function
    public function deleteOwner($id)
    {
        $owner = Owner::find($id);

        if (!$owner) {
            return response()->json(['message' => 'Owner not found'], 404);
        }

        $owner->delete();

        return response()->json(['message' => 'Owner deleted successfully']);
    }

    // Update owner function
    public function updateOwner(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.Owner::class,
            'f_name' => 'required|string',
            'm_name' => 'required|string',
            'l_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'personal_card' => 'image',
        ]);
        
        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $owner = Owner::find($request->id);

        if (!$owner) {
            return ApiResponce::sendResponce(404,'Owner not found');
        }

        // Update the owner record
        if ($request->has('f_name')) {
            $owner->f_name = $request->input('f_name');
        }
        if ($request->has('m_name')) {
            $owner->m_name = $request->input('m_name');
        }
        if ($request->has('l_name')) {
            $owner->l_name = $request->input('l_name');
        }
        if ($request->has('email')) {
            $owner->email = $request->input('email');
        }
        if ($request->has('phone')) {
            $owner->phone = $request->input('phone');
        }
        if ($request->hasFile('personal_card')) {
            // Save the new personal card image
            $image=$request->file('personal_card');//getClientOriginalExtension
            $image_name=hexdec(time()).'.'.$image->getClientOriginalExtension();
            $request->img->move(public_path('images'),$image_name);
            $img_url='images/'.$image_name;
    
            $owner->img=$img_url;
        }
        $owner->save();

        return response()->json(['message' => 'Owner updated successfully']);
    }

    public function sellPointWithOwner(Request $request){
        $validator=Validator::make($request->all(),[
            'id'=>'required|integer|exists:'.Owner::class,
        ]);
        
        if($validator->fails()) return ApiResponce::sendResponce(404,null,$validator->errors());

        $sellPoints=sellPoint::with('User')->whereHas('Owner',function($q) use ($request){
            $q->where('id',$request->id);
        })->get();

        if(count($sellPoints)==0){
            return ApiResponce::sendResponce(200,'Not Found Any sell Point in This Owner',null);
        }
        return ApiResponce::sendResponce(200,'Sell Points Retrived Successfully',$sellPoints);
    }
}
