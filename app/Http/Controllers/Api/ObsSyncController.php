<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Events\LyricsUpdated; // I-siguro nga naa ni

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

        Cache::put('obs_live_data', $data, 1440);

        // I-trigger ang Event para sa Laravel Reverb/Echo
        event(new LyricsUpdated($data));

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
        set_time_limit(0);

        return new StreamedResponse(function () {
            $lastId = null;
            if (ob_get_level() > 0) ob_end_clean();

            while (true) {
                if (connection_aborted()) break;

                $data = Cache::get('obs_live_data');
                $currentId = $data['updatedAt'] ?? null;

                if ($currentId !== $lastId && $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastId = $currentId;
                }

                echo ": heartbeat\n\n";
                ob_flush();
                flush();
                sleep(1); 
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}