<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VoucherDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'product_id',
        'quantity',
        'price',
        'tax',
        'subtotal',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function taxConditionType(): BelongsTo
    {
        return $this->belongsTo(TaxConditionType::class);
    }
}
