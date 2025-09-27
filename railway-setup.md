# Railway Deployment Guide for ManifestLink Backend

## Step 1: Create Railway Account
1. Go to [Railway.app](https://railway.app)
2. Sign up with GitHub (recommended)
3. Connect your GitHub account

## Step 2: Deploy Your Backend
1. **Create New Project** in Railway
2. **Connect GitHub Repository** (you'll need to push your code to GitHub first)
3. **Select your ManifestLink repository**
4. **Railway will automatically detect PHP** and use the `composer.json` file

## Step 3: Configure Environment Variables
In Railway dashboard, add these environment variables:

```
DB_HOST=your-database-host
DB_PORT=3306
DB_NAME=manifestlink
DB_USER=your-username
DB_PASSWORD=your-password
```

## Step 4: Set Up Database
1. **Add MySQL Service** in Railway
2. **Copy connection details** from the MySQL service
3. **Update environment variables** with the new database details

## Step 5: Update Frontend Config
After deployment, update your `config.js` file:

```javascript
production: {
    apiBaseUrl: 'https://your-railway-app.railway.app/api',
    // ... rest of config
}
```

## Railway Advantages:
- ✅ Free tier available
- ✅ Automatic deployments from GitHub
- ✅ Built-in MySQL database
- ✅ Easy environment variable management
- ✅ Automatic HTTPS
- ✅ Simple PHP deployment

## Commands to Deploy:
```bash
# Push your code to GitHub first
git add .
git commit -m "Add Railway deployment files"
git push origin main

# Then deploy through Railway dashboard
```

## Troubleshooting:
- Check Railway logs if deployment fails
- Ensure all PHP extensions are available
- Verify environment variables are set correctly
