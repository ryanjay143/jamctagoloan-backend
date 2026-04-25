<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PptPresentation extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slides_count',
        'uploaded_at',
        'thumbnail_url',
        'source_text',
        'slide_data',
        'template_id',
        'background_image_url',
        'source_type',
        'original_file_name',
    ];
}
