<?php
include_once("config.php");
require_once("sanity.php");
require_once("pdo.php");
require_once("lib/util.php");

header('Content-Type: text/html; charset=utf-8');
session_start();

if ( !isset($_SESSION['id']) ) {
    $_SESSION['error'] = "Must be logged in to pair";
    header('Location: index.php');
    return;
}

$p = $CFG->dbprefix;
// Clean up old unfinished pairings
$stmt = pdoQueryDie($pdo, 
    "DELETE FROM {$p}pair WHERE user_id IS NULL OR paired_at IS NULL 
        AND created_at < (NOW() - INTERVAL 20 MINUTE)");

if ( isset($_POST['pair']) || isset($_POST['unpair']) ) {
    $stmt = pdoQueryDie($pdo, "DELETE FROM {$p}pair WHERE user_id = :UI",
        array(':UI' => $_SESSION['id']));
} 

if ( isset($_POST['pair']) ) {
    $guid = uniqid();
    $rnum = rand(100000,999999);
    $stmt = pdoQuery($pdo, "INSERT INTO {$p}pair 
        (pair_key, pair_guid, user_id, created_at) VALUES
        ( :PK, :PG, :UI, NOW() )",
        array(':UI' => $_SESSION['id'], ':PG' => $guid, ':PK' => $rnum));
}

if ( isset($_POST['pair']) || isset($_POST['unpair']) ) {
    header( 'Location: pair.php') ;
    return;
}

$row = pdoRowDie($pdo, "SELECT pair_key FROM {$p}pair WHERE user_id = :UI",
    array(':UI' => $_SESSION['id']));

headerContent();
startBody();
flashMessages();
if ( $row !== false ) {
    echo("<p>Your current pairing key is: ".$row['pair_key']."</p>\n");
} else {
    echo("<p>You do not have a pairing key set.</p>\n");
}
?>
<form method="post">
<?php if ( $row !== false ) { ?>
<input class="btn btn-primary" type="submit" name="unpair" value="Un-Pair"/>
<input class="btn btn-primary" type="submit" name="pair" value="Re-Pair"/>
<?php } else { ?>
<input class="btn btn-primary" type="submit" name="pair" value="Pair"/>
<?php } ?>
<input class="btn" type="button" onclick="location.href='index.php'; return false;" value="Done"/>
</form>
<?php
footerContent();
