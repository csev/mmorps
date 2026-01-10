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
    <div class="container" style="position: relative;">
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
				<li><a href="privacy.php">Privacy Policy</a></li>
				<li><a href="service.php">Terms of Service</a></li>
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

<div style="float: right; margin-left: 20px; vertical-align: top;">
  <a href="about.php">
    <img src="logo.png" alt="MMORPS Logo" style="max-width: 25vw; max-height: 150px; height: auto; width: auto; vertical-align: top;">
  </a>
</div>

<p style="margin-top: 0;">
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
document.addEventListener('DOMContentLoaded', function() {
  window.console && console.log('Hello Vanilla JS..');
  
  var rockBtn = document.getElementById('rock');
  var paperBtn = document.getElementById('paper');
  var scissorsBtn = document.getElementById('scissors');
  
  if (rockBtn) {
    rockBtn.addEventListener('click', function(event) { 
      event.preventDefault();
      play(0); 
    });
  }
  if (paperBtn) {
    paperBtn.addEventListener('click', function(event) { 
      event.preventDefault();
      play(1); 
    });
  }
  if (scissorsBtn) {
    scissorsBtn.addEventListener('click', function(event) { 
      event.preventDefault();
      play(2); 
    });
  }
  
  // Bootstrap dropdown menu handler
  var dropdownToggle = document.querySelector('.dropdown-toggle');
  if (dropdownToggle) {
    dropdownToggle.addEventListener('click', function(e) {
      e.preventDefault();
      var dropdown = this.parentElement;
      var menu = dropdown.querySelector('.dropdown-menu');
      if (menu) {
        var isOpen = dropdown.classList.contains('open');
        // Close all dropdowns
        document.querySelectorAll('.dropdown').forEach(function(d) {
          d.classList.remove('open');
        });
        // Toggle this one
        if (!isOpen) {
          dropdown.classList.add('open');
        }
      }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      var clickedDropdown = false;
      var el = e.target;
      while (el && el !== document.body) {
        if (el.classList && el.classList.contains('dropdown')) {
          clickedDropdown = true;
          break;
        }
        el = el.parentElement;
      }
      if (!clickedDropdown) {
        document.querySelectorAll('.dropdown').forEach(function(d) {
          d.classList.remove('open');
        });
      }
    });
  }
  
  // Bootstrap navbar collapse handler
  var navbarToggle = document.querySelector('.navbar-toggle');
  var navbarCollapse = document.querySelector('.navbar-collapse');
  if (navbarToggle && navbarCollapse) {
    navbarToggle.addEventListener('click', function() {
      navbarCollapse.classList.toggle('collapse');
      navbarCollapse.classList.toggle('in');
    });
  }
  
  // Run for the first time
  leaders();
});

function play(strategy) {
	var successEl = document.getElementById('success');
	var errorEl = document.getElementById('error');
	var statusTextEl = document.getElementById('statustext');
	var statusEl = document.getElementById('status');
	var formInputs = document.querySelectorAll('#rpsform input');
	
	if (successEl) successEl.innerHTML = "";
	if (errorEl) errorEl.innerHTML = "";
	if (statusTextEl) statusTextEl.innerHTML = "Playing...";
	formInputs.forEach(function(input) {
		input.disabled = true;
	});
	if (statusEl) statusEl.style.display = 'block';
	
	window.console && console.log('Played '+strategy);
	
	fetch('play.php?play='+strategy)
		.then(function(response) {
			return response.json();
		})
		.then(function(data) {
			window.console && console.log(data);
			if ( data.guid ) {
				if (statusTextEl) statusTextEl.innerHTML = "Waiting for opponent...";
				check(data.guid); // Start the checking process
			} else {
				if (statusEl) statusEl.style.display = 'none';
				if (successEl) {
					if ( data.tie ) {
						successEl.innerHTML = "You tied "+data.displayname;
					} else if ( data.win ) {
						successEl.innerHTML = "You beat "+data.displayname;
					} else { 
						successEl.innerHTML = "You lost to "+data.displayname;
					}
				}
				formInputs.forEach(function(input) {
					input.disabled = false;
				});
				leaders();  // Immediately update the leaderboard
			}
		})
		.catch(function(error) {
			window.console && console.error('Error:', error);
			if (errorEl) errorEl.innerHTML = "Error playing game";
			if (statusEl) statusEl.style.display = 'none';
			formInputs.forEach(function(input) {
				input.disabled = false;
			});
		});
	
	return false;
}

var GLOBAL_GUID;
function check(guid) {
	GLOBAL_GUID = guid;
	window.console && console.log('Checking game '+guid);
	
	fetch('play.php?game='+guid)
		.then(function(response) {
			return response.json();
		})
		.then(function(data) {
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
			var statusEl = document.getElementById('status');
			var successEl = document.getElementById('success');
			var formInputs = document.querySelectorAll('#rpsform input');
			
			if (statusEl) statusEl.style.display = 'none';
			if (successEl) {
				if ( data.tie ) {
					successEl.innerHTML = "You tied "+data.displayname;
				} else if ( data.win ) {
					successEl.innerHTML = "You beat "+data.displayname;
				} else { 
					successEl.innerHTML = "You lost to "+data.displayname;
				}
			}
			formInputs.forEach(function(input) {
				input.disabled = false;
			});
			leaders();  // Immediately update the leaderboard
		})
		.catch(function(error) {
			window.console && console.error('Error:', error);
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
	
	fetch('stats.php')
		.then(function(response) {
			return response.json();
		})
		.then(function(data) {
			window.console && console.log(data);
			var leadersEl = document.getElementById('leaders');
			if (leadersEl) {
				leadersEl.innerHTML = "<ol>\n";
				for (var i = 0; i < data.length; i++) {
					var entry = data[i];
					var li = document.createElement('li');
					li.textContent = entry.name + ' - Wins: ' + entry.wins + ', Losses: ' + entry.losses + ' (Net: ' + entry.score + ')';
					leadersEl.querySelector('ol').appendChild(li);
					console.log(data[i]);
				}
			}
			OLD_TIMEOUT = setTimeout(leaders, 20000);
		})
		.catch(function(error) {
			window.console && console.error('Error:', error);
			OLD_TIMEOUT = setTimeout(leaders, 20000);
		});
}
</script>
<?php
footerEnd();
