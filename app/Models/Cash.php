<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    use HasFactory;
    protected $table = 'cash';
    protected $fillable = [
        'id',
        'name',
        'description',
        'value',
        'type',
        'created_at',
        'updated_at',
    ];
}
