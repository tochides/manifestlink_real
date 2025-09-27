# Database Setup Options for ManifestLink

## Option 1: Railway MySQL (Recommended - Easiest)

### Steps:
1. **In your Railway project dashboard**
2. **Click "Add Service" → "Database" → "MySQL"**
3. **Railway will automatically create a MySQL database**
4. **Copy the connection details** from the service
5. **Import your database structure:**

```bash
# Connect to your Railway MySQL database
mysql -h your-host -P your-port -u your-username -p your-database

# Import your tables
source manifestlink.sql;
source manifest_table.sql;
source otp_table.sql;
```

### Environment Variables for Railway:
```
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=your-password
```

---

## Option 2: PlanetScale (Free Tier)

### Steps:
1. **Go to [PlanetScale.com](https://planetscale.com)**
2. **Create free account**
3. **Create new database** called `manifestlink`
4. **Get connection details**
5. **Import your database structure**

### PlanetScale Connection:
```
DB_HOST=aws.connect.psdb.cloud
DB_PORT=3306
DB_NAME=manifestlink
DB_USER=your-username
DB_PASSWORD=your-password
```

---

## Option 3: DigitalOcean Managed Database

### Steps:
1. **Create DigitalOcean account**
2. **Go to Databases → Create Database**
3. **Choose MySQL**
4. **Select $15/month plan (cheapest)**
5. **Import your database structure**

---

## Database Migration Script

Create this script to help with migration:

```php
<?php
// migrate-database.php
require_once 'connect.php';

echo "Starting database migration...\n";

// Read and execute SQL files
$sqlFiles = [
    'manifestlink.sql',
    'manifest_table.sql', 
    'otp_table.sql'
];

foreach ($sqlFiles as $file) {
    if (file_exists($file)) {
        $sql = file_get_contents($file);
        
        // Split by semicolon and execute each statement
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                if ($conn->query($statement)) {
                    echo "✅ Executed: " . substr($statement, 0, 50) . "...\n";
                } else {
                    echo "❌ Error: " . $conn->error . "\n";
                }
            }
        }
    }
}

echo "Migration completed!\n";
?>
```

## Recommended Choice: Railway MySQL

**Why Railway is best for beginners:**
- ✅ **Free tier** available
- ✅ **Integrated with your backend** deployment
- ✅ **Automatic backups**
- ✅ **Easy scaling**
- ✅ **Built-in connection management**
- ✅ **No separate billing**

## Next Steps:
1. **Deploy backend to Railway**
2. **Add MySQL service in Railway**
3. **Import your database structure**
4. **Update environment variables**
5. **Test the connection**
