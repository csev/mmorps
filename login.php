<?php
require_once "config.php";
require_once "pdo.php";
require_once 'lib/util.php';
require_once 'lib/lightopenid/openid.php';

session_start();

$errormsg = false;
$success = false;

$doLogin = false;
$identity = false;
$firstName = false;
$lastName = false;
$userEmail = false;

try {
    $openid = new LightOpenID($CFG->wwwroot);
    if(!$openid->mode) {
        if(isset($_GET['login'])) {
            $openid->identity = 'https://www.google.com/accounts/o8/id';
            $openid->required = array('contact/email', 'namePerson/first', 'namePerson/last');
            $openid->optional = array('namePerson/friendly');
            header('Location: ' . $openid->authUrl());
            return;
        }
    } else {
        if($openid->mode == 'cancel') {
            $errormsg = "You have canceled authentication. That's OK but we cannot log you in.  Sorry.";
            error_log('Google-Cancel');
        } else if ( ! $openid->validate() ) {
            $errormsg = 'You were not logged in by Google.  It may be due to a technical problem.';
            error_log('Google-Fail');
        } else {
            $identity = $openid->identity;
            $userAttributes = $openid->getAttributes();
            // echo("\n<pre>\n");print_r($userAttributes);echo("\n</pre>\n");
            $firstName = isset($userAttributes['namePerson/first']) ? $userAttributes['namePerson/first'] : false;
            $lastName = isset($userAttributes['namePerson/last']) ? $userAttributes['namePerson/last'] : false;
            $userEmail = isset($userAttributes['contact/email']) ? $userAttributes['contact/email'] : false;
            $doLogin = true;
        }
    }
} catch(ErrorException $e) {
    $errormsg = $e->getMessage();
}

if ( $doLogin ) {
    if ( $firstName === false || $lastName === false || $userEmail === false ) {
        error_log('Google-Missing:'.$identity.','.$firstName.','.$lastName.','.$userEmail);
        $_SESSION["error"] = "You do not have a first name, last name, and email in Google or you did not share it with us.";
        header('Location: index.php');
        return;
    } else {
        $userSHA = lti_sha256($identity);
        $displayName = $firstName . ' ' . $lastName;

        // Load the user checking to see if everything
        $user_row = pdoRowDie($pdo,
            "SELECT user_id, displayname, email, admin FROM {$CFG->dbprefix}user
                WHERE user_sha256 = :SHA LIMIT 1",
            array(':SHA' => $userSHA)
        );

        // Make sure we have a user for this person
        $didinsert = false;
        $user_id = 0;
        $admin = 0;
        if ( $user_row === false ) {
            $stmt = pdoQueryDie($pdo,
                "INSERT INTO {$CFG->dbprefix}user  
                (user_sha256, user_key, email, displayname, created_at, updated_at, login_at) ".
                    "VALUES ( :SHA, :UKEY, :EMAIL, :DN, NOW(), NOW(), NOW() )",
                 array(':SHA' => $userSHA, ':UKEY' => $identity,
                    ':EMAIL' => $userEmail, ':DN' => $displayName)
            );

            if ( $stmt->success ) $user_id = $pdo->lastInsertId();
            error_log('User-Insert:'.$identity.','.$displayName.','.$userEmail.','.$user_id);
            $didinsert = true;
        } else {
            $user_id = $user_row['user_id']+0;
            $admin = $user_row['admin']+0;
            $stmt = pdoQueryDie($pdo,
                "UPDATE {$CFG->dbprefix}user  
                SET email = :EMAIL, displayname = :DN, login_at = NOW()
                WHERE user_id = :USER",
                 array(':USER' => $user_id, 
                    ':EMAIL' => $userEmail, ':DN' => $displayName)
            );
            error_log('User-Update:'.$identity.','.$displayName.','.$userEmail.','.$user_id);
        }

        if ( $user_id < 1 ) {
             error_log('No User Entry:'.$identity.','.$displayName.','.$userEmail);
             $_SESSION["error"] = "Internal database error, sorry";
             header('Location: index.php');
             return;
         }

        // We made a user and made a displayname
        $welcome = "Welcome ";
        if ( ! $didinsert ) $welcome .= "back ";
        $_SESSION["success"] = $welcome.($displayName)." (".$userEmail.")";
        $_SESSION["id"] = $user_id;
        $_SESSION["email"] = $userEmail;
        $_SESSION["displayname"] = $displayName;
        $_SESSION["user_id"] = $user_id;
        $_SESSION["admin"] = $admin;
        // Set the secure cookie
        $guid = MD5($identity);
        $ct = create_secure_cookie($user_id,$guid);
        setcookie($CFG->cookiename,$ct,time() + (86400 * 45)); // 86400 = 1 day

        if ( $didinsert ) {
            // header('Location: profile.php');
            header('Location: index.php');
        } else {
            header('Location: index.php');
        }
        return;
    }
}
headerContent();
startBody();
flashMessages();
require_once("sanity-db.php");
?>
<div style="margin: 15px">
<p>
We here at <?php echo($CFG->servicename); ?> use Google Accounts as our sole login.  
We do not want to spend a lot of time verifying identity, resetting passwords, 
detecting robot-login storms, and other issues so we let Google do that hard work. 
</p>
<form action="?login" method="post">
    <input class="btn btn-warning" type="button" onclick="location.href='index.php'; return false;" value="Cancel"/>
    <button class="btn btn-primary">Login with Google</button>
</form>
<p>
So you must have a Google account and we will require your
name and email address to login.  We do not need and do not receive your password - only Google
will ask you for your password.  When you press login, you will be directed to the Google
authentication system where you will be given the option to share your 
information with <?php echo($CFG->servicename); ?>.
</p>
</div>
<?php
footerContent();
