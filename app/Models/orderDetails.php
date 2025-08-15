<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orderDetails extends Model
{
    use HasFactory;
    protected $table='order_details';

    public function Order(){
        return $this->belongsTo(Order::class);
    }

    public function Category(){
        return $this->belongsTo(Category::class);
    }
}
