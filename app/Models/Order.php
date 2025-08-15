<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $table='orders';

    public function Order_Details(){
        return $this->HasMany(orderDetails::class);
    }

    public function sellPoint(){
        return $this->belongsTo(sellPoint::class);
    }


    public function Card(){
        return $this->hasMany(Card::class);
    }

    public function Payment(){
        return $this->hasMany(Payment::class);
    }
}
