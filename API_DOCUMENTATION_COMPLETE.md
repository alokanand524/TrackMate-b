# 🎯 TrackMate API Documentation - Complete Guide

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your_token_here}
```

**⚠️ Security Note:** All attendance endpoints require authentication. Without a valid token, you'll receive a 401 Unauthorized error.

---

## 🧪 Test Endpoints

### Test API Connection
**GET** `/test`

**Response:**
```json
{
    "message": "Project is Setup"
}
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
            "phone": "+1234567890",
            "employee": {
                "employee_id": "EMP001",
                "department": "IT",
                "designation": "Developer",
                "joining_date": "2024-01-15"
            }
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
*🔒 Requires Authentication*

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
*🔒 Requires Authentication*

**Request Body:**
```json
{
    "name": "John Updated",
    "phone": "+9876543210",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Updated",
            "email": "john@example.com",
            "phone": "+9876543210"
        }
    }
}
```

### 4. Logout
**POST** `/auth/logout`
*🔒 Requires Authentication*

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## 📍 Attendance Endpoints (Employee)

**⚠️ All attendance endpoints require authentication and zone validation**

### 1. Check Zone
**POST** `/attendance/check-zone`
*🔒 Requires Authentication*

**Request Body:**
```json
{
    "latitude": 40.7128,
    "longitude": -74.0060
}
```

**Response (Within Zone):**
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

**Response (Outside Zone):**
```json
{
    "success": true,
    "data": {
        "within_zone": false,
        "zone": null,
        "user_location": {
            "latitude": 40.7128,
            "longitude": -74.0060
        }
    }
}
```

### 2. Check In
**POST** `/attendance/check-in`
*🔒 Requires Authentication + Zone Validation*

**Request Body:**
```json
{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "type": "manual"
}
```

**Response (Success):**
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

**Response (Outside Zone):**
```json
{
    "success": false,
    "message": "You are not within the office zone"
}
```

**Response (Already Checked In):**
```json
{
    "success": false,
    "message": "Already checked in today",
    "data": {
        "check_in_time": "09:15:30"
    }
}
```

### 3. Check Out
**POST** `/attendance/check-out`
*🔒 Requires Authentication*

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
*🔒 Requires Authentication*

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
*🔒 Requires Authentication*

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
*🔒 Requires Authentication*

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
        "breaks": [
            {
                "id": 1,
                "start_time": "12:00:00",
                "end_time": "13:00:00",
                "duration_minutes": 60,
                "type": "lunch",
                "reason": "Lunch break"
            }
        ]
    }
}
```

### 7. Attendance History
**GET** `/attendance/history?from_date=2024-11-01&to_date=2024-11-30&limit=30`
*🔒 Requires Authentication*

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

## 👨💼 Admin Endpoints

**🔒 All admin endpoints require admin authentication**

### 1. Dashboard
**GET** `/admin/dashboard`
*🔒 Requires Admin Authentication*

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

### 2. Create Admin
**POST** `/admin/create-admin`
*🔒 Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "New Admin",
    "email": "newadmin@trackmate.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Admin created successfully",
    "data": {
        "user": {
            "id": 2,
            "name": "New Admin",
            "email": "newadmin@trackmate.com",
            "role": "admin",
            "phone": "+1234567890"
        }
    }
}
```

### 3. Get All Employees
**GET** `/admin/employees?search=john&per_page=15`
*🔒 Requires Admin Authentication*

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

### 4. Create Employee
**POST** `/admin/employees`
*🔒 Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "password123",
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

### 5. Update Employee
**PUT** `/admin/employees/{id}`
*🔒 Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "Jane Updated",
    "department": "Marketing",
    "salary": 65000.00,
    "is_active": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Employee updated successfully"
}
```

### 6. Delete Employee
**DELETE** `/admin/employees/{id}`
*🔒 Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "message": "Employee deleted successfully"
}
```

### 7. Register Employee (Alternative)
**POST** `/admin/register-employee`
*🔒 Requires Admin Authentication*

Same as Create Employee endpoint but with password confirmation required.

### 8. Attendance Reports
**GET** `/admin/attendance-reports?from_date=2024-11-01&to_date=2024-11-30&employee_id=2&status=present&per_page=15`
*🔒 Requires Admin Authentication*

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

### 9. Get Office Zones
**GET** `/admin/office-zones`
*🔒 Requires Admin Authentication*

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

### 10. Create Office Zone
**POST** `/admin/office-zones`
*🔒 Requires Admin Authentication*

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

**Response:**
```json
{
    "success": true,
    "message": "Office zone created successfully",
    "data": {
        "id": 2,
        "name": "Branch Office"
    }
}
```

### 11. Update Office Zone
**PUT** `/admin/office-zones/{id}`
*🔒 Requires Admin Authentication*

**Request Body:**
```json
{
    "name": "Updated Office Name",
    "radius_meters": 200,
    "is_active": false
}
```

**Response:**
```json
{
    "success": true,
    "message": "Office zone updated successfully"
}
```

### 12. Delete Office Zone
**DELETE** `/admin/office-zones/{id}`
*🔒 Requires Admin Authentication*

**Response:**
```json
{
    "success": true,
    "message": "Office zone deleted successfully"
}
```

### 13. Get Settings
**GET** `/admin/settings`
*🔒 Requires Admin Authentication*

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

### 14. Update Settings
**PUT** `/admin/settings`
*🔒 Requires Admin Authentication*

**Request Body:**
```json
{
    "work_start_time": "08:30",
    "work_end_time": "17:30",
    "break_duration_minutes": 45,
    "late_threshold_minutes": 10
}
```

**Response:**
```json
{
    "success": true,
    "message": "Settings updated successfully"
}
```

---

## 🌍 Public Endpoints (Authenticated Users)

### Get Office Zones
**GET** `/office-zones`
*🔒 Requires Authentication*

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

### Zone Restriction (400)
```json
{
    "success": false,
    "message": "You are not within the office zone"
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

## 🔒 Security Features

### Authentication Security
- **Token-based Authentication** using Laravel Sanctum
- **Role-based Access Control** (Admin/Employee)
- **Automatic Token Expiration** (configurable)
- **Secure Password Hashing** using bcrypt

### Location Security
- **GPS Zone Validation** - Check-in only allowed within defined office zones
- **Coordinate Validation** - Latitude/longitude bounds checking
- **Distance Calculation** using Haversine formula for accuracy

### Data Security
- **Input Validation** on all endpoints
- **SQL Injection Protection** via Eloquent ORM
- **CORS Configuration** for cross-origin requests
- **Rate Limiting** (configurable)

---

## 🚀 Getting Started

### 1. Admin Setup
```bash
# Login as default admin
POST /api/auth/login
{
    "email": "admin@trackmate.com",
    "password": "password123"
}
```

### 2. Configure Office Zone
```bash
# Create office zone with GPS coordinates
POST /api/admin/office-zones
{
    "name": "Your Office",
    "latitude": YOUR_OFFICE_LAT,
    "longitude": YOUR_OFFICE_LNG,
    "radius_meters": 100,
    "address": "Your Office Address"
}
```

### 3. Create Employees
```bash
# Add employees to the system
POST /api/admin/employees
{
    "name": "Employee Name",
    "email": "employee@company.com",
    "password": "password123",
    "employee_id": "EMP001",
    "department": "IT",
    "designation": "Developer",
    "joining_date": "2024-11-25"
}
```

### 4. Employee Usage
```bash
# Employee login
POST /api/auth/login

# Check zone before attendance
POST /api/attendance/check-zone

# Check in (only if within zone)
POST /api/attendance/check-in

# Manage breaks
POST /api/attendance/break-start
POST /api/attendance/break-end

# Check out
POST /api/attendance/check-out
```

---

## 📱 Mobile App Integration Guide

### Background Location Monitoring
```javascript
// Check zone every 2-3 minutes
setInterval(async () => {
    const location = await getCurrentLocation();
    const response = await checkZone(location.latitude, location.longitude);
    
    if (response.data.within_zone && !isCheckedIn) {
        // Auto check-in option
        await autoCheckIn(location);
    }
}, 180000); // 3 minutes
```

### Auto Check-in Implementation
```javascript
const autoCheckIn = async (location) => {
    try {
        const response = await fetch('/api/attendance/check-in', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                latitude: location.latitude,
                longitude: location.longitude,
                type: 'auto'
            })
        });
        
        if (response.ok) {
            showNotification('Auto checked-in successfully!');
        }
    } catch (error) {
        console.error('Auto check-in failed:', error);
    }
};
```

### Offline Support
```javascript
// Store attendance data locally when offline
const storeOfflineAttendance = (attendanceData) => {
    const offlineData = JSON.parse(localStorage.getItem('offline_attendance') || '[]');
    offlineData.push({
        ...attendanceData,
        timestamp: Date.now(),
        synced: false
    });
    localStorage.setItem('offline_attendance', JSON.stringify(offlineData));
};

// Sync when back online
const syncOfflineData = async () => {
    const offlineData = JSON.parse(localStorage.getItem('offline_attendance') || '[]');
    const unsynced = offlineData.filter(item => !item.synced);
    
    for (const item of unsynced) {
        try {
            await syncAttendanceRecord(item);
            item.synced = true;
        } catch (error) {
            console.error('Sync failed for item:', item);
        }
    }
    
    localStorage.setItem('offline_attendance', JSON.stringify(offlineData));
};
```

---

## 🔧 Environment Configuration

### Required Environment Variables
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=trackmateDB
DB_USERNAME=postgres
DB_PASSWORD=1234

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

# App
APP_URL=http://127.0.0.1:8000
APP_ENV=local
APP_DEBUG=true
```

### Production Deployment
```env
# Production settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (production)
DB_HOST=your-production-db-host
DB_DATABASE=trackmate_production

# Sanctum (production)
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,app.yourdomain.com
```

---

## 📊 API Endpoints Summary

| Category | Endpoints | Authentication | Description |
|----------|-----------|----------------|-------------|
| **Test** | 1 | None | API connectivity test |
| **Auth** | 4 | Mixed | Login, profile, logout |
| **Attendance** | 7 | Required + Zone | Employee attendance management |
| **Admin Dashboard** | 1 | Admin | Statistics and overview |
| **Admin Users** | 6 | Admin | User and employee management |
| **Admin Zones** | 4 | Admin | Office zone management |
| **Admin Reports** | 1 | Admin | Attendance reporting |
| **Admin Settings** | 2 | Admin | System configuration |
| **Public** | 1 | Required | Office zones for employees |

**Total: 27 Endpoints**

---

## 🎯 Key Features Implemented

✅ **Authentication & Authorization**
- JWT token-based authentication
- Role-based access control
- Secure password handling

✅ **GPS-Based Attendance**
- Real-time location validation
- Office zone boundary checking
- Auto and manual check-in/out

✅ **Break Management**
- Multiple break types (lunch, tea, other)
- Break duration tracking
- Active break monitoring

✅ **Admin Panel**
- Employee management
- Office zone configuration
- Attendance reporting
- System settings

✅ **Data Security**
- Input validation
- SQL injection protection
- Secure API endpoints

✅ **Mobile-Ready**
- RESTful API design
- JSON responses
- Offline support ready

---

**🎉 Your TrackMate API is production-ready!**

For support or questions, refer to the codebase or contact the development team.