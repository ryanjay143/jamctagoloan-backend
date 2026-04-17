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

        // Gamita ang forever para dili ma-expire ug walay overhead
        Cache::forever('obs_live_data', $data);

        return response()->json(['ok' => true]);
    }

    public function latest()
    {
        return response()->json(Cache::get('obs_live_data', [
            'text'       => '',
            'fontSize' => 60,
            'background' => 'none',
        ]));
    }

    public function stream()
    {
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        set_time_limit(0);

        $response = new StreamedResponse(function () {
            $lastId = null;

        while (true) {
            if (connection_aborted()) break;

            $data = Cache::get('obs_live_data');
            $currentId = $data['updatedAt'] ?? null;

            if ($currentId !== $lastId && $data) {
                echo "data: " . json_encode($data) . "\n\n";
                $lastId = $currentId;
                
                // Paspas nga flushing
                if (ob_get_level() > 0) ob_flush();
                flush();
            }

            // 🔥 10ms poll interval (10,000 microseconds)
            // usleep(10000); 
        }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);

        $response->setProtocolVersion('1.1');
        return $response;
    }
}