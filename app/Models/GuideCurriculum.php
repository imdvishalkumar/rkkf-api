<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideCurriculum extends Model
{
    protected $table = 'guide_curricula';

    protected $fillable = [
        'title',
        'subtitle',
        'language',
        'icon',
        'file_url',
        'type',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
