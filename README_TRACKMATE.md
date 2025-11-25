# 🎯 TrackMate - Location-Based Attendance System

A comprehensive **location-based attendance management system** built with **Laravel 10** backend API. Perfect for companies that need GPS-based employee attendance tracking without expensive biometric systems.

## 🚀 Features

### 👨💼 Admin Features
- **Dashboard** with real-time attendance statistics
- **Employee Management** (Create, Update, Delete employees)
- **Office Zone Management** (Set GPS boundaries for office locations)
- **Attendance Reports** with filtering and pagination
- **Settings Management** (Work hours, break duration, late thresholds)
- **Real-time Monitoring** of employee check-ins/check-outs

### 👨💻 Employee Features
- **GPS-based Check-in/Check-out** (Auto & Manual)
- **Break Management** (Start/End breaks with tracking)
- **Zone Detection** (Check if within office boundaries)
- **Attendance History** with detailed reports
- **Profile Management**
- **Today's Status** with real-time updates

### 🔧 Technical Features
- **RESTful API** with comprehensive documentation
- **JWT Authentication** using Laravel Sanctum
- **Role-based Access Control** (Admin/Employee)
- **GPS Geofencing** with distance calculations
- **Real-time Location Validation**
- **Comprehensive Error Handling**
- **Database Relationships** with proper constraints

## 📋 Requirements

- PHP 8.1+
- Laravel 10
- MySQL 8.0+
- Composer

## ⚡ Quick Setup

### 1. Clone & Install
```bash
git clone <your-repo>
cd TrackMate-b
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration
Update `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations & Seed Data
```bash
php artisan migrate
php artisan db:seed --class=DefaultDataSeeder
```

### 5. Start Server
```bash
php artisan serve
```

## 🔑 Default Credentials

**Admin Login:**
- Email: `admin@trackmate.com`
- Password: `password123`

## 📚 API Documentation

Complete API documentation is available in `API_DOCUMENTATION.md`

**Base URL:** `http://127.0.0.1:8000/api`

### Quick Test
```bash
# Test endpoint
GET http://127.0.0.1:8000/api/test

# Login
POST http://127.0.0.1:8000/api/auth/login
{
    "email": "admin@trackmate.com",
    "password": "password123"
}
```

## 🗄️ Database Schema

### Core Tables
- **users** - User accounts (Admin/Employee)
- **employees** - Employee details and metadata
- **attendances** - Daily attendance records
- **break_logs** - Break tracking with duration
- **office_zones** - GPS boundaries for office locations
- **settings** - System configuration

### Key Relationships
- User → Employee (1:1)
- User → Attendances (1:Many)
- Attendance → BreakLogs (1:Many)

## 🌍 GPS & Geofencing

### How it Works
1. **Admin sets office zone** with latitude, longitude, and radius
2. **Employee location is checked** against office boundaries
3. **Auto check-in** when entering zone (configurable)
4. **Manual override** always available
5. **Distance calculation** using Haversine formula

### Location Validation
```php
// Check if user is within office zone
POST /api/attendance/check-zone
{
    "latitude": 40.7128,
    "longitude": -74.0060
}
```

## 📱 Mobile App Integration

### Key Endpoints for Mobile
```bash
# Check if in office zone (call every 2-3 minutes)
POST /api/attendance/check-zone

# Auto check-in when entering zone
POST /api/attendance/check-in
{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "type": "auto"
}

# Get today's status
GET /api/attendance/today

# Break management
POST /api/attendance/break-start
POST /api/attendance/break-end
```

### Background Service Recommendations
1. **Location Monitoring** - Check zone every 2-3 minutes
2. **Battery Optimization** - Use efficient location APIs
3. **Offline Support** - Store data locally, sync when online
4. **Push Notifications** - Alert users about attendance status

## 🔐 Security Features

- **Token-based Authentication** (Laravel Sanctum)
- **Role-based Access Control**
- **Input Validation** on all endpoints
- **SQL Injection Protection**
- **CORS Configuration**
- **Rate Limiting** (configurable)

## 📊 Admin Dashboard Data

```json
{
    "statistics": {
        "total_employees": 25,
        "present_today": 20,
        "absent_today": 5,
        "late_today": 3
    },
    "recent_attendances": [...]
}
```

## 🎛️ Configuration

### Work Hours Settings
```json
{
    "work_start_time": "09:00",
    "work_end_time": "18:00",
    "break_duration_minutes": 60,
    "late_threshold_minutes": 15
}
```

### Office Zone Example
```json
{
    "name": "Main Office",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "radius_meters": 100,
    "address": "123 Main Street, New York, NY 10001"
}
```

## 🚀 Production Deployment

### 1. Server Requirements
- PHP 8.1+ with required extensions
- MySQL 8.0+
- SSL Certificate (recommended)
- Cron jobs for scheduled tasks

### 2. Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=trackmate_prod

# Sanctum
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

### 3. Optimization Commands
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 🔄 API Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": { ... }
}
```

## 🧪 Testing

### Manual Testing with Postman
1. Import the API collection
2. Set base URL: `http://127.0.0.1:8000/api`
3. Login to get token
4. Test all endpoints

### Test Scenarios
- ✅ Admin login and dashboard access
- ✅ Employee creation and management
- ✅ Office zone setup and validation
- ✅ GPS-based check-in/check-out
- ✅ Break management
- ✅ Attendance reports

## 📈 Scalability Considerations

### Database Optimization
- Index on frequently queried columns
- Partition attendance table by date
- Archive old attendance data

### Performance
- Cache frequently accessed data
- Use database connection pooling
- Implement API rate limiting

### Monitoring
- Log API requests and responses
- Monitor GPS accuracy and performance
- Track user engagement metrics

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes with tests
4. Submit pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🎉 Ready to Use!

Your TrackMate backend is now ready! 

**Next Steps:**
1. ✅ Test all API endpoints
2. ✅ Set up your office zones
3. ✅ Create employee accounts
4. ✅ Integrate with your mobile app
5. ✅ Deploy to production

**Need Help?** Check the `API_DOCUMENTATION.md` for detailed endpoint documentation.

---

**Built with ❤️ for modern workforce management**