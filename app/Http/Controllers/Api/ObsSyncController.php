<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ObsSyncController extends Controller
{
    // POST /api/update
   public function update(Request $request)
    {
        $data = [
            'text' => $request->text,
            'fontSize' => $request->fontSize,
            'background' => $request->background,
            'updatedAt' => now()->timestamp * 1000,
        ];

        // I-save sa cache (memory)
        Cache::put('obs_live_data', $data, now()->addHours(12));

        return response()->json(['ok' => true]);
    }

    // GET: /api/obs/latest
    public function latest()
    {
        return response()->json(Cache::get('obs_live_data', []));
    }

    // GET: /obs-stream (Kini ang SSE link)
    public function stream()
    {
        return new StreamedResponse(function () {
            $lastId = null;

            while (true) {
                // Kon ang user mo-close sa OBS, undangon ang loop
                if (connection_aborted()) break;

                $data = Cache::get('obs_live_data');
                $currentId = $data['updatedAt'] ?? null;

                // I-send lang ang data kung naay bag-ong update
                if ($currentId !== $lastId) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastId = $currentId;
                }

                ob_flush();
                flush();
                usleep(300000); // Check matag 0.3 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Importante para sa Nginx/Forge
        ]);
    }
}