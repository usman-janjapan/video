<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;
    protected $fillable = [
        'path',
        'metadata',
        'converted',
        'converted_path',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
