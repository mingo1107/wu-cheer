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
        'carry_qty'       => 0,
        'carry_soil_type' => '',
        'status_desc'     => '',
        'remark_desc'     => '',
        'created_by'      => '',
        'updated_by'      => '',
        'sys_serial_no'   => '',
        'status'          => 'active',
    ];

    const CASTS = [
        'issue_date'      => 'date',
        'valid_date_from' => 'date',
        'valid_date_to'   => 'date',
        'carry_qty'       => 'decimal:2',
        'customer_id'     => 'integer',
        'issue_count'     => 'integer',
        'deleted_at'      => 'datetime',
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = self::CASTS;
}
