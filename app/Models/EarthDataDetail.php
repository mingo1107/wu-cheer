<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EarthDataDetail extends Model
{
    use HasFactory;

    protected $table = 'earth_data_detail';

    protected $fillable = [
        'earth_data_id',
        'status',   //0:未列印/1:已列印/2:已使用/3:作廢/4:回收
        'barcode',
        'verified_at',
        'verified_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'earth_data_id' => 'integer',
        'status'        => 'integer',
        'verified_at'   => 'datetime',
        'verified_by'   => 'integer',
        'print_at'      => 'datetime',
    ];

    // 狀態常數
    const STATUS_UNPRINTED = 0;  // 未列印
    const STATUS_PRINTED = 1;    // 已列印
    const STATUS_USED = 2;       // 已使用
    const STATUS_VOIDED = 3;     // 作廢
    const STATUS_RECYCLED = 4;    // 回收

    // 狀態對照中文
    const STATUS_LABELS = [
        self::STATUS_UNPRINTED => '未列印',
        self::STATUS_PRINTED   => '已列印',
        self::STATUS_USED      => '已使用',
        self::STATUS_VOIDED    => '作廢',
        self::STATUS_RECYCLED  => '回收',
    ];


    public function earthData()
    {
        return $this->belongsTo(EarthData::class, 'earth_data_id');
    }

    /**
     * 取得狀態中文標籤
     *
     * @param int|null $status
     * @return string
     */
    public static function getStatusLabel(?int $status): string
    {
        return self::STATUS_LABELS[$status] ?? '未知';
    }
}
