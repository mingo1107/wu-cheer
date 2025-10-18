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
        'batch_no',
        'doc_seq_detail',
        'issue_date',
        'issue_count',
        'customer_code',
        'valid_from',
        'valid_to',
        'cleaner_name',
        'project_name',
        'flow_control_no',
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
        'doc_seq_detail'  => '',
        'issue_date'      => null,
        'issue_count'     => 0,
        'customer_code'   => '',
        'valid_from'      => null,
        'valid_to'        => null,
        'cleaner_name'    => '',
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
        'issue_date'   => 'date',
        'valid_from'   => 'date',
        'valid_to'     => 'date',
        'carry_qty'    => 'decimal:2',
        'issue_count'  => 'integer',
        'deleted_at'   => 'datetime',
    ];

    protected $fillable   = self::FILLABLE;
    protected $attributes = self::ATTRIBUTES;
    protected $casts      = self::CASTS;
}
