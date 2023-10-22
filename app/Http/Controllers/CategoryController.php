<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();

        return response()->json($categories, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->all());

        if ($category) {
            return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $category], 201);
        } else {
            return response()->json(['message' => 'Erro ao salvar o registro'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $category = Category::find($category->id);

        return response()->json($category, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CategoryRequest  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->fill($request->all());

        if ($category->save()) {
            return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $category], 200);
        } else {
            return response()->json(['message' => 'Erro ao atualizar o registro'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        if ($category->delete()) {
            return response()->json(['message' => 'Registro excluído com sucesso'], 200);
        } else {
            return response()->json(['message' => 'Erro ao excluir o registro'], 500);
        }
    }
}
