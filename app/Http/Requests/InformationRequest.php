<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InformationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_name' => 'required|max:30',
            'cnpj_cpf' => 'required|max:14',
            'address' => 'required|max:100',
            'address_number' => 'required|max:10',
            'city' => 'required|max:30',
            'state' => 'required|max:2',
            'logo' => 'nullable'
        ];
    }
}
