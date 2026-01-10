<?php
require_once "config.php";
require_once "pdo.php";
require_once "lib/util.php";

session_start();
header('Content-type: application/json');

$p = $CFG->dbprefix;
// Get users with their wins and losses, calculate net score (wins - losses)
$stmt = $pdo->prepare("SELECT user_id, displayname, wins, losses, 
		(wins - losses) AS net_score,
		(wins + losses) AS total_games
		FROM {$p}user 
		WHERE wins > 0 OR losses > 0
		ORDER BY (wins - losses) DESC, wins DESC
		LIMIT 20");
$stmt->execute(array());

$results = array();
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
	$results[] = array(
		"name" => $row['displayname'], 
		"wins" => (int)$row['wins'], 
		"losses" => (int)$row['losses'],
		"games" => (int)$row['total_games'],
		"score" => (int)$row['net_score']
	);
}
echo(json_encode($results));
