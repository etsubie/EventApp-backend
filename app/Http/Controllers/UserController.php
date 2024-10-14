<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        // Check if the user has permission to view users
        if ($user->can('view users')) {
            $users = User::with('roles')->get();

            // Ensure the collection is properly transformed to array
            $data = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->pluck('name')->first() ?? 'No Role',
                ];
            })->toArray();  // Make sure to convert to array

            // Return data as an array, even if empty
            return response()->json($data, 200);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
