<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tithes extends Model
{
    protected $table = 'tithes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'member_id', 
        'type', 
        'amount', 
        'payment_method', 
        'notes',
        'date_created',
    ];

    public function member()
    {
        return $this->belongsTo(ListOfMemberModel::class, 'member_id', 'id');
    }
}
