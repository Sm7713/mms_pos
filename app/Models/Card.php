<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    
    protected $table='cards';

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function status(){
        return $this->hasOne(CardStatus::class);
    }

    public function Order(){
        return $this->belongsTo(Order::class);
    }
}
