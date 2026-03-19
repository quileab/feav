<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'origin_code',
        'category_id',
        'brand',
        'model',
        'description',
        'quantity_min',
        'price',
        'tax_condition_type_id',
        'unit_type_id',
        'sale_price1',
        'profit_percentage1',
        'sale_price2',
        'profit_percentage2',
        'discount_max',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function taxConditionType(): BelongsTo
    {
        return $this->belongsTo(TaxConditionType::class);
    }

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class);
    }
}
