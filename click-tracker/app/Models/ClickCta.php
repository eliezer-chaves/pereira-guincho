<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClickCta extends Model
{
    use HasFactory;

    protected $table = 'clicks_cta';

    protected $fillable = [
        'data',
        'info',
    ];

    // Se quiser, pode converter automaticamente para Carbon
    protected $casts = [
        'data' => 'datetime',
    ];
}
