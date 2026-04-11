<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['id', 'name'];
    public $incrementing = false; // Important kay string atong ID
    protected $keyType = 'string';

    public function songs() {
        return $this->hasMany(Song::class)->orderBy('order');
    }
}
