<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ObsStateController extends Controller
{
    private $file;

    public function __construct()
    {
        $this->file = storage_path('app/obs_state.json');
    }

    public function show()
    {
        if (file_exists($this->file)) {
            return response()->json(json_decode(file_get_contents($this->file), true));
        }
        return response()->json(['text' => '', 'fontSize' => 60, 'background' => 'none', 'updatedAt' => 0]);
    }

    public function update(Request $request)
    {
        $current = file_exists($this->file) 
            ? json_decode(file_get_contents($this->file), true) ?? [] 
            : [];
        $merged = array_merge($current, $request->all());
        file_put_contents($this->file, json_encode($merged));
        return response()->json(['ok' => true]);
    }
}
