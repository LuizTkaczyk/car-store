<?php

namespace App\Http\Controllers;

use App\Http\Requests\InformationRequest;
use App\Models\Contact;
use App\Models\Information;
use App\Services\FtpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InformationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\InformationRequest  $request
     * @return \Illuminate\Http\Response
     */

    protected $ftpService;


    public function __construct(FtpService $ftpService)
    {
        $this->ftpService = $ftpService;

    }

    public function store(InformationRequest $request)
    {
        if ($request->id) {
            return $this->update($request, Information::find($request->id));
        }
        DB::beginTransaction();
        try {
            if ($request->logo) {
                $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->logo));
                $imageName = uniqid() . '.png';
                Storage::disk('public')->put('logo/' . $imageName, $decodedImage);
                $logoUrl = 'logo/' . $imageName;
                $this->ftpService->saveLogoFtp($decodedImage);
            }else{
                $logoUrl = '';
            }

            $fieldsToFtp = [
                'company_name',
            ];

            $dataToPass = $request->only($fieldsToFtp);

            $this->ftpService->saveInfosFtp($dataToPass);

            $data = $request->only([
                'company_name',
                'cnpj_cpf',
                'address',
                'address_number',
                'city',
                'state',
                'company_phone',
            ]);

            $data['logo'] = $logoUrl;

            $information = Information::create($data);


            if (count($request->contact) > 0) {
                foreach ($request->contact as $contact) {
                    Contact::insert([
                        'name' => $contact['name'],
                        'phone' => $contact['phone'],
                        'information_id' => $information->id
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Informações salvas com sucesso', 'data' => $information], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao salvar informações', 'error' => $e->getTrace()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Information  $information
     * @return \Illuminate\Http\Response
     */
    public function show(Information $information)
    {
        $information->load('contacts');

        if ($information->logo) {
            $imagePath = str_replace('/', '\\', $information->logo);
            $fullPath = storage_path('app\public\\' . $imagePath);

            if (File::exists($fullPath)) {
                $imageData = Storage::disk('public')->get($information->logo);
                $base64Image = base64_encode($imageData);
                $information->logo = 'data:image/png;base64,' . $base64Image;
            }
        }

        return response()->json($information, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\InformationRequest  $request
     * @param  \App\Models\Information  $information
     * @return \Illuminate\Http\Response
     */
    public function update(InformationRequest $request, Information $information)
    {
        DB::beginTransaction();

        try {
            $logoUrl = null;
            $this->deleteLogo($information);

            if ($request->logo) {
                $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->logo));
                $imageName = uniqid() . '.png';
                Storage::disk('public')->put('logo/' . $imageName, $decodedImage);
                $logoUrl = 'logo/' . $imageName;
                $this->ftpService->saveLogoFtp($decodedImage);
            }else{
                $logoUrl = '';
            }

            $fieldsToFtp = [
                'company_name',
            ];

            $dataToPass = $request->only($fieldsToFtp);

            $this->ftpService->saveInfosFtp($dataToPass);

            $data = $request->only([
                'company_name',
                'cnpj_cpf',
                'address',
                'address_number',
                'city',
                'state',
                'company_phone',
            ]);

            $data['logo'] = $logoUrl;

            $information->update($data);

            if (count($request->contact) > 0) {
                Contact::where('information_id', $information->id)->delete();
                foreach ($request->contact as $contact) {
                    if (isset($contact['name']) && isset($contact['phone']) && $contact['name'] !== null && $contact['phone'] !== null) {
                        Contact::insert([
                            'name' => $contact['name'],
                            'phone' => $contact['phone'],
                            'information_id' => $information->id
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Informações atualizadas com sucesso', 'data' => $information], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar as informações', 'error' => $e->getMessage()], 500);
        }
    }

    private function deleteLogo(Information $information)
    {
        $imagePath = $information->logo;
        $fullPath = storage_path('app/public/' . $imagePath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
