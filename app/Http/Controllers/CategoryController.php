<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController
{
    public function index()
    {
        $categories = Category::with('files')->get();
        
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $category = Category::updateOrCreate(
            ['id' => $request->input('id')], // Adjusted for manual ID if provided
            [
                'name' => $request->input('name'),
                'kitchen_process' => $request->input('kitchen_process'),
                'description' => $request->input('description')
            ]
        );

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $product->files()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'disk' => 'public',
                    'path' => $path
                ]);
            }
        }

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
