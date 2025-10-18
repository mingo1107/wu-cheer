<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
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
        'customer_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_id',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    const CASTS = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const ATTRIBUTES = [
        'company_id'     => null,
        'customer_name'  => '',
        'contact_person' => '',
        'phone'          => '',
        'email'          => '',
        'address'        => '',
        'tax_id'         => '',
        'status'         => 'active',
        'notes'          => '',
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = self::CASTS;

    /**
     * Scope a query to only include active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive customers.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
