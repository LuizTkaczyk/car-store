<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Http\Requests\VehicleRequest;
use App\Models\Images;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicles = Vehicle::with('images')->get();
       
        $vehicles->each(function ($vehicle) {
            $vehicle->images->each(function ($image) {
            $imagePath = str_replace('\\', '/', $image->file);

            // Verifica se o arquivo existe
            $fullPath = storage_path('app/public/' . $imagePath);
            if (File::exists($fullPath)) {
                    $imageData = Storage::disk('public')->get($image->file);
                    $base64Image = base64_encode($imageData);
                    $image->file = $base64Image;
                }
            });
        });

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
     * @param  \App\Http\Requests\VehicleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VehicleRequest $request)
    {
        $data = [
            'model' => $request->model,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'year' => $request->year,
            'price' => $request->price
        ];

        $vehicle = Vehicle::create($data);

        if (count($request->images) > 0) {
            foreach ($request->images as $base64Image) {
                $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                $imageName = uniqid() . '.png';
                Storage::disk('public')->put('vehicles/' . $imageName, $decodedImage);
                Images::create([
                    'file' => 'vehicles/' . $imageName,
                    'vehicle_id' => $vehicle->id, // Supondo que $vehicle seja o veículo recém-criado
                ]);
            }
        }

        if ($vehicle) {
            return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $vehicle], 201);
        } else {
            return response()->json(['message' => 'Erro ao salvar o registro'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\VehicleRequest  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        //
    }
}
