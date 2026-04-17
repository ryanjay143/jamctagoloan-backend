<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Events\LyricsUpdated;
use Illuminate\Support\Facades\Log; // Import the Log facade

class ObsSyncController extends Controller
{
    public function update(Request $request)
    {
        try {
            // 1. Data Validation (Optional pero mas maayo)
            $request->validate([
                'text' => 'nullable|string',
                'fontSize' => 'nullable|integer|min:10|max:200',
                'background' => 'nullable|string',
            ]);

            // 2. Prepare Data (Kuhaa ang data gikan sa request)
            $data = [
                'text' => $request->input('text', ''),
                'fontSize' => $request->input('fontSize', 90), // Default 90
                'background' => $request->input('background', 'none'), // Default 'none'
                'updatedAt' => now()->timestamp * 1000, // Milliseconds (para sa JS)
            ];

            // 3. Save to Cache
            Cache::put('obs_live_data', $data, 1440); // 24 hours

            // 4. Fire the Reverb Event (Pag-broadcast sa Reverb event)
            $event = new LyricsUpdated($data);
            broadcast($event)->toOthers();

            // 5. Response (Successful update)
            return response()->json([
                'ok' => true,
                'message' => 'Lyrics updated successfully',
                'data' => $data, // Para sa debugging, pwede nimong i-return ang data.
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error handling (Validation error)
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'error' => 'Validation failed',
                'details' => $e->errors(),
            ], 422); // 422 Unprocessable Entity

        } catch (\Exception $e) {
            // 6. Error Handling (Catch all exceptions)
            // Log the detailed error (Pinaka-importante)
            Log::error('Error updating lyrics: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString()); // Full stack trace

            // Response sa API
            return response()->json([
                'ok' => false,
                'error' => 'Internal Server Error',
                'details' => $e->getMessage(), // Ipakita ang detalye sa error para sa debugging
            ], 500); // 500 Internal Server Error
        }
    }

    public function latest()
    {
        try {
            // Get data from cache
            $data = Cache::get('obs_live_data', [
                'text' => '',
                'fontSize' => 90,
                'background' => 'none',
            ]);

            // Return the data
            return response()->json([
                'ok' => true,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            // Error logging (Kung mag-error ang get sa cache)
            Log::error('Error getting latest lyrics: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'error' => 'Failed to retrieve latest lyrics',
            ], 500);
        }
    }

     public function stream()
    {
        // I-close ang session para dili ma-block ang update requests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // 1. Dili mag-set og execution time limit
        set_time_limit(0);

        // 2. Clear PHP Buffering (Important for SSE)
        if (function_exists('ob_end_flush')) {
            @ob_end_flush();
        }
        ob_implicit_flush(1);

        // 3. Create the StreamedResponse
        return new StreamedResponse(function () {
            $lastUpdate = null;
            $heartbeatTimer = time();

            // Initial Data
            $data = Cache::get('obs_live_data', [
                'text' => '',
                'fontSize' => 90,
                'background' => 'none',
            ]);

            if ($data) {
                echo "data: " . json_encode($data) . "\n\n";
                $lastUpdate = $data['updatedAt'] ?? null;
                flush();
            }

            // SSE Loop
            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $data = Cache::get('obs_live_data');
                $currentUpdate = $data['updatedAt'] ?? null;

                if ($currentUpdate !== $lastUpdate && $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    $lastUpdate = $currentUpdate;
                    if (ob_get_level() > 0) ob_flush(); // Force Flush
                    flush(); // Force Send
                }

                // Heartbeat (para dili ma-disconnect)
                if (time() - $heartbeatTimer > 15) {
                    echo ": heartbeat\n\n";
                    $heartbeatTimer = time();
                    if (ob_get_level() > 0) ob_flush();
                    flush();
                }

                // Pahuway og gamay
                usleep(100000); // 0.1 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // importante sa Nginx
        ]);
    }
}