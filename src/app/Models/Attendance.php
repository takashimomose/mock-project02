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
        'end_time',
    ];

    /* 今日のレコードを取得する */
    public function scopeTodayRecord($query, $userId)
    {
        return $query->where('user_id', $userId)
                     ->where('date', now()->toDateString());
    }
    
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }
}