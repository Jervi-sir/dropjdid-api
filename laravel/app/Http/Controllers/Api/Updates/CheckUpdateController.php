<?php

namespace App\Http\Controllers\Api\Updates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckUpdateController extends Controller
{
    /**
     * Check if a app update is required.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'version' => 'required|string',
            'platform' => 'required|string',
        ]);

        $currentVersion = $request->input('version');
        $platform = strtolower($request->input('platform'));

        // Let's assume the latest version is 1.0.0 for now, or we can read from config/env
        $latestVersion = env('LATEST_APP_VERSION', '1.0.0');
        
        // We require update if currentVersion is lower than latestVersion
        $updateRequired = version_compare($currentVersion, $latestVersion, '<');

        $storeUrl = null;
        if ($platform === 'ios') {
            $storeUrl = env('IOS_STORE_URL', 'https://apps.apple.com');
        } elseif ($platform === 'android') {
            $storeUrl = env('ANDROID_STORE_URL', 'https://play.google.com');
        }

        return response()->json([
            'update_required' => $updateRequired,
            'store_url' => $storeUrl,
            'latest_version' => $latestVersion,
            'force_update' => $updateRequired,
        ]);
    }
}
