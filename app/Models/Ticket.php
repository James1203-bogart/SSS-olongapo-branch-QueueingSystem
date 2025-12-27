<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'number',
        'priority',
        'category',
        'category_id',
        'mode',
        'status',
        'counter',
        'called_at',
        'completed_at',
        'branch',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['timestamp'];

    public function getTimestampAttribute()
    {
        return optional($this->created_at)->toIso8601String();
    }
}
