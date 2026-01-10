<?php
include_once("config.php");
require_once("sanity.php");
require_once("lib/util.php");

header('Content-Type: text/html; charset=utf-8');
session_start();


$admin = isset($_SESSION['admin']) && $_SESSION['admin'] == 1;
// The reset operation is a normal POST - not AJAX
if ( $admin && isset($_POST['reset']) ) {
    $sql = "DELETE FROM {$p}rps WHERE link_id = :LI";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':LI' => $LTI['link_id']));
    header( 'Location: index.php') ;
    return;
}

headerContent();
?>
</head>
<body>
  <form method="post" id="actionform">
    <div class="container">
      <!-- Static navbar -->
      <div class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php"><?php echo($CFG->servicename); ?></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="about.php">About</a></li>
            <?php if ( isset($_SESSION['id']) ) { ?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo($_SESSION['displayname']);?><b class="caret"></b></a>
              <ul class="dropdown-menu">
				<li><a href="profile.php">Profile</a></li>
				<?php /* <li><a href="pair.php">Pair wih a Mobile Device</a></li> */ ?>
				<li><a href="logout.php">Logout</a></li>
              </ul>
            </li>
            <?php } else { ?>
            <li><a href="login.php">Login</a></li>
            <?php } ?>
          </ul>
        </div><!--/.nav-collapse -->
      </div>

      <div>
<?php
flashMessages();

if ( !isset($_SESSION['id']) ) {
    echo("<p>Please log in to play RPS</p>");
    echo("</div> <!-- container -->\n");
    footerContent(); 
    return;
}
?>
<p>
<form id="rpsform" method="post">
<input type="submit" id="rock" name="rock" value="Rock"/>
<input type="submit" id="paper" name="paper" value="Paper"/>
<input type="submit" id="scissors" name="scissors" value="Scissors"/>
<?php if ( $admin ) { ?>
<input type="submit" name="reset" value="Reset"/>
<?php } ?>
</form>
<p id="error" style="color:red"></p>
<p id="success" style="color:green"></p>
<p id="status" style="display:none">
<img id="spinner" src="spinner.gif">
<span id="statustext" style="color:orange"></span>
</p>
<div>
<p><b>Leaderboard</b></p>
<p id="leaders">
</p>
</div> <!-- /container -->
<?php
footerStart();
?>
<script type="text/javascript">
$(document).ready(function(){ 
  window.console && console.log('Hello JQuery..');
  $("#rock").click( function(event) { play(0); event.preventDefault(); } ) ;
  $("#paper").click( function(event) { play(1); event.preventDefault(); } ) ;
  $("#scissors").click( function(event) { play(2); event.preventDefault(); } ) ;
});

function play(strategy) {
	$("#success").html("");
	$("#error").html("");
	$("#statustext").html("Playing...");
	$("#rpsform input").attr("disabled", true);
	$("#status").show();
	window.console && console.log('Played '+strategy);
	$.getJSON('play.php?play='+strategy, function(data) {
		window.console && console.log(data);
		if ( data.guid ) {
			$("#statustext").html("Waiting for opponent...");
			check(data.guid); // Start the checking process
		} else {
			$("#status").hide();
			if ( data.tie ) {
				$("#success").html("You tied "+data.displayname);
			} else if ( data.win ) {
				$("#success").html("You beat "+data.displayname);
			} else { 
				$("#success").html("You lost to "+data.displayname);
			}
			$("#rpsform input").attr("disabled", false);
			leaders();  // Immediately update the leaderboard
		}
  });
  return false;
}

var GLOBAL_GUID;
function check(guid) {
	GLOBAL_GUID = guid;
	window.console && console.log('Checking game '+guid);
	$.getJSON('play.php?game='+guid)
		.done(function(data) {
			window.console && console.log(data);
			window.console && console.log(GLOBAL_GUID);
			if ( data.error ) {
				window.console && console.log("Error: " + data.error);
				// Retry after 4 seconds even on error
				setTimeout(function() { check(GLOBAL_GUID); }, 4000);
				return;
			}
			if ( ! data.displayname ) {
				window.console && console.log("Need to wait some more...");
				setTimeout(function() { check(GLOBAL_GUID); }, 4000);
				return;
			}
			$("#status").hide();
			if ( data.tie ) {
				$("#success").html("You tied "+data.displayname);
			} else if ( data.win ) {
				$("#success").html("You beat "+data.displayname);
			} else { 
				$("#success").html("You lost to "+data.displayname);
			}
			$("#rpsform input").attr("disabled", false);
			leaders();  // Immediately update the leaderboard
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			window.console && console.log("AJAX error: " + textStatus + " - " + errorThrown);
			// Retry after 4 seconds even on failure
			setTimeout(function() { check(GLOBAL_GUID); }, 4000);
		});
}

var OLD_TIMEOUT = false;
function leaders() {
	if ( OLD_TIMEOUT ) {
		clearTimeout(OLD_TIMEOUT);
		OLD_TIMEOUT = false;
	}
	window.console && console.log('Updating leaders...');
	$.getJSON('stats.php', function(data) {
		window.console && console.log(data);
		$("#leaders").html("");
		$("#leaders").append("<ol>\n");
		for (var i = 0; i < data.length; i++) {
			entry = data[i];
			$("#leaders").append("<li>"+entry.name+' - Wins: '+entry.wins+', Losses: '+entry.losses+' (Net: '+entry.score+')</li>\n');
			console.log(data[i]);
		}
		$("#leaders").append("</ol>\n");
		OLD_TIMEOUT = setTimeout('leaders()', 20000);
  });
}

// Run for the first time
leaders();
</script>
<?php
footerEnd();
