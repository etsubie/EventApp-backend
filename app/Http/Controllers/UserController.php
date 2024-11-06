<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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

            // the collection is properly transformed to array
            $data = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->pluck('name')->first() ?? 'No Role',
                ];
            })->toArray();
            // Return data as an array, even if empty
            return response()->json($data, 200);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
    public function show($id)
    {
        $fetchedUser = User::findOrFail($id);
        return response()->json($fetchedUser, 200);
    }
    public function getAuthenticatedUser(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Get the roles assigned to the user
        $roles = $user->getRoleNames();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'profile_picture' => $user->profile_picture,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "sometimes|max:255",
            "email" => "sometimes|email|unique:users,email," . $id,
            "password" => "sometimes",
            "role" => "sometimes|in:host,attendee"
        ]);
        $updatedUser = User::findOrFail($id);

        // Only assign the role if it's provided in the request
        if (isset($data['role'])) {
            $updatedUser->assignRole($data['role']);
        }
        // Only update the password if it is provided
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            // Remove password from the data array to prevent updating it
            unset($data['password']);
        }
        // Update the user's attributes if they are provided
        $updatedUser->fill($data);
        $updatedUser->save();

        return response()->json($updatedUser, 200);
    }

    public function destroy(Request $request, $id)
    {
        $dUser = User::findOrFail($id);
        $dUser->delete();
        return response()->json(['message' => 'user deleted successfully']);
    }
}
