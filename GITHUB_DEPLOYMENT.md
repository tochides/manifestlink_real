# 🚀 Deploy ManifestLink to GitHub + Railway

## Step 1: Install and Setup Git

### Install Git:
1. **Download:** [git-scm.com/download/win](https://git-scm.com/download/win)
2. **Install** with default settings
3. **Restart** your command prompt completely

### Verify Git Installation:
```bash
git --version
```

## Step 2: Create GitHub Repository

1. **Go to:** [github.com](https://github.com)
2. **Sign up** or login
3. **Click:** "New repository"
4. **Repository name:** `manifestlink`
5. **Description:** "ManifestLink QR-enabled passenger manifest system"
6. **Make it Public** (free)
7. **Click:** "Create repository"

## Step 3: Initialize Git and Push to GitHub

### In your project folder:
```bash
# Navigate to your project
cd C:\xampp\htdocs\manifestlink

# Initialize git repository
git init

# Add all files
git add .

# Make first commit
git commit -m "Initial ManifestLink deployment"

# Add GitHub remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/manifestlink.git

# Push to GitHub
git push -u origin main
```

## Step 4: Connect Railway to GitHub

1. **Go to Railway dashboard**
2. **Click:** "New Project"
3. **Choose:** "Deploy from GitHub repo"
4. **Select:** your `manifestlink` repository
5. **Railway will automatically deploy** from GitHub

## Step 5: Add MySQL Database

1. **In Railway project**
2. **Click:** "Add Service"
3. **Select:** "Database" → "MySQL"
4. **Railway creates database automatically**

## Step 6: Configure Environment Variables

In Railway dashboard:
```
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=your-railway-password
APP_ENV=production
```

## Step 7: Test Your Deployment

1. **Visit your Railway app URL**
2. **Test registration functionality**
3. **Test QR code generation**
4. **Test admin dashboard**

## Benefits of GitHub + Railway:

✅ **Version control** - Track all changes
✅ **Easy updates** - Just push to GitHub
✅ **Automatic deployment** - Railway deploys automatically
✅ **Collaboration** - Others can contribute
✅ **Backup** - Your code is safe on GitHub
✅ **Professional** - Industry standard workflow

## Troubleshooting:

### If Git not found after restart:
1. **Check PATH** environment variable
2. **Reinstall Git** with "Add to PATH" option
3. **Use Git Bash** instead of Command Prompt

### If GitHub push fails:
1. **Check repository URL**
2. **Verify GitHub credentials**
3. **Try using GitHub Desktop** (GUI alternative)

## Next Steps:

1. **Install Git and restart command prompt**
2. **Create GitHub repository**
3. **Push your code to GitHub**
4. **Connect Railway to GitHub**
5. **Test your deployed app**

This approach is much more professional and gives you better control over your deployment!
