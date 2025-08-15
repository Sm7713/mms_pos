<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnBill extends Model
{
    use HasFactory;

    protected $table='return_bills';

    public function sellPoint(){
        return $this->belongsTo(sellPoint::class);
    }

    public function returnPayment(){
        return $this->hasMany(ReturnPayment::class);
    }
}
