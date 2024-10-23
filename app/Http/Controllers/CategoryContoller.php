<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryContoller extends Controller
{
    public function index()
    {

            $categories = Category::all();

            return response()->json([
                'message' => 'Categories retrieved successfully',
                'categories' => $categories,
            ], 200);

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:categories|max:255',
        ]);

        if (Auth::user()->can('create events')) {
            $category = Category::create($data);

            return response()->json([
                'message' => 'Category created successfully',
                'category' => $category,
            ], 201);
        }

        return response()->json([
            'message' => 'Unauthorized',
        ], 403);
    }
}
