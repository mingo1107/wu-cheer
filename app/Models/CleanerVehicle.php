<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CleanerVehicle extends Model
{
    use HasFactory, SoftDeletes;

    const FILLABLE = [
        'cleaner_id',
        'front_plate',
        'rear_plate',
        'status',
        'notes',
    ];

    const ATTRIBUTES = [
        'cleaner_id'  => null,
        'front_plate' => '',
        'rear_plate'  => null,
        'status'      => 'active',
        'notes'       => null,
    ];

    const CASTS = [
        'deleted_at' => 'datetime',
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = self::CASTS;

    /**
     * 關聯到清運業者
     */
    public function cleaner()
    {
        return $this->belongsTo(Cleaner::class);
    }

    /**
     * Scope: 活躍的車輛
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: 停用的車輛
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
