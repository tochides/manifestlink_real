# 🚀 ManifestLink Deployment Checklist

## ✅ Phase 1: Firebase Hosting (Static Files)

### Prerequisites:
- [ ] Node.js installed (✅ Already done)
- [ ] Firebase CLI installed (✅ Already done)
- [ ] Google account for Firebase

### Steps:
1. [ ] **Firebase Login:** Run `firebase login` in terminal
2. [ ] **Create Firebase Project:** Go to [Firebase Console](https://console.firebase.google.com/)
3. [ ] **Initialize Firebase:** Run `firebase init hosting`
4. [ ] **Deploy Static Files:** Run `firebase deploy`
5. [ ] **Test Frontend:** Visit your Firebase hosting URL

### Files Created:
- ✅ `firebase.json` - Firebase hosting configuration
- ✅ `.firebaserc` - Project configuration
- ✅ `config.js` - Environment configuration
- ✅ `deploy.md` - Deployment instructions

---

## ✅ Phase 2: Backend Hosting (PHP API)

### Prerequisites:
- [ ] GitHub account
- [ ] Railway account (free)

### Steps:
1. [ ] **Push Code to GitHub:** 
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git push origin main
   ```
2. [ ] **Create Railway Account:** Go to [Railway.app](https://railway.app)
3. [ ] **Deploy Backend:** Connect GitHub repo to Railway
4. [ ] **Test Backend:** Check Railway deployment URL

### Files Created:
- ✅ `railway.json` - Railway configuration
- ✅ `composer.json` - PHP dependencies
- ✅ `api/index.php` - API entry point
- ✅ `railway-setup.md` - Railway setup guide

---

## ✅ Phase 3: Database Setup

### Steps:
1. [ ] **Add MySQL Service:** In Railway dashboard
2. [ ] **Import Database Structure:**
   ```bash
   mysql -h your-host -P your-port -u your-username -p your-database
   source manifestlink.sql;
   source manifest_table.sql;
   source otp_table.sql;
   ```
3. [ ] **Set Environment Variables:** In Railway dashboard
4. [ ] **Test Database Connection**

### Files Available:
- ✅ `manifestlink.sql` - Main database structure
- ✅ `manifest_table.sql` - Manifest table
- ✅ `otp_table.sql` - OTP verification table
- ✅ `database-setup.md` - Database setup guide

---

## ✅ Phase 4: Configuration & Testing

### Steps:
1. [ ] **Update config.js:** Add production URLs
2. [ ] **Test Complete System:**
   - [ ] User registration
   - [ ] QR code generation
   - [ ] OTP verification
   - [ ] Admin dashboard
3. [ ] **Fix any issues**
4. [ ] **Deploy final version**

---

## 🔧 Troubleshooting Common Issues

### Firebase Deployment Issues:
- **Problem:** `firebase: command not found`
- **Solution:** Reinstall Firebase CLI: `npm install -g firebase-tools`

### Railway Deployment Issues:
- **Problem:** PHP extension missing
- **Solution:** Add to `composer.json` requirements

### Database Connection Issues:
- **Problem:** Connection refused
- **Solution:** Check environment variables and firewall settings

---

## 📊 Cost Estimate

### Free Tier (Recommended for Testing):
- **Firebase Hosting:** Free (10GB bandwidth)
- **Railway:** Free (500 hours/month)
- **Railway MySQL:** Free (1GB storage)

### Paid Tier (Production):
- **Firebase Hosting:** Free (generous limits)
- **Railway:** $5/month (unlimited hours)
- **Railway MySQL:** $5/month (1GB storage)

**Total Production Cost: ~$10/month**

---

## 🎯 Final URLs

After deployment, you'll have:
- **Frontend:** `https://your-project.web.app`
- **Backend API:** `https://your-app.railway.app/api`
- **Admin Panel:** `https://your-app.railway.app/api/admin`

---

## 📞 Support

If you encounter issues:
1. Check the specific setup guides
2. Review error logs in Firebase Console and Railway dashboard
3. Test each component individually
4. Ask for help with specific error messages
