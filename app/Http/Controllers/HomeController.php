<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function show($id)
    {
        $vehicle = Vehicle::with('images', 'category', 'brand','optional')->find($id);
        $vehicle->load('images','optional');
        return response()->json(['vehicle' => $vehicle], 200);
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

    public function getBrands()
    {
        $brand = Brand::select('id', 'brand')->get();
        return response()->json($brand, 200);
    }

    public function getCategories()
    {
        $categories = Category::select('id', 'category')->get();
        return response()->json($categories, 200);
    }
    
    public function getFilteredVehicles(Request $request){
        $query = Vehicle::query();
        if ($request->has('brandId') && $request->input('brandId') != 0) {
            $query->where('brand_id', $request->input('brandId'));
        }

        if ($request->has('categoryId') && $request->input('categoryId') != 0) {
            $query->where('category_id', $request->input('categoryId'));
        }

        if ($request->has('priceFrom') && $request->input('priceFrom') != 0 && $request->has('priceTo') && $request->input('priceTo') != 0) {
            $query->whereBetween('price', [$request->input('priceFrom'), $request->input('priceTo')]);
        }

        if($request->has('yearFrom') && $request->input('yearFrom') != 0 && $request->has('yearTo') && $request->input('yearTo') != 0){
            $query->whereBetween('year', [$request->input('yearFrom'), $request->input('yearTo')]);
        }

        $vehicles = $query->with('images', 'category', 'brand','optional')->orderBy('created_at', 'desc')->paginate($request['itemPerPage'], ['*']);


        return response()->json($vehicles);
    }
}
