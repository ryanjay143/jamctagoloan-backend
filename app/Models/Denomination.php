<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denomination extends Model
{
    protected $table = 'denomination';
    protected $primaryKey = 'id';

    protected $fillable = [
        'total_amount',
    ];
}
