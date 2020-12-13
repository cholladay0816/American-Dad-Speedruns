<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Billable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function speedruns()
    {
        return $this->hasMany(Speedrun::class);
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    public function abilities()
    {
        return $this->roles->map->abilities->flatten()->pluck('name')->unique();
    }
    public function hasAbility($ability)
    {
        return $this->abilities()->contains($ability);
    }
    public function banners()
    {
        return $this->belongsToMany(Banner::class);
    }
    public function uploadedBanners()
    {
        return $this->hasMany(Banner::class);
    }
    public function suspensions()
    {
        return $this->hasMany(Suspension::class);
    }
    public function isSuspended()
    {
        return $this->suspensions->count() > 0;
    }
}
