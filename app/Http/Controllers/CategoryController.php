<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all(['id', 'name', 'description', 'img_no']);
        
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $category = Category::updateOrCreate(
            ['id' => $request->input('category-id')], // Adjusted for manual ID if provided
            [
                'name' => $request->input('category-name'),
                'kitchen_process' => $request->input('kitchen-process'),
                'description' => $request->input('category-desc'),
                'img_no' => $request->input('category_img_no', 0)
            ]
        );

        return response()->json([
            'err' => 0,
            'msg' => 'Category saved successfully',
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        Category::destroy($id);
        
        return response()->json([
            'err' => 0,
            'msg' => 'Category removed'
        ]);
    }
}
