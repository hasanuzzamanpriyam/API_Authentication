<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Admin extends Authenticatable implements JWTSubject
{

    use HasFactory, SoftDeletes, HasRoles;


    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'otp',
        'is_otp_verified',
        'otp_expires_at',
        'status',
    ];
    protected $hidden = [
        'password',
        'otp',
    ];


    /**
     * Get the identifier that will be stored in the JWT token.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return an array with custom claims to be added to the JWT token.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
