<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id_type_id',
        'business_name',
        'name',
        'address',
        'city',
        'province_id_type_id',
        'phone',
        'email',
        'CUIT',
        'responsibility_type_id',
    ];

    public function customerIdType(): BelongsTo
    {
        return $this->belongsTo(CustomerIdType::class);
    }

    public function provinceIdType(): BelongsTo
    {
        return $this->belongsTo(ProvinceIdType::class);
    }

    public function responsibilityType(): BelongsTo
    {
        return $this->belongsTo(ResponsibilityType::class);
    }
}
