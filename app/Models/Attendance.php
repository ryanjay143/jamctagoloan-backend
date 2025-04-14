<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    protected $fillable = [
        'member_id',
        'status'
    ];

    public function member()
    {
        return $this->belongsTo(ListOfMemberModel::class, 'member_id');
    }
}
