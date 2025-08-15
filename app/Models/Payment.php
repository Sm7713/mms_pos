<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table='payments';

    public function sellPoint(){
        return $this->belongsTo(sellPoint::class);
    }

    public function Order(){
        return $this->belongsTo(Order::class);
    }
}
