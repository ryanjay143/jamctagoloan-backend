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

        // 1. I-save sa Cache (para sa initial load)
        Cache::put('obs_live_data', $data, 1440);

        // 2. 🔥 KINI ANG IMPORTANTE: I-broadcast ang event sa Reverb!
        // Gamita ang ShouldBroadcastNow para dili na moagi sa Queue (instant)
        broadcast(new \App\Events\LyricsUpdated($data))->toOthers();

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
        // I-close ang session para dili ma-block ang ubang requests (/update)
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        set_time_limit(0);

        if (function_exists('ob_end_flush')) {
        @ob_end_flush();
    }
    ob_implicit_flush(1);
        
        return new StreamedResponse(function () {
            $lastId = null;
            while (true) {
                if (connection_aborted()) break;

                $data = Cache::get('obs_live_data');
                $currentId = $data['updatedAt'] ?? null;

                if ($currentId !== $lastId && $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastId = $currentId;
                    flush();
                }
                usleep(100000); // 0.1s
            }
        }, 200, [
        'Content-Type' => 'text/event-stream',
        'X-Accel-Buffering' => 'no', // Sultian ang Nginx/Forge nga ayaw i-buffer
        'Cache-Control' => 'no-cache',
    ]);
    }
}