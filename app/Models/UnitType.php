<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'value',
        'description',
    ];
}
