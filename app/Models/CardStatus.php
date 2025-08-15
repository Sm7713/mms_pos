<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'status'
    ];


    public function Cards(){
        return $this->hasMany(Card::class);
    }
}
