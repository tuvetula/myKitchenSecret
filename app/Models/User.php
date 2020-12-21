<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($user) {
            Log::channel(config('logging.channels.user.name'))->info('New user has been created',[
                'id'=>$user->id,
                'email' => $user->email
            ]);
        });
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'first_name',
        'email',
        'password',
        'is_admin',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean'
    ];


    /**
     * Mutator - Hash password before saving user model.
     * @param $password
     */
    public function setPasswordAttribute($password)
    {
        if($password)
        {
            $this->attributes['password'] = bcrypt($password);
        }
    }

    /**
     * Mutator - strToLower and ucfirst name
     * @param $name
     */
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = ucfirst(strtolower($name));
    }

    /**
     * Mutator - strToLower and ucfirst name
     * @param $first_name
     */
    public function setFirstNameAttribute($first_name)
    {
        $this->attributes['first_name'] = ucfirst(strtolower($first_name));
    }

    //SETTER
    /**
     * Set the token value for the "remember me" session.
     * @param string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Set the email_verified_at
     * @param $value
     * @return void
     */
    public function setEmailVerifiedAt($value)
    {
        $this->email_verified_at = $value;
    }

    /**
     * Set password
     * @param $value
     * @return void
     */
    public function setPassword($value)
    {
        $this->password = $value;
    }

    // GETTERS
    /**
     * Get remember_token
     * @return string|null
     */
    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    /**
     * Get email_verified_at
     * @return mixed
     */
    public function getEmailVerifiedAt()
    {
        return $this->email_verified_at;
    }
}
