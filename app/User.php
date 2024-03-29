<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'role_type_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->hasOne('App\MetadataUserRoleType', 'id', 'role_type_id');
    }

    public function findForPassport($username)
    {
        return $this->where('phone_number', $username)->orWhere('email', $username)->first();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        $admin_role = MetadataUserRoleType::where(['slug' => 'admin'])->first();
        if ($this->role_type_id === $admin_role->id) {
            return true;
        }
        return false;
    }
}
