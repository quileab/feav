<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id_type_id',
        'name',
        'address',
        'city',
        'province_id_type_id',
        'phone',
        'email',
        'CUIT',
        'tax_condition_type_id',
    ];

    public function customerIdType(): BelongsTo
    {
        return $this->belongsTo(CustomerIdType::class);
    }

    public function provinceIdType(): BelongsTo
    {
        return $this->belongsTo(ProvinceIdType::class);
    }

    public function taxConditionType(): BelongsTo
    {
        return $this->belongsTo(TaxConditionType::class);
    }
}
