<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // I-save ang data
        Cache::put('obs_live_data', $data, 1440);

        return response()->json(['ok' => true]);
    }

    public function latest()
    {
        return response()->json(Cache::get('obs_live_data', [
            'text' => '', 'fontSize' => 60, 'background' => 'none'
        ]));
    }

    public function stream()
    {
        // Walay time limit, aron magsige og stream
        set_time_limit(0);
        
        // Paspasan ang pag-send sa data
        if (function_exists('ob_implicit_flush')) {
            ob_implicit_flush(1);
        }

        return new StreamedResponse(function () {
            $lastId = null;

            while (true) {
                if (connection_aborted()) break;

                $data = Cache::get('obs_live_data');
                $currentId = $data['updatedAt'] ?? null;

                if ($currentId !== $lastId && $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastId = $currentId;
                    
                    // Puwersahon ang PHP nga i-send dayon sa browser
                    if (ob_get_level() > 0) ob_flush();
                    flush();
                }

                // Gamay nga interval para dili mag-lag ang server (0.1 seconds delay = INSTANT reaction)
                usleep(100000); 
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable Nginx buffer para instant sa Forge
        ]);
    }
}