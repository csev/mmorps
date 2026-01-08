<?php

if ( isset($CFG->upgrading) && $CFG->upgrading === true ) require_once("upgrading.php");

if ( ! isset($CFG) ) die("Please configure this product using config.php");
if ( ! isset($CFG->staticroot) ) die('$CFG->staticroot not defined in config.php');
if ( ! isset($CFG->timezone) ) die('$CFG->timezone not defined in config.php');
if ( strpos($CFG->dbprefix, ' ') !== false ) die('$CFG->dbprefix cannot have spaces in it');

error_reporting(E_ALL & ~E_NOTICE);
error_reporting(E_ALL );
ini_set('display_errors', 1);

date_default_timezone_set($CFG->timezone);

function htmlspec_utf8($string) {
    return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
}

function htmlent_utf8($string) {
    return htmlentities($string,ENT_QUOTES,'UTF-8');
}

// Makes sure a string is safe as an href
function safe_href($string) {
    return str_replace(array('"', '<'),
        array('&quot;',''), $string);
}

// Convienence method to wrap sha256
function lti_sha256($val) {
    return hash('sha256', $val);
}

function sessionize($url) {
    if ( ini_get('session.use_cookies') != '0' ) return $url;
    $parameter = session_name().'='.session_id();
    if ( strpos($url, $parameter) !== false ) return $url;
    $url = $url . (strpos($url,'?') > 0 ? "&" : "?");
    $url = $url . $parameter;
    return $url;
}

// No trailer
