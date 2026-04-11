<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    public function index()
    {
        $folders = Folder::with(['songs' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->get();
        
        $data = $folders->map(function($f) {
            return [
                'id' => (string) $f->id,
                'name' => $f->name,
                'songs' => $f->songs->map(function($s) {
                    return [
                        'id' => (string) $s->id,
                        'title' => $s->title,
                        'artist' => $s->artist ?? 'Unknown Artist',
                        'url' => $s->url,
                        'lyrics' => $s->lyrics ?? '',
                        'chords' => $s->chords ?? '',
                    ];
                })->values()
            ];
        });

        return response()->json($data);
    }

    public function sync(Request $request)
    {
        // I-kuha ang raw data gikan sa Axios
        $foldersData = $request->all();

        try {
            DB::transaction(function() use ($foldersData) {
                // Kuhaon ang tanang folder ID gikan sa Frontend
                $incomingFolderIds = collect($foldersData)->pluck('id')->filter()->toArray();
                
                // I-delete sa DB kadtong mga folder nga WALA na sa Frontend (Gi-delete ni User)
                if (!empty($incomingFolderIds)) {
                    Folder::whereNotIn('id', $incomingFolderIds)->delete();
                } else {
                    Folder::query()->delete(); // Kung empty ang array gikan sa frontend, i-delete tanan
                }

                // I-loop ang matag folder nga nadawat
                foreach($foldersData as $folderData) {
                    if (!isset($folderData['id']) || !isset($folderData['name'])) continue;

                    // Buhat o I-update ang folder
                    $folder = Folder::updateOrCreate(
                        ['id' => (string) $folderData['id']],
                        ['name' => $folderData['name']]
                    );

                    // Kuhaon ang mga songs sulod aning foldera
                    $songs = $folderData['songs'] ?? [];
                    $incomingSongIds = collect($songs)->pluck('id')->filter()->toArray();
                    
                    // I-delete ang mga kanta sa DB nga gitangtang ni User aning foldera
                    if (!empty($incomingSongIds)) {
                        Song::where('folder_id', $folder->id)->whereNotIn('id', $incomingSongIds)->delete();
                    } else {
                        Song::where('folder_id', $folder->id)->delete();
                    }

                    // I-save ang mga kanta (I-mintinar ang han-ay/order para sa Drag and Drop)
                    foreach($songs as $index => $song) {
                        if (!isset($song['id']) || !isset($song['url'])) continue;

                        Song::updateOrCreate(
                            ['id' => (string) $song['id']],
                            [
                                'folder_id' => $folder->id,
                                'title' => $song['title'] ?? 'Unknown Title',
                                'artist' => $song['artist'] ?? 'Unknown Artist',
                                'url' => $song['url'],
                                'lyrics' => $song['lyrics'] ?? '',
                                'chords' => $song['chords'] ?? '',
                                'order' => $index
                            ]
                        );
                    }
                }
            });

            return response()->json(['message' => 'Synced successfully', 'status' => 'success'], 200);

        } catch (\Exception $e) {
            // I-return ang EXACT nga error aron makita nato sa console unsa ang guba
            return response()->json([
                'message' => 'Error syncing data', 
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function upload(Request $request) {
        $request->validate([
            'audio' => 'required|mimes:mp3,wav,ogg|max:30000', // max 30MB
            'folder_id' => 'required',
            'title' => 'required'
        ]);

        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $path = $file->store('songs', 'public'); 
            
            $song = \App\Models\Song::create([
                'id' => (string) str_replace('.', '', microtime(true)),
                'folder_id' => $request->folder_id,
                'title' => $request->title,
                'artist' => 'Local Upload',
                'url' => asset('storage/' . $path), 
                'file_path' => $path,
                'lyrics' => '',
                'chords' => '',
                'order' => 0
            ]);

            return response()->json($song);
        }
    }
}