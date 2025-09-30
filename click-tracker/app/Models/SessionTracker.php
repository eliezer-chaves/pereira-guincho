<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionTracker extends Model
{
    use HasFactory;

    protected $table = 'session_trackers';

    protected $fillable = [
        'uuid',
        'initialTime',
        'lastTime',
        'time',
        'clicou',
        'info',
    ];

    protected $casts = [
        'initialTime' => 'date',
        'lastTime' => 'date',
        'clicou' => 'boolean',
        'info' => 'array',
    ];
}
