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
        'barcode',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'earth_data_id' => 'integer',
        'verified_at'   => 'datetime',
        'verified_by'   => 'integer',
    ];

    public function earthData()
    {
        return $this->belongsTo(EarthData::class, 'earth_data_id');
    }
}
