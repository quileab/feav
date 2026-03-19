<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
    }

    /**
     * Obtiene el nombre del cliente basado en el DocNro del JSON.
     */
    public function getCustomerNameAttribute(): string
    {
        $docNro = $this->data['DocNro'] ?? null;
        if (!$docNro) return 'Consumidor Final';

        $customer = \App\Models\Customer::where('CUIT', $docNro)->first();
        return $customer ? $customer->name : 'No registrado (Doc: ' . $docNro . ')';
    }

    /**
     * Obtiene la URL de AFIP para el código QR.
     */
    public function getQrUrlAttribute(): string
    {
        return (new \App\Services\AfipService())->getQrUrl($this);
    }
}
