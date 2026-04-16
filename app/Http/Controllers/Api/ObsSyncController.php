<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Events\LyricsUpdated;

class ObsSyncController extends Controller
{
    public function update(Request $request)
    {
        $data = [
            'text' => $request->text ?? '',
            'fontSize' => $request->fontSize ?? 60,
            'background' => $request->background ?? 'none',
            'updatedAt' => now()->timestamp * 1000,
        ];

        // I-save gihapon sa Cache para sa initial load (fetch)
        Cache::put('obs_live_data', $data, 1440);

        // I-dispatch ang Event (Kini ang mo-trigger sa Reverb/Pusher)
        // Gikuha nato ang ->toOthers() para sigurado mo-send sa tanan!
        event(new LyricsUpdated($data));

        return response()->json([
            'ok' => true,
            'data' => $data
        ]);
    }

    public function latest()
    {
        return response()->json(
            Cache::get('obs_live_data', [
                'text' => '',
                'fontSize' => 60,
                'background' => 'none'
            ])
        );
    }
}