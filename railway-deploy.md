# Railway Deployment Guide for ManifestLink

## Step 1: Create Railway Account
1. Go to [Railway.app](https://railway.app)
2. Sign up with your email
3. Verify your email address

## Step 2: Manual Deployment (No Git Required)

Since Git isn't working, we'll use Railway's manual deployment:

### Option A: Direct Upload
1. **Create a ZIP file** of your project:
   - Select all files in your `manifestlink` folder
   - Right-click → "Send to" → "Compressed folder"
   - Name it `manifestlink.zip`

2. **Upload to Railway**:
   - Go to Railway dashboard
   - Click "New Project"
   - Choose "Deploy from template" → "Empty Project"
   - Click "Deploy Now"
   - Go to project settings
   - Upload your ZIP file

### Option B: Use Railway CLI
1. **Install Railway CLI**:
   ```bash
   npm install -g @railway/cli
   ```

2. **Login to Railway**:
   ```bash
   railway login
   ```

3. **Initialize project**:
   ```bash
   railway init
   ```

4. **Deploy**:
   ```bash
   railway deploy
   ```

## Step 3: Add MySQL Database

1. **In your Railway project dashboard**
2. **Click "Add Service"**
3. **Select "Database" → "MySQL"**
4. **Railway will automatically create a MySQL instance**

## Step 4: Configure Environment Variables

In Railway dashboard, add these environment variables:

```
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=your-railway-password
APP_ENV=production
```

## Step 5: Import Database Structure

1. **Get database connection details** from Railway MySQL service
2. **Use MySQL client** (like phpMyAdmin, MySQL Workbench, or command line)
3. **Connect to your Railway database**
4. **Import your SQL files:**
   ```sql
   source manifestlink.sql;
   source manifest_table.sql;
   source otp_table.sql;
   ```

## Step 6: Update Frontend Configuration

Update your `config.js` file with Railway backend URL:

```javascript
const config = {
    production: {
        apiBaseUrl: 'https://your-railway-app.railway.app',
        // ... rest of config
    }
};
```

## Step 7: Test Your Deployment

1. **Visit your Railway app URL**
2. **Test registration functionality**
3. **Test QR code generation**
4. **Test admin dashboard**

## Troubleshooting

### Common Issues:
1. **Database connection failed**: Check environment variables
2. **File permissions**: Ensure PHP can write to directories
3. **Missing extensions**: Check if GD extension is available

### Railway Logs:
```bash
railway logs
```

## Cost
- **Free tier**: 500 hours/month
- **Paid tier**: $5/month for unlimited usage

## Next Steps
1. **Test all functionality**
2. **Update frontend to use Railway backend**
3. **Set up custom domain** (optional)
4. **Monitor performance**
