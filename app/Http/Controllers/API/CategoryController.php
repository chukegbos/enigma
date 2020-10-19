<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Inventory;
use App\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('api');
    }


    public function index(Request $request)
    {
        $query =  Category::where('deleted_at', NULL);
        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        $category = $query->latest()->paginate(10);
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        return Category::create([
            'name' => ucwords($request->name),
        ]);


    }

    public function show($id)
    {
        $category =  Category::where('deleted_at', NULL)->find($id);
        if ($category) {
            return response()->json($category);
        }
        return ['error' => 'Not found'];
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $category = Category::where('deleted_at', NULL)->find($id);
        $category->name = $request->name;
       
        if($category->save()){
            return ['success'=> 'Data updated successfully'];
        }else{
            return ['error' => 'Opps something went wrong'];
        }
    }

    public function destroy($id)
    {
        $category = Category::where('deleted_at', NULL)->find($id);
        $category->delete();
    }
}
