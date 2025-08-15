<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table='positions';

    public function AccessPoint(){
        return $this->hasMany(Position::class);
    }

    public function sellPoint(){
        return $this->hasMany(sellPoint::class);
    }
}
