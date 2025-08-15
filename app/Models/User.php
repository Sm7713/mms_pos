<?php

namespace App\Models;

use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    protected $table ='users';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function sellPoint(){
        return $this->hasOne(sellPoint::class);
    }

    public function setting(){
        return $this->hasOne(Setting::class);
    }

    public function Commant(){
        return $this->hasMany(Comment::class);
    }

    public function Maintainer(){
        return $this->hasOne(Maintainer::class);
    }

    public function Subscriber(){
        return $this->hasOne(Subscriber::class);
    }
}
