<?php
namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryContoller extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        if (!Auth::user()->can('create events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Create a new category
        $category = Categories::create([
            'name' => $request->name,
        ]);

        // Return a response
        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }
}
