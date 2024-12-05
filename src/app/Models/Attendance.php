<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'break_start_time',
        'break_end_time',
        'end_time',
    ];

    /* 今日のレコードを取得する */
    public function scopeTodayRecord($query, $userId)
    {
        return $query->where('user_id', $userId)
                     ->where('date', now()->toDateString());
    }
}
