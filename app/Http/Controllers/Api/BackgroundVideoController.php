<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackgroundVideoController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:102400'],
        ]);

        $file = $validated['video'];
        $extension = $file->getClientOriginalExtension() ?: 'mp4';
        $filename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('background-videos', $filename, 'public');

        return response()->json([
            'ok' => true,
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'name' => $file->getClientOriginalName(),
        ]);
    }

    public function delete(Request $request)
    {
        $validated = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = ltrim($validated['path'], '/');

        if (!Str::startsWith($path, 'background-videos/')) {
            return response()->json(['message' => 'Invalid background video path.'], 422);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['ok' => true]);
    }
}
