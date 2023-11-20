<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Http\Requests\VehicleRequest;
use App\Models\Images;
use App\Models\Optional;
use App\Models\VehicleHasOptional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function index(Request $request)
    {
        $perPage = request('itemPerPage', 10);
        $vehicles = Vehicle::with(['images', 'category', 'brand', 'optional'])->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $request->page);
        return response()->json($vehicles, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\VehicleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VehicleRequest $request){
        DB::beginTransaction();
        try {
            $data = [
                'model' => $request->model,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'year' => $request->year,
                'price' => $request->price,
                'description' => $request->description
            ];

            $vehicle = Vehicle::create($data);

            if (count($request->images) > 0) {
                foreach ($request->images as $base64Image) {
                    
                    $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image['file']));
                    $imageName = uniqid() . '.png';
                    $path = 'vehicles/'. $vehicle->id . '/' . $imageName;
                    Storage::disk('public')->put($path, $decodedImage);
                    Images::create([
                        'file' =>  $imageName,
                        'url' => Storage::url($path),
                        'vehicle_id' => $vehicle->id,
                    ]);
                   
                }
            }else{
                Images::create([
                    'file' =>  'defaultImage',
                    'url' => Storage::url('defaultImage/default.png'),
                    'vehicle_id' => $vehicle->id,
                ]);
            }

            if (count($request->optional) > 0) {
                foreach ($request->optional as $optional) {
                    if(!isset($optional['optional'])) {
                        continue;
                    };
                    $option = Optional::create([
                        'name' => $optional['optional'],
                    ]);

                    VehicleHasOptional::insert([
                        'vehicle_id' => $vehicle->id,
                        'optional_id' => $option->id
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $vehicle], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getTrace()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle){
        $vehicle->load('images','optional');
        return response()->json($vehicle, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\VehicleRequest  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(VehicleRequest $request, Vehicle $vehicle){
        DB::beginTransaction();
        try {
            $data = [
                'model' => $request->model,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'year' => $request->year,
                'price' => $request->price,
                'description' => $request->description
            ];

            $vehicle->update($data);
            if (count($request->images)) {
                foreach ($request->images as $base64Image) {
                    Images::where('vehicle_id', $vehicle->id)->where('file', 'defaultImage')->delete();
                    if(!array_key_exists('url', $base64Image)) {
                       
                        $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image['file']));
                        $imageName = uniqid() . '.png';
                        $path = 'vehicles/'. $vehicle->id . '/' . $imageName;
                      
                        Storage::disk('public')->put($path, $decodedImage);
                        
                        Images::create([
                            'file' =>  $imageName,
                            'url' => Storage::url($path),
                            'vehicle_id' => $vehicle->id, // Supondo que $vehicle seja o veículo recém-criado
                        ]);
                     
                    };
                }
            }else{
                Images::create([
                    'file' =>  'defaultImage',
                    'url' => Storage::url('defaultImage/default.png'),
                    'vehicle_id' => $vehicle->id,
                ]);
            }

            if(isset($request->imagesToDelete) && count($request->imagesToDelete)){
                foreach($request->imagesToDelete as $image){
                    $this->deleteImagesByName($vehicle->id, $image['file']);
                }
            }

            if (count($request->optional) > 0) {
                VehicleHasOptional::where('vehicle_id', $vehicle->id)->delete();
                foreach ($request->optional as $optional) {
                    if(!isset($optional['optional'])) {
                        continue;
                    };
                    $option = Optional::create([
                        'name' => $optional['optional'],
                    ]);

                    VehicleHasOptional::insert([
                        'vehicle_id' => $vehicle->id,
                        'optional_id' => $option->id
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $vehicle->load('optional')], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar o registro', 'error' =>  $e->getTrace()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle){
        DB::beginTransaction();
        try {
            $this->deleteImageDir($vehicle);
            optional($vehicle->optional)->each->delete();
            $vehicle->delete();

            DB::commit();
            return response()->json(['message' => 'Registro excluído com sucesso'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao excluir o registro', 'error' => $e->getTrace()], 500);
        }
    }

    public function deleteImageDir(Vehicle $vehicle){
        $path = 'vehicles/'. $vehicle->id;
        $fullPath = storage_path('app/public/' . $path);
       
        if (File::exists($fullPath)) {
            File::deleteDirectory($fullPath);
        }

        Images::where('vehicle_id', $vehicle->id)->delete();
    }

    public function deleteImagesByName($vehicleId, $imageName){
       
        $path = 'vehicles/'. $vehicleId . '/' . $imageName;
        $fullPath = storage_path('app/public/' . $path);
       
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
        
        Images::where('vehicle_id', $vehicleId)->where('file', $imageName)->delete();

    }
}
