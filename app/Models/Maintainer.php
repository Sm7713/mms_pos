<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintainer extends Model
{
    use HasFactory;

    protected $table='maintainers';

    public function User(){
        return $this->belongsTo(User::class);
    }
}
