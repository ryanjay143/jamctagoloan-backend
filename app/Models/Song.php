<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = ['id', 'folder_id', 'title', 'artist', 'url', 'lyrics', 'chords', 'order'];
    public $incrementing = false;
    protected $keyType = 'string';
}
