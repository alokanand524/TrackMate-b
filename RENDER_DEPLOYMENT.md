# 🚀 Deploy TrackMate to Render

## Prerequisites
- GitHub account
- Render account (free)
- Your TrackMate code pushed to GitHub

## Step 1: Push to GitHub

```bash
# Initialize git (if not done)
git init
git add .
git commit -m "Initial TrackMate backend"

# Create GitHub repo and push
git remote add origin https://github.com/yourusername/trackmate-backend.git
git branch -M main
git push -u origin main
```

## Step 2: Deploy on Render

### Option A: Using render.yaml (Recommended)

1. **Go to Render Dashboard**
   - Visit https://render.com
   - Sign up/Login with GitHub

2. **Create New Service**
   - Click "New +"
   - Select "Blueprint"
   - Connect your GitHub repo
   - Render will auto-detect `render.yaml`

3. **Deploy**
   - Click "Apply"
   - Wait for deployment (5-10 minutes)

### Option B: Manual Setup

1. **Create PostgreSQL Database**
   - New + → PostgreSQL
   - Name: `trackmate-db`
   - Plan: Free
   - Create Database

2. **Create Web Service**
   - New + → Web Service
   - Connect GitHub repo
   - Settings:
     - **Name:** `trackmate-api`
     - **Environment:** `PHP`
     - **Build Command:**
       ```bash
       composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
       ```
     - **Start Command:**
       ```bash
       php artisan migrate --force && php artisan db:seed --class=DefaultDataSeeder --force && php -S 0.0.0.0:$PORT -t public
       ```

3. **Environment Variables**
   Add these in Render dashboard:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=[Generate new key]
   APP_URL=[Your render URL]
   DB_CONNECTION=pgsql
   DB_HOST=[From database info]
   DB_PORT=5432
   DB_DATABASE=[From database info]
   DB_USERNAME=[From database info]
   DB_PASSWORD=[From database info]
   SANCTUM_STATEFUL_DOMAINS=[Your render domain]
   ```

## Step 3: Get Database Connection Info

1. **Go to your PostgreSQL service**
2. **Copy connection details:**
   - Host
   - Port (usually 5432)
   - Database name
   - Username
   - Password

3. **Add to Web Service Environment Variables**

## Step 4: Test Deployment

Once deployed, test your API:

```bash
# Test endpoint
GET https://your-app-name.onrender.com/api/test

# Admin login
POST https://your-app-name.onrender.com/api/auth/login
{
    "email": "admin@trackmate.com",
    "password": "password123"
}
```

## Step 5: Update Postman Collection

Update your Postman collection base URL:
```
https://your-app-name.onrender.com/api
```

## 🔧 Troubleshooting

### Common Issues:

1. **Build Fails**
   - Check PHP version in `composer.json`
   - Ensure all dependencies are in `composer.json`

2. **Database Connection Error**
   - Verify database environment variables
   - Check database is running

3. **Migration Fails**
   - Check database permissions
   - Verify connection string

4. **App Key Error**
   - Generate new APP_KEY in environment variables
   - Use: `base64:` + random 32-character string

### Logs:
- Check Render service logs for detailed errors
- Use `php artisan tinker` for debugging (if needed)

## 🎯 Production Checklist

✅ **Security:**
- APP_DEBUG=false
- Strong APP_KEY
- Secure database credentials

✅ **Performance:**
- Config cached
- Routes cached
- Views cached
- Optimized autoloader

✅ **Database:**
- Migrations run
- Default data seeded
- Connection verified

✅ **API:**
- All endpoints working
- Authentication functional
- CORS configured

## 📱 Mobile App Configuration

Update your mobile app base URL to:
```
https://your-app-name.onrender.com/api
```

## 🔄 Continuous Deployment

Render automatically redeploys when you push to GitHub:

```bash
# Make changes
git add .
git commit -m "Update feature"
git push origin main
# Render auto-deploys
```

## 💰 Render Pricing

- **PostgreSQL:** Free (1GB storage)
- **Web Service:** Free (750 hours/month)
- **Custom Domain:** Free
- **SSL:** Free

## 🎉 Your TrackMate API is Live!

**API Base URL:** `https://your-app-name.onrender.com/api`

**Admin Credentials:**
- Email: `admin@trackmate.com`
- Password: `password123`

**Next Steps:**
1. Test all endpoints
2. Update mobile app configuration
3. Share API URL with your team
4. Monitor usage in Render dashboard