# ManifestLink Deployment Guide

## Quick Deployment Commands

### 1. Deploy to Firebase Hosting
```bash
# Navigate to your project directory
cd C:\xampp\htdocs\manifestlink

# Deploy to Firebase
firebase deploy
```

### 2. Check deployment status
```bash
# View your deployed site
firebase hosting:channel:open

# View deployment history
firebase hosting:releases
```

### 3. Update your site
```bash
# After making changes, redeploy
firebase deploy --only hosting
```

## Important Notes

1. **Update config.js** with your backend URL after deploying PHP
2. **Test locally** before deploying
3. **Check Firebase Console** for deployment status
4. **Monitor usage** in Firebase Console

## Next Steps After Firebase Deployment

1. Set up PHP backend hosting (Railway/Heroku)
2. Set up MySQL database (PlanetScale)
3. Update config.js with production URLs
4. Test the complete system

## Troubleshooting

- If deployment fails, check `firebase.json` syntax
- Make sure you're logged in: `firebase login`
- Check project ID in `.firebaserc`
