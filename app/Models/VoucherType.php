<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'value',
        'letter',
        'type',
        'enabled',
    ];
}
