# TrackMate API Documentation

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your_token_here}
```

---

## 🔐 Authentication Endpoints

### 1. Login
**POST** `/auth/login`

**Request Body:**
```json
{
    "email": "admin@trackmate.com",
    "password": "password123"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@trackmate.com",
            "role": "admin",
            "phone": "+1234567890"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

### 2. Get Profile
**GET** `/auth/profile`
*Requires Authentication*

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "employee",
        "phone": "+1234567890",
        "is_active": true,
        "employee": {
            "employee_id": "EMP001",
            "department": "IT",
            "designation": "Developer",
            "joining_date": "2024-01-15",
            "salary": 50000.00
        }
    }
}
```

### 3. Update Profile
**PUT** `/auth/profile`
*Requires Authentication*

**Request Body:**
```json
{
    "name": "John Updated",
    "phone": "+9876543210",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### 4. Logout
**POST** `/auth/logout`
*Requires Authentication*

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## 📍 Attendance Endpoints

### 1. Check Zone
**POST** `/attendance/check-zone`
*Requires Authentication*

**Request Body:**
```json
{
    "latitude": 40.7128,
    "longitude": -74.0060
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "within_zone": true,
        "zone": {
            "id": 1,
            "name": "Main Office",
            "address": "123 Main Street, New York, NY 10001"
        },
        "user_location": {
            "latitude": 40.7128,
            "longitude": -74.0060
        }
    }
}
```

### 2. Check In
**POST** `/attendance/check-in`
*Requires Authentication*

**Request Body:**
```json
{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "type": "manual"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Checked in successfully",
    "data": {
        "attendance_id": 1,
        "check_in_time": "09:15:30",
        "date": "2024-11-25",
        "type": "manual"
    }
}
```

### 3. Check Out
**POST** `/attendance/check-out`
*Requires Authentication*

**Request Body:**
```json
{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "type": "manual"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Checked out successfully",
    "data": {
        "attendance_id": 1,
        "check_out_time": "18:30:45",
        "total_work_hours": 8.25,
        "total_break_minutes": 60
    }
}
```

### 4. Start Break
**POST** `/attendance/break-start`
*Requires Authentication*

**Request Body:**
```json
{
    "break_type": "lunch",
    "reason": "Lunch break"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Break started successfully",
    "data": {
        "break_id": 1,
        "break_start_time": "12:00:00",
        "break_type": "lunch"
    }
}
```

### 5. End Break
**POST** `/attendance/break-end`
*Requires Authentication*

**Response:**
```json
{
    "success": true,
    "message": "Break ended successfully",
    "data": {
        "break_id": 1,
        "break_end_time": "13:00:00",
        "break_duration_minutes": 60
    }
}
```

### 6. Today's Status
**GET** `/attendance/today`
*Requires Authentication*

**Response:**
```json
{
    "success": true,
    "data": {
        "date": "2024-11-25",
        "status": "present",
        "check_in": "09:15:30",
        "check_out": null,
        "total_work_hours": 0,
        "total_break_minutes": 0,
        "is_on_break": false,
        "active_break": null,
        "breaks": []
    }
}
```

### 7. Attendance History
**GET** `/attendance/history?from_date=2024-11-01&to_date=2024-11-30&limit=30`
*Requires Authentication*

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "date": "2024-11-25",
            "check_in": "09:15:30",
            "check_out": "18:30:45",
            "status": "present",
            "total_work_hours": 8.25,
            "total_break_minutes": 60,
            "breaks_count": 2
        }
    ]
}
```

---

## 👨‍💼 Admin Endpoints

### 1. Dashboard
**GET** `/admin/dashboard`
*Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "data": {
        "statistics": {
            "total_employees": 25,
            "present_today": 20,
            "absent_today": 5,
            "late_today": 3
        },
        "recent_attendances": [
            {
                "employee_name": "John Doe",
                "check_in": "09:15:30",
                "check_out": null,
                "status": "present"
            }
        ]
    }
}
```

### 2. Get All Employees
**GET** `/admin/employees?search=john&per_page=15`
*Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "is_active": true,
            "employee": {
                "employee_id": "EMP001",
                "department": "IT",
                "designation": "Developer",
                "joining_date": "2024-01-15",
                "salary": 50000.00
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 2,
        "per_page": 15,
        "total": 25
    }
}
```

### 3. Create Employee
**POST** `/admin/employees`
*Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "employee_id": "EMP002",
    "department": "HR",
    "designation": "HR Manager",
    "joining_date": "2024-11-25",
    "salary": 60000.00
}
```

**Response:**
```json
{
    "success": true,
    "message": "Employee created successfully",
    "data": {
        "id": 3,
        "name": "Jane Smith",
        "email": "jane@example.com"
    }
}
```

### 4. Update Employee
**PUT** `/admin/employees/{id}`
*Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "Jane Updated",
    "department": "Marketing",
    "salary": 65000.00,
    "is_active": true
}
```

### 5. Delete Employee
**DELETE** `/admin/employees/{id}`
*Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "message": "Employee deleted successfully"
}
```

### 6. Attendance Reports
**GET** `/admin/attendance-reports?from_date=2024-11-01&to_date=2024-11-30&employee_id=2&status=present&per_page=15`
*Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "employee_name": "John Doe",
            "employee_id": "EMP001",
            "date": "2024-11-25",
            "check_in": "09:15:30",
            "check_out": "18:30:45",
            "status": "present",
            "total_work_hours": 8.25,
            "total_break_minutes": 60
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### 7. Get Office Zones
**GET** `/admin/office-zones`
*Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Main Office",
            "latitude": 40.7128,
            "longitude": -74.0060,
            "radius_meters": 100,
            "address": "123 Main Street, New York, NY 10001",
            "is_active": true
        }
    ]
}
```

### 8. Create Office Zone
**POST** `/admin/office-zones`
*Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "Branch Office",
    "latitude": 40.7589,
    "longitude": -73.9851,
    "radius_meters": 150,
    "address": "456 Broadway, New York, NY 10013"
}
```

### 9. Update Office Zone
**PUT** `/admin/office-zones/{id}`
*Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "Updated Office Name",
    "radius_meters": 200,
    "is_active": false
}
```

### 10. Delete Office Zone
**DELETE** `/admin/office-zones/{id}`
*Requires Admin Authentication*

### 11. Get Settings
**GET** `/admin/settings`
*Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "data": {
        "work_start_time": "09:00",
        "work_end_time": "18:00",
        "break_duration_minutes": 60,
        "late_threshold_minutes": 15,
        "company_name": "TrackMate Company",
        "timezone": "UTC"
    }
}
```

### 12. Update Settings
**PUT** `/admin/settings`
*Requires Admin Authentication*

**Request Body:**
```json
{
    "work_start_time": "08:30",
    "work_end_time": "17:30",
    "break_duration_minutes": 45,
    "late_threshold_minutes": 10
}
```

### 13. Register Employee (Admin)
**POST** `/admin/register-employee`
*Requires Admin Authentication*

Same as Create Employee endpoint.

---

## 🌍 Public Endpoints (for authenticated users)

### Get Office Zones
**GET** `/office-zones`
*Requires Authentication*

Returns the same response as admin office zones endpoint but accessible to all authenticated users.

---

## 📝 Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

### Unauthorized (401)
```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
    "success": false,
    "message": "Access denied. Admin privileges required."
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error",
    "error": "Detailed error message"
}
```

---

## 🚀 Getting Started

1. **Login as Admin:**
   - Email: `admin@trackmate.com`
   - Password: `password123`

2. **Set up Office Zone:**
   - Use `/admin/office-zones` to create your office location

3. **Create Employees:**
   - Use `/admin/employees` to add employees

4. **Employee Login:**
   - Employees can login with their credentials
   - Use attendance endpoints for check-in/out

5. **Monitor Attendance:**
   - Use admin dashboard and reports to monitor attendance

---

## 📱 Mobile App Integration Notes

1. **Background Location:** Use the `/attendance/check-zone` endpoint every 2-3 minutes to check if user is in office zone
2. **Auto Check-in:** When user enters zone, automatically call `/attendance/check-in` with `type: "auto"`
3. **Manual Override:** Always provide manual check-in/out buttons
4. **Break Management:** Implement break start/end functionality
5. **Offline Support:** Store attendance data locally and sync when online
6. **Push Notifications:** Notify users about check-in/out status

---
