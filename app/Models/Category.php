<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table='categories';

    public function Cards(){
        return $this->hasMany(Card::class);
    }

    public function OrderDetails(){
        return $this->hasMany(orderDetails::class);
    }

    public function Subscriber(){
        return $this->belongsTo(Subscriber::class);
    }
}
