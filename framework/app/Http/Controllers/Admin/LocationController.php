<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function getLocations()
    {
        // Query to get latitude and longitude for each user_id
        $locations = DB::table('usermeta')
            ->select(
                'user_id',
                DB::raw("MAX(CASE WHEN `key` = 'emsourcelat' THEN `value` END) AS latitude"),
                DB::raw("MAX(CASE WHEN `key` = 'emsourcelong' THEN `value` END) AS longitude")
            )
            ->groupBy('user_id')
            ->get();

        // Filter out results without latitude or longitude
        $filteredLocations = $locations->filter(function ($location) {
            return !empty($location->latitude) && !empty($location->longitude);
        });

        // Return as JSON
        return response()->json($filteredLocations);
    }
}
