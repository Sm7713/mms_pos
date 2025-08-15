<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sellPoint extends Model
{
    use HasFactory;

    protected $table='sell_points';

    public function User(){
        return $this->belongsTo(User::class);
    }

    public function Orders(){
        return $this->hasMany(Order::class);
    }

    public function Payment(){
        return $this->hasMany(Payment::class);
    }

    public function returnBills(){
        return $this->hasMany(ReturnBill::class);
    }

    public function Position(){
        return $this->belongsTo(Position::class);
    }

    public function Owner(){
        return $this->belongsTo(Owner::class);
    }
}
