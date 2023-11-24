<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicles = Vehicle::with('images', 'category', 'brand','optional')->orderBy('created_at', 'desc')->get();
        return response()->json($vehicles, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vehicle = Vehicle::with('images', 'category', 'brand','optional')->find($id);
        $vehicle->load('images','optional');
        return response()->json(['vehicle' => $vehicle], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function yearAndPrice()
    {
        $maxYear = Vehicle::max('year');
        $minYear = Vehicle::min('year');
        
        $maxPrice = Vehicle::max('price');
        $minPrice = Vehicle::min('price');

        $result = [
            'maxYear' => $maxYear,
            'minYear' => $minYear,
            'maxPrice' => $maxPrice,
            'minPrice' => $minPrice,
        ];

        return response()->json($result, 200);
    }

    public function changeBrand(Request $request){
        $vehicles = Vehicle::with('images', 'category', 'brand','optional')->where('brand_id', $request['brandId'])->get();
        return response()->json($vehicles, 200);
    }

    public function changeCategory(Request $request){
        Log::debug($request->all());
    }


    public function changeYear(Request $request){
        Log::debug($request->all());
    }

    public function changePrice(Request $request){
        Log::debug($request->all());
    }
}
