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
        $foldersData = $request->all();

        try {
            DB::transaction(function() use ($foldersData) {
                
                // 1. RULE: FOLDER DELETION VALIDATION
                $incomingFolderIds = collect($foldersData)->pluck('id')->filter()->toArray();
                
                $foldersToDelete = empty($incomingFolderIds) 
                    ? Folder::all() 
                    : Folder::whereNotIn('id', $incomingFolderIds)->get();

                foreach ($foldersToDelete as $folderToDelete) {
                    // I-check kung naa bay kanta sulod ani nga folder
                    if (Song::where('folder_id', $folderToDelete->id)->exists()) {
                        throw new \Exception("Cannot delete folder '{$folderToDelete->name}' because it still contains songs. Please empty it first.");
                    }
                    $folderToDelete->delete();
                }

                // 2. FOLDER & SONG SYNCING LOOP
                foreach($foldersData as $folderData) {
                    if (!isset($folderData['id']) || !isset($folderData['name'])) continue;

                    // Buhat o I-update ang folder
                    $folder = Folder::updateOrCreate(
                        ['id' => (string) $folderData['id']],
                        ['name' => $folderData['name']]
                    );

                    $songs = $folderData['songs'] ?? [];
                    $incomingSongIds = collect($songs)->pluck('id')->filter()->toArray();
                    
                    // I-delete ang mga kanta sa DB nga gitangtang ni User aning foldera
                    if (!empty($incomingSongIds)) {
                        Song::where('folder_id', $folder->id)->whereNotIn('id', $incomingSongIds)->delete();
                    } else {
                        Song::where('folder_id', $folder->id)->delete();
                    }

                    // I-save ang mga kanta (I-mintinar ang han-ay/order para sa Drag and Drop)
                    $seenUrlsInPayload = []; // Tig-detect if naay duplicate link gikan sa parehas nga request

                    foreach($songs as $index => $song) {
                        if (!isset($song['id']) || !isset($song['url'])) continue;

                        // 3. RULE: YOUTUBE LINK EXISTENCE VALIDATION
                        $isYoutubeLink = str_contains($song['url'], 'youtu');

                        if ($isYoutubeLink) {
                            // Check kung gi-add niya og kaduha sa usa ka request
                            if (in_array($song['url'], $seenUrlsInPayload)) {
                                throw new \Exception("Duplicate YouTube link detected for '{$song['title']}'.");
                            }
                            $seenUrlsInPayload[] = $song['url'];

                            // Check sa Database kung nag-exist na ba ni nga link sa maong folder
                            $existingSong = Song::where('folder_id', $folder->id)
                                                ->where('url', $song['url'])
                                                ->where('id', '!=', $song['id'])
                                                ->first();

                            if ($existingSong) {
                                throw new \Exception("The YouTube link for '{$song['title']}' already exists in the folder '{$folder->name}'.");
                            }
                        }

                        // Kung pasado tanan, i-save/update ang kanta
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
            // I-return ngadto sa frontend nga 400 Bad Request aron masabtan sa Axios nga naay error sa validation
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 400); 
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