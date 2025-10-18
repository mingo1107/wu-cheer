<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory;
    use SoftDeletes;

    const FILLABLE = [
        'company_id',
        'title',
        'content',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    const ATTRIBUTES = [
        'company_id' => null,
        'title'      => '',
        'content'    => '',
        'starts_at'  => null,
        'ends_at'    => null,
        'is_active'  => false,
    ];

    const CASTS = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = self::CASTS;
}
