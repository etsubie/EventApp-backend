<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class UserGrowthControlle extends Controller
{
    /**
     * Fetch user growth data for the chart.
     *
     * @return JsonResponse
     */
    public function getUserGrowthData(): JsonResponse
    {
        if (!Auth::check() || !Auth::user()->can('view users')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Fetch user growth data grouped by month
        $data = User::selectRaw('MONTHNAME(created_at) as month, COUNT(*) as users')
            ->groupBy('month')
            ->orderByRaw('MONTH(created_at)')
            ->get();

        return response()->json($data);
    }
}
