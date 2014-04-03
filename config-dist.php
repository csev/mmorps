<?php 

// Configuration file - copy from config-dist.php to config.php
// and then edit.  Since this has passwords and other secrets
// never check config.php into a source repository

// We store the configuration in a global object
unset($CFG);
global $CFG;
$CFG = new stdClass();

// Set to true to redirect to the upgrading.php script
// Also copy upgrading-dist.php to upgrading.php and add your message
$CFG->upgrading = false;

// This is how the system will refer to itself.
$CFG->servicename = 'MMORPS';

// This is the URL where the software is hosted
// Do not add a trailing slash to this string 
// If you get this value wrong, the first problem will be that CSS files will not load
# $CFG->wwwroot = 'http://localhost/mmorps';
$CFG->wwwroot = 'http://localhost:8888/mmorps';   // For MAMP

// Database connection information to configure the PDO connection
// You need to point this at a database with am account and password
// that can create tables.   To make the initial tables go into Admin
// to run the upgrade.php script which auto-creates the tables.
# $CFG->pdo       = 'mysql:host=127.0.0.1;dbname=tsugi';
$CFG->pdo       = 'mysql:host=127.0.0.1;port=8889;dbname=mmorps'; // MAMP
$CFG->dbuser    = 'mmouser';
$CFG->dbpass    = 'mmopassword';

// The dbprefix allows you to give all the tables a prefix
// in case your hosting only gives you one database.  This
// can be short like "t_" and can even be an empty string if you 
// can make a separate database for each instance of TSUGI.
// This allows you to host multiple instances of TSUIG in a 
// single database if your hosting choices are limited.
$CFG->dbprefix  = '';

// This is the PW that you need to access the Administration
// features of this application.
$CFG->adminpw = 'short'; 

// When this is true it enables a Developer test harness that can launch
// tools using LTI.  It allows quick testing without setting up an LMS 
// course, etc.
$CFG->DEVELOPER = true;

// Default time zone - see http://www.php.net/....
$CFG->timezone = 'America/New_York';

// This allows you to serve the materials in the static folder using 
// a content distribution network - it is normal and typical for this 
// to be the same as wwwroot
$CFG->staticroot = $CFG->wwwroot;

// These values configure the cookie used to record the overall 
// login in a long-lived encrypted cookie.   Look at the library 
// code create_secure_cookie() for more detail on how these operate.
$CFG->cookiesecret = 'something-highly-secret-2f518066bd757a289b543';
$CFG->cookiename = 'MMPRPSAUTO';
$CFG->cookiepad = '390b426ea9'; 

// Set to false if you do not want analytics - this uses the ga.js
// analytics and sets three custom parameters 
// (oauth_consumer_key, context_id, and context_title) 
// is they are set.
$CFG->analytics_key = false;  // "UA-423997-16";
$CFG->analytics_name = false; // "dr-chuck.com";

// The path to config.php (this file).  This should not be changed.  
// It allows files to reference library files with an absolute path.
$CFG->dirroot = realpath(dirname(__FILE__));

// Leave these here
require_once $CFG->dirroot."/setup.php";
require_once $CFG->dirroot."/lib/util.php";

// No trailing tag to avoid inadvertent white space
