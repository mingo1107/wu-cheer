<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    const FILLABLE = [
        'user_id',
        'company_id',
        'data_id',
        'controller',
        'method',
        'ip',
        'requests_data',
        'result',
        'remark',
    ];

    /**
     * The attributes with default values.
     *
     * @var array<string, mixed>
     */
    const ATTRIBUTES = [
        'user_id'      => null,
        'company_id'   => null,
        'data_id'      => 0,
        'controller'   => '',
        'method'       => '',
        'ip'           => null,
        'requests_data' => null,
        'result'       => 0,
        'remark'       => null,
    ];

    protected $fillable = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requests_data' => 'array',
            'result'        => 'integer',
            'data_id'       => 'integer',
        ];
    }

    /**
     * 關聯到使用者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

