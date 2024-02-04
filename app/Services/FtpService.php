<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class FtpService
{
    public function saveLogoFtp($base64Image)
    {
        $img = Image::make($base64Image);

        $img->trim('transparent', ['top', 'bottom', 'left', 'right']);

        $imageConverted = $img->encode('png')->encoded;

        $savedLogo = Storage::disk('ftp')->put('assets/logo/logo.png', $imageConverted);

        if(!$savedLogo) {
            return response()->json(['message' => 'Erro ao atualizar as informações', 'error' => 'Erro ao salvar imagem'], 500);
        }
    }

    public function saveInfosFtp($dataInfos){

        $infos = [
            'title' => $dataInfos['company_name'],
        ];

        $jsonData = json_encode($infos, JSON_PRETTY_PRINT);

        $savedInfos = Storage::disk('ftp')->put('assets/infos/info.json', $jsonData);

        if(!$savedInfos) {
            return response()->json(['message' => 'Erro ao atualizar as informações', 'error' => 'Erro ao salvar informações'], 500);
        }
    }
}
