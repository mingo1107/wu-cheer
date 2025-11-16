<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Verifier extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    const FILLABLE = [
        'company_id',
        'name',
        'account',
        'email',
        'phone',
        'password',
        'status',
        'last_login_ip',
        'last_login_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    const CASTS = [
        'password'      => 'hashed',
        'last_login_at' => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    const ATTRIBUTES = [
        'company_id'    => null,
        'name'          => '',
        'account'       => '',
        'email'         => '',
        'phone'         => '',
        'password'      => '',
        'status'        => 'active',
        'last_login_ip' => null,
        'last_login_at' => null,
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = self::CASTS;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Scope a query to only include active verifiers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive verifiers.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
