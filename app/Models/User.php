<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

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
        // Note: profile_img column exists only in students table, not users table
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

    /**
     * Validate that Spatie role exists in database
     * Never auto-creates roles - only validates existence
     * 
     * @param string $roleName
     * @return bool
     */
    public static function validateSpatieRoleExists(string $roleName): bool
    {
        try {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
            
            if (!$role) {
                Log::critical('Role misconfiguration detected: Spatie role missing', [
                    'role_name' => $roleName,
                    'context' => 'Role validation',
                ]);
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::critical('Role misconfiguration detected: Exception during role validation', [
                'role_name' => $roleName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Ensure user has the correct Spatie role assigned based on their role attribute
     * Validates role exists before assignment
     */
    public function ensureSpatieRole(): void
    {
        if (!$this->role instanceof UserRole) {
            Log::critical('Role misconfiguration detected: Invalid role type', [
                'user_id' => $this->user_id,
                'email' => $this->email,
                'role_value' => $this->role,
            ]);
            abort(500, 'System role misconfigured');
        }

        $spatieRoleName = $this->role->spatieRole();
        
        // Validate role exists - never auto-create
        if (!self::validateSpatieRoleExists($spatieRoleName)) {
            abort(500, 'System role misconfigured');
        }

        // Assign role if not already assigned
        if (!$this->hasRole($spatieRoleName)) {
            $this->assignRole($spatieRoleName);
        }
    }

    /**
     * Get the name of the unique identifier for the user.
     * This ensures Sanctum uses 'user_id' instead of 'id'
     */
    public function getAuthIdentifierName(): string
    {
        return 'user_id';
    }

    /**
     * Get the unique identifier for the user.
     * This ensures Sanctum uses 'user_id' instead of 'id'
     */
    public function getAuthIdentifier()
    {
        return $this->user_id;
    }
}
