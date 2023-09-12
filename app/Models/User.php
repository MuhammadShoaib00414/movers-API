<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $username = 'username';

    // public function createToken($name = null, array $abilities = ['*'])
    // {
    //     return $this->tokens()->create([
    //         'name' => $name, // Use the provided name for the token
    //         'username' => $this->username, // Use the username as the token name
    //         'abilities' => $abilities,
    //     ]);
    // }


    protected $fillable = [

        'email',
        'password',
        'username',
        'phone_number',
        'otp_code',
        'first_name',
        'last_name',
        'profile_image',
        'user_type'
    ];

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
}
