<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PptPresentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PptPresentationController extends Controller
{
    public function index()
    {
        $presentations = PptPresentation::orderByDesc('uploaded_at')->get();

        $data = $presentations->map(fn ($p) => [
            'id'                => $p->id,
            'name'              => $p->name,
            'slidesCount'       => $p->slides_count,
            'uploadedAt'        => $p->uploaded_at,
            'thumbnailUrl'      => $p->thumbnail_url,
            'sourceText'        => $p->source_text,
            'slideData'         => $p->slide_data,
            'templateId'        => $p->template_id,
            'backgroundImageUrl' => $p->background_image_url,
            'sourceType'        => $p->source_type,
            'originalFileName'  => $p->original_file_name,
        ]);

        return response()->json($data);
    }

    public function sync(Request $request)
    {
        $items = $request->all();

        if (!is_array($items)) {
            return response()->json(['message' => 'Invalid payload.', 'status' => 'error'], 400);
        }

        try {
            DB::transaction(function () use ($items) {
                $incomingIds = collect($items)->pluck('id')->filter()->toArray();

                if (empty($incomingIds)) {
                    PptPresentation::query()->delete();
                } else {
                    PptPresentation::whereNotIn('id', $incomingIds)->delete();
                }

                foreach ($items as $item) {
                    if (empty($item['id'])) continue;

                    PptPresentation::updateOrCreate(
                        ['id' => (string) $item['id']],
                        [
                            'name'                => $item['name'] ?? 'Untitled',
                            'slides_count'        => $item['slidesCount'] ?? 0,
                            'uploaded_at'         => $item['uploadedAt'] ?? now()->toISOString(),
                            'thumbnail_url'       => $item['thumbnailUrl'] ?? null,
                            'source_text'         => $item['sourceText'] ?? null,
                            'slide_data'          => $item['slideData'] ?? null,
                            'template_id'         => $item['templateId'] ?? null,
                            'background_image_url' => $item['backgroundImageUrl'] ?? null,
                            'source_type'         => $item['sourceType'] ?? null,
                            'original_file_name'  => $item['originalFileName'] ?? null,
                        ]
                    );
                }
            });

            return response()->json(['message' => 'Synced successfully.', 'status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
