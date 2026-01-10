<?php
include_once("config.php");
require_once("sanity.php");
require_once("lib/util.php");

header('Content-Type: text/html; charset=utf-8');
session_start();

headerContent();
?>
</head>
<body>
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
?>

<h1>About MMORPS</h1>

<p>MMORPS (Massively Multiplayer Online Rock Paper Scissors) is an online game platform where players from around the world can compete in classic Rock-Paper-Scissors matches. Challenge your friends, test your strategy, and climb the leaderboard!</p>

<h2>How It Works</h2>
<p>When you play a game, you select Rock, Paper, or Scissors. The system will match you with another player who has also chosen to play. If no opponent is available within 10 seconds, you'll be matched with a random opponent so you can always complete your game.</p>

<p>Your wins and losses are tracked, and you can see how you rank against other players on the leaderboard. The leaderboard is sorted by your net score (wins minus losses), so consistent players rise to the top!</p>

<h2>Features</h2>
<ul>
  <li>Real-time multiplayer gameplay</li>
  <li>Automatic matchmaking with other players</li>
  <li>Win/loss tracking and statistics</li>
  <li>Live leaderboard showing top players</li>
  <li>Simple, fast-paced gameplay</li>
</ul>

<h2>Learn More</h2>
<p>For more online courses and educational resources, visit <a href="https://online.dr-chuck.com" target="_blank">online.dr-chuck.com</a>.</p>

<p><a href="index.php">Back to Game</a></p>

    </div> <!-- /container -->
<?php
footerStart();
?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
<?php
footerEnd();
?>
