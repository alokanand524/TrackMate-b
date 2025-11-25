<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\OfficeZone;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard(Request $request)
    {
        $today = Carbon::today();
        
        $totalEmployees = User::where('role', 'employee')->where('is_active', true)->count();
        $presentToday = Attendance::where('date', $today)->whereNotNull('check_in')->count();
        $absentToday = $totalEmployees - $presentToday;
        $lateToday = Attendance::where('date', $today)->where('status', 'late')->count();
        
        // Get recent attendances
        $recentAttendances = Attendance::with('user')
            ->where('date', $today)
            ->orderBy('check_in', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_employees' => $totalEmployees,
                    'present_today' => $presentToday,
                    'absent_today' => $absentToday,
                    'late_today' => $lateToday,
                ],
                'recent_attendances' => $recentAttendances->map(function ($attendance) {
                    return [
                        'employee_name' => $attendance->user->name,
                        'check_in' => $attendance->check_in,
                        'check_out' => $attendance->check_out,
                        'status' => $attendance->status,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get all employees
     */
    public function employees(Request $request)
    {
        $employees = User::with('employee')
            ->where('role', 'employee')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $employees->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_active' => $user->is_active,
                    'employee' => $user->employee ? [
                        'employee_id' => $user->employee->employee_id,
                        'department' => $user->employee->department,
                        'designation' => $user->employee->designation,
                        'joining_date' => $user->employee->joining_date,
                        'salary' => $user->employee->salary,
                    ] : null,
                ];
            }),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        ]);
    }

    /**
     * Create new employee
     */
    public function createEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:15',
            'employee_id' => 'required|string|unique:employees',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employee',
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            Employee::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'salary' => $request->salary,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update employee
     */
    public function updateEmployee(Request $request, $id)
    {
        $user = User::with('employee')->where('role', 'employee')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:15',
            'is_active' => 'sometimes|boolean',
            'department' => 'sometimes|nullable|string|max:255',
            'designation' => 'sometimes|nullable|string|max:255',
            'salary' => 'sometimes|nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userUpdateData = [];
            $employeeUpdateData = [];

            if ($request->has('name')) $userUpdateData['name'] = $request->name;
            if ($request->has('email')) $userUpdateData['email'] = $request->email;
            if ($request->has('phone')) $userUpdateData['phone'] = $request->phone;
            if ($request->has('is_active')) $userUpdateData['is_active'] = $request->is_active;

            if ($request->has('department')) $employeeUpdateData['department'] = $request->department;
            if ($request->has('designation')) $employeeUpdateData['designation'] = $request->designation;
            if ($request->has('salary')) $employeeUpdateData['salary'] = $request->salary;

            if (!empty($userUpdateData)) {
                $user->update($userUpdateData);
            }

            if (!empty($employeeUpdateData) && $user->employee) {
                $user->employee->update($employeeUpdateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete employee
     */
    public function deleteEmployee($id)
    {
        try {
            $user = User::where('role', 'employee')->findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance reports
     */
    public function attendanceReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'employee_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|in:present,absent,late,half_day',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Attendance::with('user');

        if ($request->from_date) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->where('date', '<=', $request->to_date);
        }

        if ($request->employee_id) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_name' => $attendance->user->name,
                    'employee_id' => $attendance->user->employee->employee_id ?? null,
                    'date' => $attendance->date,
                    'check_in' => $attendance->check_in,
                    'check_out' => $attendance->check_out,
                    'status' => $attendance->status,
                    'total_work_hours' => round($attendance->total_work_minutes / 60, 2),
                    'total_break_minutes' => $attendance->total_break_minutes,
                ];
            }),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ]
        ]);
    }

    /**
     * Get office zones
     */
    public function getOfficeZones()
    {
        $zones = OfficeZone::all();

        return response()->json([
            'success' => true,
            'data' => $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'latitude' => $zone->latitude,
                    'longitude' => $zone->longitude,
                    'radius_meters' => $zone->radius_meters,
                    'address' => $zone->address,
                    'is_active' => $zone->is_active,
                ];
            })
        ]);
    }

    /**
     * Create office zone
     */
    public function createOfficeZone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:1000',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $zone = OfficeZone::create([
                'name' => $request->name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_meters' => $request->radius_meters,
                'address' => $request->address,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Office zone created successfully',
                'data' => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Office zone creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update office zone
     */
    public function updateOfficeZone(Request $request, $id)
    {
        $zone = OfficeZone::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'radius_meters' => 'sometimes|integer|min:10|max:1000',
            'address' => 'sometimes|nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $zone->update($request->only([
                'name', 'latitude', 'longitude', 'radius_meters', 'address', 'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Office zone updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Office zone update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete office zone
     */
    public function deleteOfficeZone($id)
    {
        try {
            $zone = OfficeZone::findOrFail($id);
            $zone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Office zone deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Office zone deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get settings
     */
    public function getSettings()
    {
        $settings = Setting::all();

        return response()->json([
            'success' => true,
            'data' => $settings->mapWithKeys(function ($setting) {
                return [$setting->key => Setting::getValue($setting->key)];
            })
        ]);
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_start_time' => 'sometimes|date_format:H:i',
            'work_end_time' => 'sometimes|date_format:H:i',
            'break_duration_minutes' => 'sometimes|integer|min:0|max:480',
            'late_threshold_minutes' => 'sometimes|integer|min:0|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->has('work_start_time')) {
                Setting::setValue('work_start_time', $request->work_start_time, 'string', 'Office work start time');
            }

            if ($request->has('work_end_time')) {
                Setting::setValue('work_end_time', $request->work_end_time, 'string', 'Office work end time');
            }

            if ($request->has('break_duration_minutes')) {
                Setting::setValue('break_duration_minutes', $request->break_duration_minutes, 'integer', 'Default break duration in minutes');
            }

            if ($request->has('late_threshold_minutes')) {
                Setting::setValue('late_threshold_minutes', $request->late_threshold_minutes, 'integer', 'Late threshold in minutes after work start time');
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Settings update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}