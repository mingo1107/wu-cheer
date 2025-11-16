<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cleaner extends Model
{
    use HasFactory, SoftDeletes;

    const FILLABLE = [
        'company_id',
        'cleaner_name',
        'tax_id',
        'contact_person',
        'phone',
        'status',
        'notes',
    ];

    const ATTRIBUTES = [
        'company_id'     => 1,
        'cleaner_name'   => '',
        'tax_id'         => null,
        'contact_person' => '',
        'phone'          => '',
        'status'         => 'active',
        'notes'          => '',
    ];

    const CASTS = [
        'deleted_at' => 'datetime',
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = self::CASTS;

    /**
     * 關聯到車輛
     */
    public function vehicles()
    {
        return $this->hasMany(CleanerVehicle::class);
    }

    /**
     * 取得活躍的車輛
     */
    public function activeVehicles()
    {
        return $this->hasMany(CleanerVehicle::class)->where('status', 'active');
    }
}
