<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ObsStateController extends Controller
{
    private $file;

    public function __construct()
    {
        $this->file = storage_path('app/obs_state.json');
    }

    private function defaultPayload(): array
    {
        return ['text' => '', 'fontSize' => 60, 'background' => 'none', 'updatedAt' => 0];
    }

    private function readPayload(): array
    {
        if (!file_exists($this->file)) {
            return $this->defaultPayload();
        }

        return json_decode(file_get_contents($this->file), true) ?: $this->defaultPayload();
    }

    public function show()
    {
        return response()
            ->json($this->readPayload())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function stream(): StreamedResponse
    {
        return response()->stream(function () {
            @ini_set('max_execution_time', '0');
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            ignore_user_abort(true);
            @set_time_limit(0);

            if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
                @session_write_close();
            }

            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }

            while (ob_get_level() > 0) {
                @ob_end_flush();
            }

            ob_implicit_flush(true);

            $lastUpdatedAt = 0;
            $lastHeartbeatAt = microtime(true);
            $initialPayload = $this->readPayload();

            echo "retry: 1000\n\n";
            echo 'event: obs-state' . "\n";
            echo 'data: ' . json_encode($initialPayload) . "\n\n";
            @flush();

            $lastUpdatedAt = (int) ($initialPayload['updatedAt'] ?? 0);

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $payload = $this->readPayload();
                $updatedAt = (int) ($payload['updatedAt'] ?? 0);
                $now = microtime(true);

                // Some XAMPP/PHP setups still enforce execution limits unless refreshed periodically.
                @set_time_limit(0);

                if ($updatedAt > $lastUpdatedAt) {
                    $lastUpdatedAt = $updatedAt;
                    echo 'event: obs-state' . "\n";
                    echo 'data: ' . json_encode($payload) . "\n\n";
                    @flush();
                    $lastHeartbeatAt = $now;
                }

                if (($now - $lastHeartbeatAt) >= 15) {
                    echo ": keep-alive\n\n";
                    @flush();
                    $lastHeartbeatAt = $now;
                }

                usleep(250000);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
            'Content-Encoding' => 'none',
        ]);
    }

    public function update(Request $request)
    {
        $current = file_exists($this->file)
            ? json_decode(file_get_contents($this->file), true) ?? []
            : [];

        $merged = array_merge($current, $request->all());
        $merged['updatedAt'] = (int) round(microtime(true) * 1000);

        file_put_contents($this->file, json_encode($merged));
        Cache::forever('obs_live_data', $merged);

        return response()->json(['ok' => true]);
    }
}
