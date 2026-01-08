<?php
require_once "config.php";
require_once 'lib/util.php';
require_once 'pdo.php';

session_start();

// OAuth 2.0 configuration for providers
$oauth_providers = array(
    'google' => array(
        'name' => 'Google',
        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
        'scopes' => 'openid email profile',
        'client_id' => isset($CFG->google_client_id) ? $CFG->google_client_id : '',
        'client_secret' => isset($CFG->google_client_secret) ? $CFG->google_client_secret : '',
    ),
    'github' => array(
        'name' => 'GitHub',
        'auth_url' => 'https://github.com/login/oauth/authorize',
        'token_url' => 'https://github.com/login/oauth/access_token',
        'userinfo_url' => 'https://api.github.com/user',
        'scopes' => 'user:email',
        'client_id' => isset($CFG->github_client_id) ? $CFG->github_client_id : '',
        'client_secret' => isset($CFG->github_client_secret) ? $CFG->github_client_secret : '',
    ),
    'patreon' => array(
        'name' => 'Patreon',
        'auth_url' => 'https://www.patreon.com/oauth2/authorize',
        'token_url' => 'https://www.patreon.com/api/oauth2/token',
        'userinfo_url' => 'https://www.patreon.com/api/oauth2/v2/identity',
        'scopes' => 'identity identity[email]',
        'client_id' => isset($CFG->patreon_client_id) ? $CFG->patreon_client_id : '',
        'client_secret' => isset($CFG->patreon_client_secret) ? $CFG->patreon_client_secret : '',
    ),
);

$errormsg = false;
$doLogin = false;
$identity = false;
$firstName = false;
$lastName = false;
$userEmail = false;
$provider = false;

// Handle OAuth callback
if (isset($_GET['code']) && isset($_GET['state'])) {
    $state = $_GET['state'];
    $code = $_GET['code'];
    
    // Verify state to prevent CSRF
    if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
        $errormsg = 'Invalid state parameter. Please try again.';
    } else {
        $provider = isset($_SESSION['oauth_provider']) ? $_SESSION['oauth_provider'] : false;
        
        if ($provider && isset($oauth_providers[$provider])) {
            $config = $oauth_providers[$provider];
            
            // Exchange code for access token
            $token_data = exchangeCodeForToken($config, $code);
            
            if ($token_data && isset($token_data['access_token'])) {
                // Get user info
                $user_info = getUserInfo($config, $token_data['access_token'], $provider);
                
                if ($user_info) {
                    $identity = $user_info['identity'];
                    $userEmail = $user_info['email'];
                    $firstName = $user_info['first_name'];
                    $lastName = $user_info['last_name'];
                    $doLogin = true;
                } else {
                    $errormsg = 'Failed to retrieve user information from ' . $config['name'];
                }
            } else {
                $errormsg = 'Failed to obtain access token from ' . $config['name'];
            }
        } else {
            $errormsg = 'Invalid provider';
        }
        
        // Clean up session
        unset($_SESSION['oauth_state']);
        unset($_SESSION['oauth_provider']);
    }
}

// Handle login initiation
if (isset($_GET['provider']) && isset($oauth_providers[$_GET['provider']])) {
    $provider = $_GET['provider'];
    $config = $oauth_providers[$provider];
    
    // Check if credentials are configured
    if (empty($config['client_id']) || empty($config['client_secret'])) {
        $errormsg = $config['name'] . ' OAuth is not configured. Please check config.php';
    } else {
        // Generate state for CSRF protection
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $provider;
        
        // Build authorization URL
        $redirect_uri = $CFG->wwwroot . '/login.php';
        $params = array(
            'client_id' => $config['client_id'],
            'redirect_uri' => $redirect_uri,
            'scope' => $config['scopes'],
            'state' => $state,
            'response_type' => 'code',
        );
        
        // Patreon requires additional parameter
        if ($provider === 'patreon') {
            $params['response_type'] = 'code';
        }
        
        // Google uses OpenID Connect
        if ($provider === 'google') {
            $params['access_type'] = 'offline';
            $params['prompt'] = 'consent';
        }
        
        $auth_url = $config['auth_url'] . '?' . http_build_query($params);
        
        header('Location: ' . $auth_url);
        exit;
    }
}

// Handle successful login
if ($doLogin) {
    if ($firstName === false || $lastName === false || $userEmail === false) {
        error_log('OAuth-Missing: ' . $provider . ',' . $identity . ',' . $firstName . ',' . $lastName . ',' . $userEmail);
        $_SESSION["error"] = "Unable to retrieve required information (name and email) from " . ($provider ? $oauth_providers[$provider]['name'] : 'provider');
        header('Location: index.php');
        exit;
    } else {
        $userSHA = lti_sha256($identity);
        $displayName = $firstName . ' ' . $lastName;

        // Get user's IP address
        $login_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $login_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        // Load the user checking to see if everything
        $user_row = pdoRowDie($pdo,
            "SELECT user_id, displayname, email, admin, provider FROM {$CFG->dbprefix}user
                WHERE user_sha256 = :SHA LIMIT 1",
            array(':SHA' => $userSHA)
        );

        // Make sure we have a user for this person
        $didinsert = false;
        $user_id = 0;
        $admin = 0;
        if ($user_row === false) {
            $stmt = pdoQueryDie($pdo,
                "INSERT INTO {$CFG->dbprefix}user  
                (user_sha256, user_key, email, displayname, provider, login_at, login_ip, created_at, updated_at) " .
                    "VALUES ( :SHA, :UKEY, :EMAIL, :DN, :PROVIDER, NOW(), :IP, NOW(), NOW() )",
                array(':SHA' => $userSHA, ':UKEY' => $identity,
                    ':EMAIL' => $userEmail, ':DN' => $displayName, 
                    ':PROVIDER' => $provider, ':IP' => $login_ip)
            );

            if ($stmt->success) $user_id = $pdo->lastInsertId();
            error_log('User-Insert: ' . $provider . ',' . $identity . ',' . $displayName . ',' . $userEmail . ',' . $user_id);
            $didinsert = true;
        } else {
            $user_id = $user_row['user_id'] + 0;
            $admin = $user_row['admin'] + 0;
            $stmt = pdoQueryDie($pdo,
                "UPDATE {$CFG->dbprefix}user  
                SET email = :EMAIL, displayname = :DN, provider = :PROVIDER, login_at = NOW(), login_ip = :IP
                WHERE user_id = :USER",
                array(':USER' => $user_id,
                    ':EMAIL' => $userEmail, ':DN' => $displayName,
                    ':PROVIDER' => $provider, ':IP' => $login_ip)
            );
            error_log('User-Update: ' . $provider . ',' . $identity . ',' . $displayName . ',' . $userEmail . ',' . $user_id);
        }

        if ($user_id < 1) {
            error_log('No User Entry: ' . $provider . ',' . $identity . ',' . $displayName . ',' . $userEmail);
            $_SESSION["error"] = "Internal database error, sorry";
            header('Location: index.php');
            exit;
        }

        // We made a user and made a displayname
        $welcome = "Welcome ";
        if (!$didinsert) $welcome .= "back ";
        $_SESSION["success"] = $welcome . ($displayName) . " (" . $userEmail . ")";
        $_SESSION["id"] = $user_id;
        $_SESSION["email"] = $userEmail;
        $_SESSION["displayname"] = $displayName;
        $_SESSION["user_id"] = $user_id;
        $_SESSION["admin"] = $admin;
        // Set the secure cookie
        $guid = MD5($identity);
        $ct = create_secure_cookie($user_id, $guid);
        setcookie($CFG->cookiename, $ct, time() + (86400 * 45)); // 86400 = 1 day

        header('Location: index.php');
        exit;
    }
}

// Display login page
headerContent();
startBody();
flashMessages();
require_once("sanity-db.php");
?>
<div style="margin: 15px">
<?php if ($errormsg) { ?>
<div class="alert alert-danger">
<p><strong>Error:</strong> <?php echo htmlspecialchars($errormsg); ?></p>
</div>
<?php } ?>
<p>
We here at <?php echo($CFG->servicename); ?> use OAuth authentication for login.
We do not want to spend a lot of time verifying identity, resetting passwords,
detecting robot-login storms, and other issues so we let trusted providers do that hard work.
</p>
<p>Please choose a login provider:</p>
<div style="margin: 20px 0;">
<?php foreach ($oauth_providers as $key => $provider_config): ?>
    <?php if (!empty($provider_config['client_id']) && !empty($provider_config['client_secret'])): ?>
    <div style="margin: 10px 0;">
        <a href="login.php?provider=<?php echo htmlspecialchars($key); ?>" class="btn btn-primary" style="min-width: 200px;">
            Login with <?php echo htmlspecialchars($provider_config['name']); ?>
        </a>
    </div>
    <?php else: ?>
    <div style="margin: 10px 0;">
        <button class="btn btn-default" disabled style="min-width: 200px;">
            <?php echo htmlspecialchars($provider_config['name']); ?> (Not Configured)
        </button>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
</div>
<div style="margin-top: 20px;">
    <a href="index.php" class="btn btn-warning">Cancel</a>
</div>
<p style="margin-top: 20px;">
You must have an account with one of these providers. We will require your
name and email address to login. We do not need and do not receive your password - only the provider
will ask you for your password. When you press login, you will be directed to the provider's
authentication system where you will be given the option to share your
information with <?php echo($CFG->servicename); ?>.
</p>
</div>
<?php
footerContent();

// Helper function to exchange authorization code for access token
function exchangeCodeForToken($config, $code) {
    $redirect_uri = $GLOBALS['CFG']->wwwroot . '/login.php';
    
    $data = array(
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
    );
    
    $ch = curl_init($config['token_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    
    // GitHub requires different content type
    if (strpos($config['token_url'], 'github.com') !== false) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $token_data = json_decode($response, true);
        return $token_data;
    } else {
        error_log('Token exchange failed: HTTP ' . $http_code . ' - ' . $response);
        return false;
    }
}

// Helper function to get user info from provider
function getUserInfo($config, $access_token, $provider) {
    $ch = curl_init($config['userinfo_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $access_token,
        'User-Agent: MMORPS',
        'Accept: application/json'
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $user_data = json_decode($response, true);
        
        // Normalize user data from different providers
        $result = array(
            'identity' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
        );
        
        if ($provider === 'google') {
            $result['identity'] = $user_data['id'];
            $result['email'] = isset($user_data['email']) ? $user_data['email'] : '';
            $result['first_name'] = isset($user_data['given_name']) ? $user_data['given_name'] : '';
            $result['last_name'] = isset($user_data['family_name']) ? $user_data['family_name'] : '';
        } elseif ($provider === 'github') {
            $result['identity'] = (string)$user_data['id'];
            $result['email'] = isset($user_data['email']) ? $user_data['email'] : '';
            $name = isset($user_data['name']) ? $user_data['name'] : '';
            $name_parts = explode(' ', $name, 2);
            $result['first_name'] = $name_parts[0];
            $result['last_name'] = isset($name_parts[1]) ? $name_parts[1] : '';
            
            // GitHub email might be private, try to get from email API
            if (empty($result['email'])) {
                $email_data = getGitHubEmail($access_token);
                if ($email_data) {
                    $result['email'] = $email_data;
                }
            }
        } elseif ($provider === 'patreon') {
            $result['identity'] = $user_data['data']['id'];
            $result['email'] = isset($user_data['data']['attributes']['email']) ? $user_data['data']['attributes']['email'] : '';
            $name = isset($user_data['data']['attributes']['full_name']) ? $user_data['data']['attributes']['full_name'] : '';
            $name_parts = explode(' ', $name, 2);
            $result['first_name'] = $name_parts[0];
            $result['last_name'] = isset($name_parts[1]) ? $name_parts[1] : '';
        }
        
        return $result;
    } else {
        error_log('User info failed: HTTP ' . $http_code . ' - ' . $response);
        return false;
    }
}

// Helper function to get GitHub email (since it might be private)
function getGitHubEmail($access_token) {
    $ch = curl_init('https://api.github.com/user/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $access_token,
        'User-Agent: MMORPS',
        'Accept: application/json'
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $emails = json_decode($response, true);
        if (is_array($emails) && count($emails) > 0) {
            // Find primary email or first verified email
            foreach ($emails as $email_data) {
                if (isset($email_data['primary']) && $email_data['primary']) {
                    return $email_data['email'];
                }
            }
            foreach ($emails as $email_data) {
                if (isset($email_data['verified']) && $email_data['verified']) {
                    return $email_data['email'];
                }
            }
            // Return first email if no primary/verified found
            return $emails[0]['email'];
        }
    }
    return false;
}
