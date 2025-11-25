<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\OfficeZone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Check if user is within office zone
     */
    public function checkZone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $officeZones = OfficeZone::where('is_active', true)->get();
        
        if ($officeZones->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active office zones found'
            ], 404);
        }

        $withinZone = false;
        $nearestZone = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($officeZones as $zone) {
            if ($zone->isWithinZone($request->latitude, $request->longitude)) {
                $withinZone = true;
                $nearestZone = $zone;
                break;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'within_zone' => $withinZone,
                'zone' => $nearestZone ? [
                    'id' => $nearestZone->id,
                    'name' => $nearestZone->name,
                    'address' => $nearestZone->address,
                ] : null,
                'user_location' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]
            ]
        ]);
    }

    /**
     * Check in attendance
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'type' => 'sometimes|in:auto,manual',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $today = Carbon::today();
        
        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in today',
                'data' => [
                    'check_in_time' => $existingAttendance->check_in,
                ]
            ], 400);
        }

        // Verify location is within office zone
        $officeZones = OfficeZone::where('is_active', true)->get();
        $withinZone = false;
        
        foreach ($officeZones as $zone) {
            if ($zone->isWithinZone($request->latitude, $request->longitude)) {
                $withinZone = true;
                break;
            }
        }

        if (!$withinZone) {
            return response()->json([
                'success' => false,
                'message' => 'You are not within the office zone'
            ], 400);
        }

        try {
            $checkInTime = Carbon::now();
            
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'check_in' => $checkInTime,
                    'check_in_lat' => $request->latitude,
                    'check_in_lng' => $request->longitude,
                    'check_in_type' => $request->type ?? 'manual',
                    'status' => 'present',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'check_in_time' => $checkInTime->format('H:i:s'),
                    'date' => $today->format('Y-m-d'),
                    'type' => $attendance->check_in_type,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check in failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check out attendance
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'type' => 'sometimes|in:auto,manual',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'No check-in found for today'
            ], 400);
        }

        if ($attendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked out today',
                'data' => [
                    'check_out_time' => $attendance->check_out,
                ]
            ], 400);
        }

        try {
            $checkOutTime = Carbon::now();
            $checkInTime = Carbon::parse($attendance->check_in);
            
            // Calculate total work minutes
            $totalWorkMinutes = $checkOutTime->diffInMinutes($checkInTime);
            
            // Subtract break minutes
            $totalBreakMinutes = $attendance->breakLogs()->sum('break_minutes');
            $actualWorkMinutes = $totalWorkMinutes - $totalBreakMinutes;

            $attendance->update([
                'check_out' => $checkOutTime,
                'check_out_lat' => $request->latitude,
                'check_out_lng' => $request->longitude,
                'check_out_type' => $request->type ?? 'manual',
                'total_work_minutes' => $actualWorkMinutes,
                'total_break_minutes' => $totalBreakMinutes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'check_out_time' => $checkOutTime->format('H:i:s'),
                    'total_work_hours' => round($actualWorkMinutes / 60, 2),
                    'total_break_minutes' => $totalBreakMinutes,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check out failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start break
     */
    public function breakStart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'break_type' => 'sometimes|in:lunch,tea,other',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Please check in first'
            ], 400);
        }

        // Check if already on break
        $activeBreak = BreakLog::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if ($activeBreak) {
            return response()->json([
                'success' => false,
                'message' => 'Already on break',
                'data' => [
                    'break_start_time' => $activeBreak->break_start,
                ]
            ], 400);
        }

        try {
            $breakLog = BreakLog::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::now(),
                'break_type' => $request->break_type ?? 'other',
                'reason' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break started successfully',
                'data' => [
                    'break_id' => $breakLog->id,
                    'break_start_time' => $breakLog->break_start,
                    'break_type' => $breakLog->break_type,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Break start failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * End break
     */
    public function breakEnd(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance record found for today'
            ], 400);
        }

        $activeBreak = BreakLog::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if (!$activeBreak) {
            return response()->json([
                'success' => false,
                'message' => 'No active break found'
            ], 400);
        }

        try {
            $breakEndTime = Carbon::now();
            $breakStartTime = Carbon::parse($activeBreak->break_start);
            $breakMinutes = $breakEndTime->diffInMinutes($breakStartTime);

            $activeBreak->update([
                'break_end' => $breakEndTime,
                'break_minutes' => $breakMinutes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break ended successfully',
                'data' => [
                    'break_id' => $activeBreak->id,
                    'break_end_time' => $breakEndTime->format('H:i:s'),
                    'break_duration_minutes' => $breakMinutes,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Break end failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's attendance status
     */
    public function todayStatus(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        
        $attendance = Attendance::with('breakLogs')
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $today->format('Y-m-d'),
                    'status' => 'not_checked_in',
                    'check_in' => null,
                    'check_out' => null,
                    'total_work_hours' => 0,
                    'total_break_minutes' => 0,
                    'is_on_break' => false,
                    'breaks' => [],
                ]
            ]);
        }

        $activeBreak = $attendance->breakLogs()->whereNull('break_end')->first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'date' => $attendance->date,
                'status' => $attendance->status,
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'total_work_hours' => round($attendance->total_work_minutes / 60, 2),
                'total_break_minutes' => $attendance->total_break_minutes,
                'is_on_break' => $activeBreak ? true : false,
                'active_break' => $activeBreak ? [
                    'id' => $activeBreak->id,
                    'start_time' => $activeBreak->break_start,
                    'type' => $activeBreak->break_type,
                ] : null,
                'breaks' => $attendance->breakLogs->map(function ($break) {
                    return [
                        'id' => $break->id,
                        'start_time' => $break->break_start,
                        'end_time' => $break->break_end,
                        'duration_minutes' => $break->break_minutes,
                        'type' => $break->break_type,
                        'reason' => $break->reason,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get attendance history
     */
    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $limit = $request->limit ?? 30;
        
        $query = Attendance::with('breakLogs')
            ->where('user_id', $user->id);

        if ($request->from_date) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->where('date', '<=', $request->to_date);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'check_in' => $attendance->check_in,
                    'check_out' => $attendance->check_out,
                    'status' => $attendance->status,
                    'total_work_hours' => round($attendance->total_work_minutes / 60, 2),
                    'total_break_minutes' => $attendance->total_break_minutes,
                    'breaks_count' => $attendance->breakLogs->count(),
                ];
            })
        ]);
    }
}