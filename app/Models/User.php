<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'biometric_id',
        'date_of_birth',
        'sp',
        'upi',
        'status',
        'referral_code',
        'is_email_verified',
        'is_admin',
        'check_device',
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
            'is_admin' => 'boolean',
            'check_device' => 'boolean',
        ];
    }

    protected $appends = ['user_detail'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->referral_code = self::generateReferralCode();
        });
    }

    public static function generateReferralCode()
    {
        do {
            $code = Str::upper(Str::random(8)); // Generate a random 8-character code
        } while (self::where('referral_code', $code)->exists()); // Ensure it's unique

        return $code;
    }

    public function user_has_detail()
    {
        return $this->hasOne(UserDetail::class)->select('user_id', 'applied_referral_code', 'gender', 'date_of_birth', 'country_id', 'state_id', 'city_id', 'pincode');
    }

    public function getUserDetailAttribute()
    {
        # user_detail
        if ($this->id) {
            return UserDetail::select('user_id', 'applied_referral_code', 'gender', 'date_of_birth', 'country_id', 'state_id', 'city_id', 'pincode')->where("user_id", $this->id)->first();
        }
        return null;
    }

    public function deviceBindings()
    {
        return $this->hasMany(DeviceBinding::class);
    }

    public function deviceLogs()
    {
        return $this->hasMany(DeviceLog::class);
    }
}
