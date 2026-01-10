<?php
require_once "config.php";
require_once "pdo.php";
require_once("lib/util.php");

header('Content-type: application/json');

$p = $CFG->dbprefix;
$user_id = false;
if ( isset($_GET['pair']) ) {
    $row = pdoRowDie($pdo, "SELECT user_id FROM {$p}pair WHERE pair_key = :PK",
        array(':PK' => $_GET['pair']) );
    if ( $row !== false ) $user_id = $row['user_id'];
} else {
    session_start();
    if ( isset($_SESSION['user_id']) ) $user_id = $_SESSION['id'];
}

if ( $user_id === false ) {
	echo('{ "error" : "Not logged in"}');
    return;
}

$p = $CFG->dbprefix;
if ( isset($_GET['game']) ) { // I am player 1 since I made this game
	// Check if game exists and get its status
	$stmt = $pdo->prepare("SELECT rps_guid, user1_id, user2_id, play1, play2, started_at, displayname FROM {$p}rps 
		LEFT JOIN {$p}user ON {$p}rps.user2_id = {$p}user.user_id
		WHERE rps_guid = :GUID");
	$stmt->execute(array(":GUID" => $_GET['game']));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ( $row === FALSE ) {
		echo('{ "error" : "Row not found"}');
		return;
	}
	
	$game_guid = $row['rps_guid'];
	$user1_id = $row['user1_id'];
	$user2_id = $row['user2_id'];
	$already_recorded = false;
	
	// Check if wins/losses have already been recorded (check if finished_at is set)
	$check_finished = $pdo->prepare("SELECT finished_at FROM {$p}rps WHERE rps_guid = :GUID");
	$check_finished->execute(array(":GUID" => $game_guid));
	$finished_row = $check_finished->fetch(PDO::FETCH_ASSOC);
	if ( $finished_row && isset($finished_row['finished_at']) ) {
		$already_recorded = true;
	}
	
	// If game is not completed and older than 10 seconds, auto-complete with random play
	if ( !isset($row['play2']) ) {
		$started_at = strtotime($row['started_at']);
		$current_time = time();
		$elapsed = $current_time - $started_at;
		
		if ( $elapsed >= 10 ) {
			// Clean up old records (more than 10 minutes old)
			$cleanup_stmt = $pdo->prepare("DELETE FROM {$p}rps WHERE started_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
			$cleanup_stmt->execute();
			
			// Game expired - assign biased random play for opponent
			// Get win bias from config (default 5.0 means 5% more likely to win)
			$win_bias = isset($CFG->random_opponent_win_bias) ? $CFG->random_opponent_win_bias : 5.0;
			$win_probability = 50.0 + ($win_bias / 2.0); // Convert bias to win probability
			
			// Determine which play would make player 1 win vs lose
			$play1 = $row['play1'];
			$losing_play = ($play1 + 2) % 3; // Play that loses to play1 (makes player 1 win)
			$winning_play = ($play1 + 1) % 3; // Play that beats play1 (makes player 1 lose)
			
			// Use biased random selection
			$random_percent = mt_rand(0, 10000) / 100.0; // Random 0-100 with 2 decimal precision
			if ( $random_percent < $win_probability ) {
				// Player 1 wins - opponent plays the losing move
				$random_play = $losing_play;
			} else {
				// Player 1 loses - opponent plays the winning move
				$random_play = $winning_play;
			}
			
			// Update the game with biased random opponent play
			$update_stmt = $pdo->prepare("UPDATE {$p}rps SET play2 = :PLAY 
				WHERE rps_guid = :GUID AND play2 IS NULL");
			$update_stmt->execute(array(":PLAY" => $random_play, ":GUID" => $game_guid));
			
			// Re-fetch the row to get updated data
			$stmt->execute(array(":GUID" => $game_guid));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$user2_id = $row['user2_id']; // Will be NULL for random opponent
			
			// Set displayname to "Random Opponent" since there's no real player
			$row['displayname'] = 'Random Opponent';
		}
	}
	
	if ( isset($row['play2']) ) {
		$tie = $row['play1'] == $row['play2'];
		$row['tie'] = $tie;
		$lose = (($row['play1'] + 1) % 3) == $row['play2'];
		$row['win'] = ! $lose;
		
		// Record wins/losses if not already recorded
		if ( !$already_recorded ) {
			// Mark game as finished
			$finish_stmt = $pdo->prepare("UPDATE {$p}rps SET finished_at = NOW() WHERE rps_guid = :GUID");
			$finish_stmt->execute(array(":GUID" => $game_guid));
			
			if ( !$tie ) {
				// Player 1 wins or loses
				if ( $row['win'] ) {
					// Player 1 wins
					$update_wins = $pdo->prepare("UPDATE {$p}user SET wins = wins + 1 WHERE user_id = :UID");
					$update_wins->execute(array(":UID" => $user1_id));
				} else {
					// Player 1 loses
					$update_losses = $pdo->prepare("UPDATE {$p}user SET losses = losses + 1 WHERE user_id = :UID");
					$update_losses->execute(array(":UID" => $user1_id));
				}
				
				// Only update player 2 if it's a real player (not random opponent)
				if ( $user2_id !== NULL ) {
					if ( !$row['win'] ) {
						// Player 2 wins (player 1 lost)
						$update_wins2 = $pdo->prepare("UPDATE {$p}user SET wins = wins + 1 WHERE user_id = :UID");
						$update_wins2->execute(array(":UID" => $user2_id));
					} else {
						// Player 2 loses (player 1 won)
						$update_losses2 = $pdo->prepare("UPDATE {$p}user SET losses = losses + 1 WHERE user_id = :UID");
						$update_losses2->execute(array(":UID" => $user2_id));
					}
				}
			}
			// Ties don't increment wins or losses
		}
	}
	echo(json_encode($row));
	return;
}

$play = isset($_GET['play']) ? $_GET['play']+0 : -1;
if ( $play < 0 || $play > 2 ) {
	echo(json_encode(array("error" => "Bad value for play")));
	return;
}

// Check to see if there is an open game
$stmt = $pdo->prepare("SELECT rps_guid, user1_id, play1, play2, displayname FROM {$p}rps 
	LEFT JOIN {$p}user ON {$p}rps.user1_id = {$p}user.user_id
	WHERE play2 IS NULL ORDER BY started_at ASC LIMIT 1 FOR UPDATE");
$stmt1 = $pdo->prepare("UPDATE {$p}rps SET user2_id = :U2ID, play2 = :PLAY, finished_at = NOW()
	WHERE rps_guid = :GUID");

// Check to see if there is an open game we can complete
$pdo->beginTransaction();
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row == FALSE ) {
	$pdo->rollBack();
} else {
	$game_guid = $row['rps_guid'];
	$user1_id = $row['user1_id'];
	$user2_id = $user_id; // Current user is player 2
	
	$stmt1->execute(array(":U2ID" => $user_id, ":PLAY" => $play,
		":GUID" => $game_guid));
	$pdo->commit();
	
	$tie = $play == $row['play1'];
	$row['tie'] = $tie;
	// I am player 2 because I finshed this game
	$lose = (($play + 1) % 3) == $row['play1'];
	$row['win'] = ! $lose;
	
	// Record wins/losses
	if ( !$tie ) {
		// Player 2 wins or loses
		if ( $row['win'] ) {
			// Player 2 wins
			$update_wins2 = $pdo->prepare("UPDATE {$p}user SET wins = wins + 1 WHERE user_id = :UID");
			$update_wins2->execute(array(":UID" => $user2_id));
			// Player 1 loses
			$update_losses1 = $pdo->prepare("UPDATE {$p}user SET losses = losses + 1 WHERE user_id = :UID");
			$update_losses1->execute(array(":UID" => $user1_id));
		} else {
			// Player 2 loses
			$update_losses2 = $pdo->prepare("UPDATE {$p}user SET losses = losses + 1 WHERE user_id = :UID");
			$update_losses2->execute(array(":UID" => $user2_id));
			// Player 1 wins
			$update_wins1 = $pdo->prepare("UPDATE {$p}user SET wins = wins + 1 WHERE user_id = :UID");
			$update_wins1->execute(array(":UID" => $user1_id));
		}
	}
	// Ties don't increment wins or losses
	
	echo(json_encode($row));
	return;
}

// Start a new game...
$guid = uniqid();
$stmt = $pdo->prepare("INSERT INTO {$p}rps 
	(rps_guid, user1_id, play1, started_at) 
	VALUES ( :GUID, :UID, :PLAY, NOW() )");
$stmt->execute(array(":GUID" => $guid, 
	":UID" => $user_id, ":PLAY" => $play));

echo(json_encode(array("guid" => $guid)));

