<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EarthData extends Model
{
    use HasFactory, SoftDeletes;

    const FILLABLE = [
        'company_id',
        'cleaner_id',
        'batch_no',
        'project_name',
        'flow_control_no',
        'issue_date',
        'issue_count',
        'customer_id',
        'valid_date_from',
        'valid_date_to',
        'carry_qty',
        'carry_soil_type',
        'status_desc',
        'remark_desc',
        'created_by',
        'updated_by',
        'sys_serial_no',
        'status',
        'closure_status',
        'closed_at',
        'closed_by',
        'closure_certificate_path',
        'closure_remark',
    ];

    const ATTRIBUTES = [
        'company_id'      => null,
        'batch_no'        => '',
        'issue_date'      => null,
        'issue_count'     => 0,
        'customer_id'     => null,
        'valid_date_from' => null,
        'valid_date_to'   => null,
        'cleaner_id'      => null,
        'project_name'    => '',
        'flow_control_no' => '',
        'carry_qty'     => 0,
        'carry_soil_type' => '',
        'status_desc'     => '',
        'remark_desc'     => '',
        'created_by'      => '',
        'updated_by'      => '',
        'sys_serial_no'   => '',
        'status'          => 'active',
        'closure_status'  => 'open',
        'closed_at'       => null,
        'closed_by'       => null,
        'closure_certificate_path' => null,
        'closure_remark'  => '',
    ];

    const CASTS = [
        'issue_date'      => 'date',
        'valid_date_from' => 'date',
        'valid_date_to'   => 'date',
        'carry_qty'       => 'decimal:2',
        'customer_id'     => 'integer',
        'issue_count'     => 'integer',
        'deleted_at'      => 'datetime',
        'closed_at'       => 'datetime',
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = [
        'valid_date_from' => 'date',
        'valid_date_to' => 'date',
        'issue_date' => 'date',
        'carry_qty' => 'decimal:2',
        'carry_soil_type' => 'array', // 新增：將 JSON 自動轉為陣列
        'closed_at' => 'datetime',
    ];

    /**
     * 多對多關聯：清運業者
     */
    public function cleaners()
    {
        return $this->belongsToMany(Cleaner::class, 'earth_data_cleaners', 'earth_data_id', 'cleaner_id')
            ->withTimestamps();
    }

    /**
     * 一對多關聯：土單明細
     */
    public function details()
    {
        return $this->hasMany(EarthDataDetail::class, 'earth_data_id');
    }
}
