<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DenominationDetails extends Model
{
    protected $table = 'denomination_details';
    protected $primaryKey = 'id';

    protected $fillable = [
        'den_id', 
        '1000', 
        '500',
        '200',	
        '100',	
        '50',	
        '20',	
        '10',	
        '5',	
        '1'
    ];
}
