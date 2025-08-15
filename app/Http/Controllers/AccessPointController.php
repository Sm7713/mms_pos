<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\AccessPoint;
use App\MyHelper\ApiResponce;

class AccessPointController extends Controller
{
      // Add access point function
      public function addAccessPoint(Request $request)
      {
          // Validate incoming request data
          $validatedData = $request->validate([
              'name' => 'required|string|max:255',
              'ip_address'=>'required|string|max:20',
              'is_active' => 'required|boolean',
              'position_id' => 'required|integer',
          ]);
  
          // Create a new access point instance
          $accessPoint = new AccessPoint();
          $accessPoint->name = $validatedData['name'];
          $accessPoint->ip_address = $validatedData['ip_address'];
          $accessPoint->is_active = $validatedData['is_active'];
          $accessPoint->position_id = $validatedData['position_id'];
          
          // Save the access point
          $accessPoint->save();
  
          // Return a response
          return ApiResponce::sendResponce(201,'Access point added successfully');
      }

      public function updateAccessPoint(Request $request){

        $validatedData = $request->validate([
            'id'=>'required|integer|exists:'.AccessPoint::class,
            'name' => 'required|string|max:255',
            'ip_address'=>'required|string|max:20',
            'is_active' => 'required|boolean',
            'position_id' => 'required|integer',
        ]);

        // Create a new access point instance
        $accessPoint = AccessPoint::find($request->id);
        if(!$accessPoint){
            return ApiResponce::sendResponce(200,'The Access Point Not Found');
        }
        $accessPoint->name = $validatedData['name'];
        $accessPoint->ip_address = $validatedData['ip_address'];
        $accessPoint->is_active = $validatedData['is_active'];
        $accessPoint->position_id = $validatedData['position_id'];
        
        // Save the access point
        $accessPoint->save();

        // Return a response
        return ApiResponce::sendResponce(201,'Access point updated successfully');
      }
  
      // Delete access point function
      public function deleteAccessPoint($id)
      {
          // Find the access point by its ID
          $accessPoint = AccessPoint::findOrFail($id);
  
          // Delete the access point
          $accessPoint->delete();
  
          // Return a response
          return ApiResponce::sendResponce(200,'Access point deleted successfully');
      }

      public function getAccessPoint($id){
        $access=AccessPoint::find($id);
        if(!$access){
            return ApiResponce::sendResponce(200,'The Access Point Not Found');
        }
        return ApiResponce::sendResponce(200,'Access Point Retrived Successfully',$access);
      }
  
      // Show all access points function
      public function showAllAccessPoints()
      {
          // Retrieve all access points
          $accessPoints = AccessPoint::whereHas('Position',function($q){
            
          })->with('Position')->get();
  
          // Return a response with the access points
          return ApiResponce::sendResponce(200,'All Access Point',$accessPoints);
      }
}
