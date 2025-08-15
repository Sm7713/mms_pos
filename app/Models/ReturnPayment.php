<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnPayment extends Model
{
    use HasFactory;

    protected $table='return_payments';

    public function ReturnBill(){
        return $this->belongsTo(ReturnBill::class);
    }
}
