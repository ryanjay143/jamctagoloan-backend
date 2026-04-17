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
            'text'       => $request->text ?? '',
            'fontSize'   => $request->fontSize ?? 60,
            'background' => $request->background ?? 'none',
            'updatedAt'  => now()->timestamp * 1000,
        ];

        Cache::put('obs_live_data', $data, 1440);

        return response()->json(['ok' => true]);
    }

    public function latest()
    {
        return response()->json(Cache::get('obs_live_data', [
            'text'       => '',
            'fontSize'   => 60,
            'background' => 'none',
        ]));
    }

    public function stream()
    {
        // Close session early for performance
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        set_time_limit(0);

        if (function_exists('ob_end_flush')) {
            @ob_end_flush();
        }
        ob_implicit_flush(1);

        $response = new StreamedResponse(function () {
            $lastId         = null;
            $heartbeatTimer = time();

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $data      = Cache::get('obs_live_data');
                $currentId = $data['updatedAt'] ?? null;

                if ($currentId !== $lastId && $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastId = $currentId;

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                // Heartbeat every 15 seconds to keep SSE alive
                if (time() - $heartbeatTimer > 15) {
                    echo ": heartbeat\n\n";
                    $heartbeatTimer = time();

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                usleep(100000); // 100ms poll interval
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);

        // Force HTTP/1.1 — fixes ERR_HTTP2_PROTOCOL_ERROR on SSE streams
        $response->setProtocolVersion('1.1');

        return $response;
    }
}