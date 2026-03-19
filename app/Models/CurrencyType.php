<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
    ];
}
