<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'firstname',
        'lastname',
        'mobile',
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
            // Password is NOT auto-hashed here to support legacy plain text passwords
            // Hashing is handled in the AuthController
            'role' => UserRole::class,
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }


    /**
     * Check if user is admin (using direct attribute)
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if user is instructor (using direct attribute)
     */
    public function isInstructor(): bool
    {
        return $this->role === UserRole::INSTRUCTOR;
    }

    /**
     * Get role label
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->role?->label() ?? 'Unknown';
    }

    /**
     * Check if user is a special user (from config)
     */
    public function isSpecialUser(): bool
    {
        $specialUsers = config('roles.special_users', []);
        return isset($specialUsers[$this->email]);
    }

    /**
     * Get special user redirect route
     */
    public function getSpecialUserRedirectRoute(): ?string
    {
        if (!$this->isSpecialUser()) {
            return null;
        }

        $specialUsers = config('roles.special_users', []);
        return $specialUsers[$this->email]['redirect_route'] ?? null;
    }
}
