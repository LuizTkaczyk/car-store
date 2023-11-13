<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Brand::select('id', 'brand')->get();

        return response()->json($categories, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\BrandRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BrandRequest $request)
    {
        $brand = Brand::create($request->all());

        if ($brand) {
            return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $brand], 201);
        } else {
            return response()->json(['message' => 'Erro ao salvar o registro'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        $brand = Brand::find($brand->id);

        return response()->json($brand, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\BrandRequest  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(BrandRequest $request, Brand $brand)
    {
        $brand->fill($request->all());

        if ($brand->save()) {
            return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $brand], 200);
        } else {
            return response()->json(['message' => 'Erro ao atualizar o registro'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        if ($brand->delete()) {
            return response()->json(['message' => 'Registro excluído com sucesso'], 200);
        } else {
            return response()->json(['message' => 'Erro ao excluir o registro'], 500);
        }
    }
}
