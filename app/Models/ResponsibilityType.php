<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsibilityType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'value',
        'abbr',
    ];
}
