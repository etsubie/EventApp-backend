<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
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
    public function getEventsByCategory($category)
    {
        $events = Event::whereHas('category', function ($query) use ($category) {
            $query->where('name', $category);
        })
        ->whereHas('approvals', function ($query) {
            $query->where('action', 'approved');
        })
        ->orderBy('created_at', 'desc') // Order by latest
        ->get();
    
        if ($events->isEmpty()) {
            return response()->json(['message' => 'No events found for this category.'], 404);
        }
    
        return response()->json($events);
    }
    

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:categories|max:255',
        ]);

        if (Auth::user()->can('manage events')) {
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
