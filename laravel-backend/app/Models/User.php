<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'unique_id', 'name', 'email', 'password',
        'phone', 'address', 'city', 'country', 'postal_code', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function generateUniqueId(): string
    {
        do {
            $uniqueId = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }
}
