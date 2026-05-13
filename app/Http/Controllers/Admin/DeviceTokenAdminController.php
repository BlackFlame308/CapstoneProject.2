<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Household;
use Illuminate\Http\Request;

class DeviceTokenAdminController extends Controller
{
    /**
     * List all device tokens
     */
    public function index(Request $request)
    {
        $query = DeviceToken::with('household');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('household', function($q) use ($search) {
                $q->where('household_code', 'like', "%{$search}%")
                  ->orWhere('household_name', 'like', "%{$search}%");
            })->orWhere('player_id', 'like', "%{$search}%");
        }

        // Filter by last active
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('logged_at', '>=', now()->subHour());
            } elseif ($request->status === 'inactive') {
                $query->where('logged_at', '<', now()->subHour());
            }
        }

        $tokens = $query->latest('logged_at')->paginate(20)->withQueryString();

        return view('admin.device-tokens.index', [
            'tokens' => $tokens,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show device token details
     */
    public function show(DeviceToken $deviceToken)
    {
        $deviceToken->load('household');

        return view('admin.device-tokens.show', [
            'token' => $deviceToken,
        ]);
    }

    /**
     * Delete device token
     */
    public function destroy(DeviceToken $deviceToken)
    {
        try {
            $playerId = $deviceToken->player_id;
            $deviceToken->delete();

            return redirect()->route('admin.device-tokens.index')
                ->with('success', "Device token deleted successfully!");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete device token. ' . $e->getMessage());
        }
    }

    /**
     * Export device tokens (for mobile app sync)
     */
    public function export()
    {
        $tokens = DeviceToken::with('household')->get();

        return response()->json([
            'success' => true,
            'count' => $tokens->count(),
            'data' => $tokens->map(function($token) {
                return [
                    'player_id' => $token->player_id,
                    'household_id' => $token->household_id,
                    'battery_level' => $token->battery_level,
                    'signal_strength' => $token->signal_strength,
                    'last_active' => $token->logged_at,
                ];
            }),
        ]);
    }
}
