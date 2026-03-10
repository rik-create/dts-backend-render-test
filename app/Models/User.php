<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'user_role_id',
        'office_id',
        'first_name',
        'last_name',
        'middle_name',
        'display_name',
        'email',
        'username',
        'password',
        'contact_number',
        'is_active',
        'has_admin_access',
        'is_superuser',
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
            'is_active' => 'boolean',
            'has_admin_access' => 'boolean',
            'is_superuser' => 'boolean',
        ];
    }

    // --- RELATIONSHIPS ---

    /**
     * Get the primary role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    /**
     * Get the office the user is assigned to.
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * The groups that belong to the user.
     */
    public function groups()
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_members', 'user_id', 'user_group_id')
            ->withTimestamps();
    }
}
