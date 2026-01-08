mmorps
======

Massive MultiPlayer Online Rock-Paper-Scissors

A PHP-based web application for playing Rock-Paper-Scissors with other players online.

## Requirements

- PHP 8.4 or later
- MySQL 8.0 or later (or MariaDB equivalent)
- Web server (Apache, Nginx, or PHP built-in server)
- OpenSSL extension (usually included with PHP)

## Installation

1. Clone or download this repository to your web server directory
2. Copy `config-dist.php` to `config.php`
3. Edit `config.php` with your settings (see Configuration below)
4. Set up your database (see Database Setup below)
5. Configure at least one OAuth provider for login (see OAuth Setup below)
6. Visit your site in a browser - the database tables will be created automatically

## Configuration

Edit `config.php` (never commit this file - it contains secrets):

### Basic Settings

```php
// Service name displayed to users
$CFG->servicename = 'MMORPS';

// URL where the software is hosted (no trailing slash)
$CFG->wwwroot = 'http://localhost:8888/mmorps';

// Timezone
$CFG->timezone = 'America/New_York';

// Admin password for administration features
$CFG->adminpw = 'your-secure-password';
```

### Database Configuration

```php
// Database connection string
$CFG->pdo = 'mysql:host=127.0.0.1;port=3306;dbname=mmorps';
$CFG->dbuser = 'your-db-user';
$CFG->dbpass = 'your-db-password';

// Optional: table prefix if you share a database
$CFG->dbprefix = '';
```

### Database Setup

Create your database and user:

```sql
CREATE DATABASE IF NOT EXISTS mmorps DEFAULT CHARACTER SET utf8;
CREATE USER IF NOT EXISTS 'mmouser'@'localhost' IDENTIFIED BY 'mmopassword';
CREATE USER IF NOT EXISTS 'mmouser'@'127.0.0.1' IDENTIFIED BY 'mmopassword';
GRANT ALL ON mmorps.* TO 'mmouser'@'localhost';
GRANT ALL ON mmorps.* TO 'mmouser'@'127.0.0.1';
FLUSH PRIVILEGES;
```

The application will automatically create the required tables on first run.

### OAuth Provider Setup

At least one OAuth provider must be configured for login to work. The application supports:

- **Google** - OAuth 2.0
- **GitHub** - OAuth 2.0  
- **Patreon** - OAuth 2.0

#### Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create a new OAuth 2.0 Client ID
3. Set authorized redirect URI to: `{your-wwwroot}/login.php`
   - Example: `http://localhost:8888/mmorps/login.php`
4. Add to `config.php`:
   ```php
   $CFG->google_client_id = 'your-client-id.apps.googleusercontent.com';
   $CFG->google_client_secret = 'your-client-secret';
   ```

#### GitHub OAuth Setup

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Click "New OAuth App"
3. Set Authorization callback URL to: `{your-wwwroot}/login.php`
   - Example: `http://localhost:8888/mmorps/login.php`
4. Add to `config.php`:
   ```php
   $CFG->github_client_id = 'your-client-id';
   $CFG->github_client_secret = 'your-client-secret';
   ```

#### Patreon OAuth Setup

1. Go to [Patreon Portal](https://www.patreon.com/portal/registration/register-clients)
2. Register a new client
3. Set Redirect URI to: `{your-wwwroot}/login.php`
   - Example: `http://localhost:8888/mmorps/login.php`
4. Add to `config.php`:
   ```php
   $CFG->patreon_client_id = 'your-client-id';
   $CFG->patreon_client_secret = 'your-client-secret';
   ```

**Important:** Make sure the redirect/callback URL matches exactly, including the protocol (http/https) and port number.

### Security Settings

```php
// Secret key for encrypting cookies (change this to a random string)
$CFG->cookiesecret = 'something-highly-secret-change-this';

// Cookie name
$CFG->cookiename = 'MMPRPSAUTO';

// Cookie padding for validation
$CFG->cookiepad = '390b426ea9';
```

**Security Note:** Change `cookiesecret` to a long, random string. You can generate one with:
```php
echo bin2hex(random_bytes(32));
```

### Optional Settings

```php
// Static files root (usually same as wwwroot)
$CFG->staticroot = $CFG->wwwroot;

// Analytics (set to false to disable)
$CFG->analytics_key = false;
$CFG->analytics_name = false;

// Developer mode
$CFG->DEVELOPER = true;
```

## Features

- OAuth 2.0 authentication (Google, GitHub, Patreon)
- Real-time Rock-Paper-Scissors gameplay
- Leaderboard tracking
- User profiles
- Mobile device pairing
- Secure cookie-based session management

## File Structure

- `config.php` - Configuration (not in git, copy from config-dist.php)
- `login.php` - OAuth login handler
- `index.php` - Main game interface
- `play.php` - Game logic API
- `lib/util.php` - Utility functions
- `lib/crypt/` - (removed, now using PHP built-in OpenSSL)
- `sanity-db.php` - Database initialization

## Upgrading

If you're upgrading from an older version:

1. Backup your database
2. Update the code files
3. The application will automatically add new columns if needed (like the `provider` column)
4. Users may need to log in again if cookie encryption changed

## Troubleshooting

### Login not working
- Verify OAuth credentials are correct in `config.php`
- Check that redirect URI matches exactly in OAuth provider settings
- Check PHP error logs for details

### Database errors
- Verify database credentials in `config.php`
- Ensure database user has CREATE TABLE permissions
- Check MySQL error logs

### CSS/JavaScript not loading
- Verify `$CFG->wwwroot` matches your actual URL
- Check that `static/` folder is accessible
- Verify `$CFG->staticroot` is set correctly

## License

See LICENSE file for details.

## Support

For issues and questions, please check the GitHub repository.
