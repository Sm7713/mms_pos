<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    protected $table='subscribers';

    public function User(){
        return $this->belongsTo(User::class);
    }

    public function Category(){
        return $this->hasMany(Category::class);
    }
}
