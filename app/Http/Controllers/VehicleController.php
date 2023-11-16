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
        $vehicles = Vehicle::with(['images', 'category', 'brand', 'optional'])->paginate($perPage, ['*'], 'page', $request->page);
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
                $vehicleFolder = $vehicle->id;
                foreach ($request->images as $base64Image) {
                    $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image['file']));
                    $imageName = uniqid() . '.png';
                    if (!Storage::disk('google')->exists($vehicleFolder)) {
                        Storage::disk('google')->makeDirectory($vehicleFolder);
                    }

                    $dir = '/';
                    $recursive = false; // Get subdirectories also?
                    $contents = collect(Storage::disk('google')->listContents($dir, $recursive));

                    $dir = $contents->where('type', '=', 'dir')
                        ->where('filename', '=', $vehicleFolder)
                        ->first(); // There could be duplicate directory names!

                    Storage::disk('google')->put($dir['path'] . '/' .  $imageName, $decodedImage);
                    $url = Storage::disk('google')->url($dir['path'] . '/' . $imageName);

                    Images::create([
                        'file' => $imageName,
                        'url' =>  $url,
                        'vehicle_id' => $vehicle->id,
                    ]);
                }
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
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle){
        $vehicle->load('images'); // Carrega as imagens relacionadas ao veículo

        $vehicle->images->each(function ($image) {
            $imagePath = str_replace('\\', '/', $image->file);
            $fullPath = storage_path('app/public/' . $imagePath);

            if (File::exists($fullPath)) {
                $imageData = Storage::disk('public')->get($image->file);
                $base64Image = base64_encode($imageData);
                $image->file = 'data:image/png;base64,' . $base64Image;
            }
        });

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
                    if(!array_key_exists('url', $base64Image)) {
                        $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image['file']));
                        $imageName = uniqid() . '.png';
                       
                        $dir = '/';
                        $recursive = false;
                        $contents = collect(Storage::disk('google')->listContents($dir, $recursive));
                        
                        $dir = $contents->where('type', '=', 'dir')
                            ->where('filename', '=', $vehicle->id)
                            ->first();

                        Storage::disk('google')->put($dir['path'].'/'.$imageName, $decodedImage);
                        $url = Storage::disk('google')->url($dir['path'] . '/' . $imageName);
                       
                        Images::create([
                            'file' => $imageName,
                            'url' =>  $url,
                            'vehicle_id' => $vehicle->id,
                        ]);
                     
                    };
                }
            }

            if(isset($request->imagesToDelete) && count($request->imagesToDelete)){
                foreach($request->imagesToDelete as $image){
                    if(!array_key_exists('url', $image)) {
                        continue;
                    };
                    $this->deleteImagesByName($vehicle->id, $image['file']);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $vehicle], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar o registro', 'error' => $e->getMessage()], 500);
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
            $this->deleteImages($vehicle);
            optional($vehicle->optional)->each->delete();
            $vehicle->delete();

            DB::commit();
            return response()->json(['message' => 'Registro excluído com sucesso'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao excluir o registro', 'error' => $e->getTrace()], 500);
        }
    }

    public function deleteImages(Vehicle $vehicle){
        $directoryName = $vehicle->id;
        $dir = '/';
        $recursive = true; 
        $contents = collect(Storage::disk('google')->listContents($dir, $recursive));

        $directory = $contents
            ->where('type', '=', 'dir')
            ->where('filename', '=', $directoryName)
            ->first(); 

         Storage::disk('google')->deleteDirectory($directory['path']);

        Images::where('vehicle_id', $vehicle->id)->delete();
    }

    public function deleteImagesByName($vehicleId, $imageName){

        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect(Storage::disk('google')->listContents($dir, $recursive));

        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($imageName, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($imageName, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        Storage::disk('google')->delete($file['path']);
        Images::where('vehicle_id', $vehicleId)->where('file', $imageName)->delete();
    }
}
