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

    // I-save gihapon sa Cache para sa mga mag-refresh nga page
    Cache::put('obs_live_data', $data, 1440);

    // I-dispatch ang Event (Kini ang mo-trigger sa Reverb/Pusher)
    event(new \App\Events\LyricsUpdated($data));

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
        // I-disable ang time limit aron dili maputol ang stream sa OBS
        set_time_limit(0);

        return new StreamedResponse(function () {
            $lastId = null;

            // Limpyohan ang buffer sa PHP para direkta ang pag-send sa data
            if (ob_get_level() > 0) ob_end_clean();

            while (true) {
                // Kon i-close ang OBS o ang Tab, undangon ang loop
                if (connection_aborted()) break;

                $data = Cache::get('obs_live_data');
                $currentId = $data['updatedAt'] ?? null;

                // I-send lang kung naay bag-ong update
                if ($currentId !== $lastId && $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastId = $currentId;
                }

                // Heartbeat para dili ma-timeout ang Nginx/Browser
                echo ": heartbeat\n\n";

                ob_flush();
                flush();

                // Hulaton ang 1 second una mo-check usab
                // Importante ni sa "php artisan serve" kay usa ra ka request iyang kaya dunganon
                sleep(1); 
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Gi-disable ang buffering para sa Nginx/Forge
        ]);
    }
}