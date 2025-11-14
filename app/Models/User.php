<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Jika primary key di tabel user bukan 'id'
    protected $primaryKey = 'user_id';

    // Kalau tidak pakai timestamps di tabel users
    public $timestamps = false;

    protected $fillable = [
        'username',
        'full_name',
        'password',
         'email',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * Relasi ke ProjectMember
     */
    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke CardAssignment
     */
    public function assignments()
    {
        return $this->hasMany(CardAssignment::class, 'user_id', 'user_id');
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Scope untuk filter berdasarkan role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope untuk pencarian user
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('username', 'like', "%{$search}%")
              ->orWhere('full_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Check jika user adalah admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check jika user adalah manager
     */
    public function isManager()
    {
        return $this->role === 'manager';
    }

    /**
     * Check jika user adalah regular user
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Accessor untuk nama lengkap
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name ?: $this->username;
    }

    /**
     * Relasi ke projects melalui project members
     */
    public function projects()
    {
        return $this->hasManyThrough(
            Project::class,
            ProjectMember::class,
            'user_id', // Foreign key pada ProjectMember
            'project_id', // Foreign key pada Project
            'user_id', // Local key pada User
            'project_id' // Local key pada ProjectMember
        );
    }

    /**
     * Relasi ke cards melalui assignments
     */
    public function cards()
    {
        return $this->hasManyThrough(
            Card::class,
            CardAssignment::class,
            'user_id', // Foreign key pada CardAssignment
            'card_id', // Foreign key pada Card
            'user_id', // Local key pada User
            'card_id' // Local key pada CardAssignment
        );
    }
}