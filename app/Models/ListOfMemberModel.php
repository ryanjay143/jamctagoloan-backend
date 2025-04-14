<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListOfMemberModel extends Model
{
    protected $table = 'list_of_members';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'role',
        'photo',
        'attendance_status',
        'church_status'
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'member_id');
    }

    
}
