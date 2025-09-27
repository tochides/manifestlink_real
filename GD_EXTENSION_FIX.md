# Fix GD Extension Error in XAMPP

## The Problem
You're getting this error:
```
Fatal error: Uncaught Error: Call to undefined function ImageCreate()
```

This happens because the GD extension is not enabled in your PHP installation.

## Step-by-Step Solution

### Step 1: Check Current Status
1. Open your browser and go to: `http://localhost/manifestlink/check_gd.php`
2. This will show you if GD is enabled and where your php.ini file is located

### Step 2: Enable GD Extension

#### Method 1: Using XAMPP Control Panel
1. Open **XAMPP Control Panel**
2. Click **Config** button next to Apache
3. Select **PHP (php.ini)**
4. This will open the php.ini file in a text editor

#### Method 2: Direct File Access
1. Navigate to: `C:\xampp\php\php.ini`
2. Open the file with Notepad or any text editor

### Step 3: Find and Enable GD Extension
1. In the php.ini file, press **Ctrl+F** to search
2. Search for: `extension=gd`
3. You'll likely find a line like this:
   ```
   ;extension=gd
   ```
   or
   ```
   ;extension=gd2
   ```
4. **Remove the semicolon (;) at the beginning** so it becomes:
   ```
   extension=gd
   ```
   or
   ```
   extension=gd2
   ```

### Step 4: Save and Restart
1. **Save** the php.ini file
2. Go back to **XAMPP Control Panel**
3. **Stop** Apache (click the Stop button)
4. **Start** Apache again (click the Start button)

### Step 5: Verify the Fix
1. Refresh the page: `http://localhost/manifestlink/check_gd.php`
2. You should now see: "✅ GD extension is ENABLED"

### Step 6: Test Your QR Code Generation
1. Try your registration process again
2. The QR code should now generate successfully

## Alternative: If GD is Still Not Working

If you still have issues after following the steps above:

1. **Check for multiple php.ini files:**
   - Sometimes there are multiple php.ini files
   - Make sure you're editing the one that's actually being used

2. **Verify the correct php.ini location:**
   - Run the check_gd.php script to see the exact location
   - Make sure you're editing the right file

3. **Check XAMPP version:**
   - Make sure you're using a recent version of XAMPP
   - GD extension should be included by default

4. **Manual installation (if needed):**
   - Download the GD extension DLL for your PHP version
   - Place it in the `C:\xampp\php\ext\` directory
   - Add the extension line to php.ini

## Common Issues and Solutions

### Issue: Can't find php.ini
- Look in `C:\xampp\php\php.ini`
- If not there, check `C:\xampp\apache\bin\php.ini`

### Issue: Changes not taking effect
- Make sure you saved the file
- Make sure you restarted Apache completely
- Clear your browser cache

### Issue: Still getting the error
- Check if there are multiple PHP installations
- Verify you're editing the correct php.ini file
- Check Apache error logs for more details

## After Fixing
Once GD is enabled, your QR code generation should work properly. The `ImageCreate()` function will be available and your phpqrcode library will function correctly. 