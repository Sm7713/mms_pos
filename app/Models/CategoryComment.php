<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryComment extends Model
{
    use HasFactory;

    protected $table='category_comments';

    public function Comment(){
        return $this->hasMany(Comment::class);
    }
}
