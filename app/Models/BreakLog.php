<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'break_start',
        'break_end',
        'break_minutes',
        'break_type',
        'reason',
    ];

    protected $casts = [
        'break_start' => 'datetime:H:i:s',
        'break_end' => 'datetime:H:i:s',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
