<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncludedConceptType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'value',
    ];
}
