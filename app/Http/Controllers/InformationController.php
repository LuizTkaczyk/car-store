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

    public function saveInformation(InformationRequest $request)
    {
        if ($request->id) {
            return $this->update($request, Information::find($request->id));
        }
        DB::beginTransaction();
        try {
            if ($request->logo) {
                $logoUrl = $this->saveLogo($request->logo);
            }else{
                $defaultLogoPath = 'defaultImage/logo-default.png';
                $base64Image = base64_encode(Storage::disk('public')->get($defaultLogoPath));
                $this->saveLogo($base64Image);
                $logoUrl = Storage::url('defaultImage/logo-default.png');
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

            $data['logo'] = Storage::url($logoUrl);

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
    public function getInformation()
    {
        $information = Information::first();
        if($information){
            $information->load('contacts');
            return response()->json($information, 200);
        }
        return response()->json(['message' => 'Informações não encontradas'], 200);
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

            if ($request->logo && $request->logo != $information->logo) {
                $this->deleteLogo($information);
                $logoUrl = $this->saveLogo($request->logo);
            }else{
                if(!$request->logo){
                    $this->deleteLogo($information);
                    $defaultLogoPath = 'defaultImage/logo-default.png';
                    $base64Image = base64_encode(Storage::disk('public')->get($defaultLogoPath));
                    $logoUrl = $this->saveLogo($base64Image);
                }else{
                    $logoUrl = $request->logo ? $information->logo : Storage::url('defaultImage/logo-default.png');
                }
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
        $previousLogoPath = 'logo/' . basename(Storage::url($information->logo));

        if (Storage::disk('public')->exists($previousLogoPath)) {
            Storage::disk('public')->delete($previousLogoPath);
            return true;
        } else {
            return false;
        }
    }

    private function saveLogo($base64image){
        $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64image));
        $imageName = uniqid() . '.png';
        Storage::disk('public')->put('logo/' . $imageName, $decodedImage);
        $logoUrl = 'logo/' . $imageName;
        $this->ftpService->saveLogoFtp($decodedImage);
        $logoUrl = Storage::url($logoUrl);

        return $logoUrl;
    }
}
