<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const MODULES = ['ad-spend', 'pnl', 'notes'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function moduleAccess()
    {
        return $this->hasMany(ModuleAccess::class);
    }

    /**
     * Kya is user ko is module ka access hai.
     * Admin ko hamesha sab kuch milta hai, ye check unpar lagta hi nahi.
     */
    public function hasModuleAccess(string $module): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->moduleAccess()->where('module', $module)->exists();
    }
}