<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Information;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function show($id)
    {
        $vehicle = Vehicle::with('images', 'category', 'brand','optional')->find($id);
        $vehicle->load('images','optional');
        $contacts = Contact::orderBy('created_at', 'desc')->get();
        return response()->json(['vehicle' => $vehicle, 'contacts' => $contacts], 200);
    }

    public function filterValues()
    {
        $maxYear = Vehicle::max('year');
        $minYear = Vehicle::min('year');

        $maxPrice = Vehicle::max('price');
        $minPrice = Vehicle::min('price');

        $categories = Category::select('id', 'category')->get();
        $brands = Brand::select('id', 'brand')->get();

        $result = [
            'maxYear' => $maxYear,
            'minYear' => $minYear,
            'maxPrice' => $maxPrice,
            'minPrice' => $minPrice,
            'categories' => $categories,
            'brands' => $brands
        ];

        return response()->json($result, 200);
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
        $filter = request('searchFilter');
        if ($request->has('searchFilter')) {
            $query->where(function ($query) use ($filter) {
                $query->where('model', 'like', "%$filter%")
                      ->orWhereHas('category', function ($query) use ($filter) {
                          $query->where('category', 'like', "%$filter%");
                      })
                      ->orWhereHas('brand', function ($query) use ($filter) {
                          $query->where('brand', 'like', "%$filter%");
                      });
            });
        }


        $vehicles = $query->with('images', 'category', 'brand','optional')->orderBy('created_at', 'desc')->paginate($request['itemPerPage'], ['*']);


        return response()->json($vehicles);
    }

    public function companyInfo(){
        $info = Information::first();
        return response()->json($info, 200);
    }
}
