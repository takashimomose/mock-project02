<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeCorrection extends Model
{
    use HasFactory;

    protected $table = 'break_time_corrections';

    protected $fillable = [
        'attendance_correction_id',
        'start_time',
        'end_time',
    ];
}
