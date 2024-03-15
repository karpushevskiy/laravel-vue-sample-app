<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\ModelFilters\UserFilter;
use App\Models\Traits\CanGetTableNameStatically;
use App\Models\Traits\HasOrderScope;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\WelcomeNotification;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * @mixin \Eloquent
 * @package \App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes, Filterable, HasOrderScope, CanGetTableNameStatically;

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // timestamp
        'last_seen_at'      => 'datetime', // timestamp
        'is_online'         => 'boolean',
        'is_super_admin'    => 'boolean',
        'is_admin'          => 'boolean',
        'is_client'         => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'full_name',
        'primary_role_name',
        'primary_role_id',
        'is_online',
        'is_super_admin',
        'is_admin',
        'is_client',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function firstName() : Attribute
    {
        return Attribute::make(
            get: fn($value) => trim(ucfirst($value)),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function lastName() : Attribute
    {
        return Attribute::make(
            get: fn($value) => trim(ucfirst($value)),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function fullName() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => trim(ucfirst($attributes['first_name']) . ' ' . ucfirst($attributes['last_name'])),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function primaryRole() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => optional($this->roles()->orderBy('id')->first()) ?? null,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function primaryRoleName() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => optional($this->roles()->orderBy('id')->first())->name ?? null,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function primaryRoleHumanName() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => optional($this->roles()->orderBy('id')->first())->human_name ?? null,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function primaryRoleId() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => optional($this->roles()->orderBy('id')->first())->id ?? null,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function isOnline() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 3,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function isSuperAdmin() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->hasRole(config('permission.project_roles.super_admin')),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function isAdmin() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->hasRole(config('permission.project_roles.admin')),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function isClient() : Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->hasRole(config('permission.project_roles.client')),
        );
    }

    /**
     * @return string|null
     */
    public function modelFilter() : ?string
    {
        return $this->provideFilter(UserFilter::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Define User Notifications
    |--------------------------------------------------------------------------
    */
    /**
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token) : void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return void
     */
    public function sendEmailVerificationNotification() : void
    {
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * @return void
     */
    public function sendWelcomeNotification() : void
    {
        $this->notify(new WelcomeNotification());
    }

    /*
    |--------------------------------------------------------------------------
    | Define Model Relations
    |--------------------------------------------------------------------------
    */
}
