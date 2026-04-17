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
        try {
            $data = [
                'text' => $request->text ?? '',
                'fontSize' => $request->fontSize ?? 90,
                'background' => $request->background ?? 'none',
                'updatedAt' => now()->timestamp * 1000,
            ];

            // 1. I-save sa Cache
            Cache::put('obs_live_data', $data, 1440);

            // 2. I-broadcast sa Reverb (Kini ang posibleng hinungdan sa 500 kung sayop ang config)
            broadcast(new LyricsUpdated($data));

            return response()->json(['ok' => true, 'message' => 'Updated successfully']);
            
        } catch (\Exception $e) {
            // Kung naay error, i-report para makita nimo sa logs
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function latest()
    {
        return response()->json(Cache::get('obs_live_data', [
            'text' => '', 'fontSize' => 90, 'background' => 'none'
        ]));
    }

   public function stream()
{
    // 1. KINI ANG PINAKA-IMPORTANTE: I-release ang Session Lock.
    // Kung dili kini i-close, ang "/update" nga request maghulat nga mahuman ang stream loop.
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    // 2. Siguroa nga dili maputol ang connection sa PHP level
    set_time_limit(0);

    // 3. I-disable ang tanang buffering sa PHP
    if (function_exists('ob_end_flush')) {
        @ob_end_flush();
    }
    ob_implicit_flush(1);

    return new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
        $lastUpdate = null;
        $heartbeatTimer = time();

        // I-set ang initial data aron dili blangko sa sugod
        $data = \Illuminate\Support\Facades\Cache::get('obs_live_data');
        if ($data) {
            echo "data: " . json_encode($data) . "\n\n";
            $lastUpdate = $data['updatedAt'] ?? null;
            flush();
        }

        while (true) {
            // Susiha kung gi-close na sa OBS/Browser ang connection
            if (connection_aborted()) {
                break;
            }

            // Kuhaa ang pinakabag-ong data sa Cache
            $data = \Illuminate\Support\Facades\Cache::get('obs_live_data');
            $currentUpdate = $data['updatedAt'] ?? null;

            // I-send lang kung naay bag-ong "updatedAt" timestamp
            if ($currentUpdate !== $lastUpdate && $data) {
                echo "data: " . json_encode($data) . "\n\n";
                $lastUpdate = $currentUpdate;
                
                // Puwersahon ang pag-gawas sa data
                if (ob_get_level() > 0) ob_flush();
                flush();
            }

            // 4. HEARTBEAT: Pag-send og comment matag 15 segundos
            // Aron dili huna-hunaon sa Forge/Nginx nga "dead" na ang connection
            if (time() - $heartbeatTimer > 15) {
                echo ": heartbeat\n\n";
                $heartbeatTimer = time();
                if (ob_get_level() > 0) ob_flush();
                flush();
            }

            // 0.1 seconds interval (Paspas kaayo pero dili bug-at sa CPU)
            usleep(100000); 
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no', // Sultian ang Nginx/Forge nga ayaw gyud i-buffer
        'Access-Control-Allow-Origin' => '*', // Para walay CORS issue sa OBS
    ]);
}
}