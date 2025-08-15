<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\MyHelper\ApiResponce;
use Illuminate\Http\Request;

class PositionController extends Controller
{
   /**
     * Store a new position.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\MyHelper\ApiResponce 
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
        ]);

        $position = new Position();
        $position->city = $validatedData['city'];
        $position->street = $validatedData['street'];
        $position->zone = $validatedData['zone'];
        $position->save();

        return ApiResponce::sendResponce(201,'Position Created Successfully',$position);
    }

    /**
     * Update an existing position.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validatedData = $request->validate([
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
        ]);

        $position->city = $validatedData['city'];
        $position->street = $validatedData['street'];
        $position->zone = $validatedData['zone'];
        $position->save();

        return response()->json($position);
    }

    /**
     * Remove the specified position.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return response()->json(null, 204);
    }

    /**
     * Display a listing of all positions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $positions = Position::all();
        return response()->json($positions);
    }

    /**
     * Display the specified position.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $position = Position::findOrFail($id);
        return ApiResponce::sendResponce(200,'Retrived Position Successfully',$position);
    }
}
