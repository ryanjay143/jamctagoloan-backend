<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ObsStateController extends Controller
{
    public function show()
    {
        $state = Cache::get('obs_state', [
            'text' => '',
            'fontSize' => 60,
            'background' => 'none',
            'updatedAt' => 0,
        ]);

        return response()->json($state);
    }

    public function update(Request $request)
    {
        $current = Cache::get('obs_state', []);
        $merged = array_merge($current, $request->all());
        Cache::forever('obs_state', $merged);

        return response()->json(['ok' => true]);
    }
}
