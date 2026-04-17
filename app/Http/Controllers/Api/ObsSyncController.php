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
        // 1. I-CLOSE ANG SESSION (Important for performance)
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // 2. DISABLE TIME LIMIT (Para dili maputol)
        set_time_limit(0);

        // 3. FORCE FLUSH (Para dili ma-buffer)
        if (function_exists('ob_end_flush')) {
            @ob_end_flush();
        }
        ob_implicit_flush(1);

        return new StreamedResponse(function () {
            $lastId = null;
            $heartbeatTimer = time(); //  Para sa heartbeat

            while (true) {
                // 4. CHECK FOR CONNECTION ABORT (Kung gi-close ang browser)
                if (connection_aborted()) {
                    break;
                }

                $data = Cache::get('obs_live_data');
                $currentId = $data['updatedAt'] ?? null;

                // 5. SEND DATA KUNG NAAY KAUSABAN
                if ($currentId !== $lastId && $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastId = $currentId;

                    // FORCE FLUSH (Para ma-send dayon)
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                // 6. HEARTBEAT (Para dili ma-disconnect ang SSE)
                if (time() - $heartbeatTimer > 15) { // Send a heartbeat every 15 seconds
                    echo ": heartbeat\n\n";
                    $heartbeatTimer = time();
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                // 7.  SMALL PAUSE (Para dili ma-overload ang server)
                usleep(100000); // 100 milliseconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable Nginx buffering
        ]);
    }
}