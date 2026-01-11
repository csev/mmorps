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
    <?php include('nav.php'); ?>

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

<?php if ((isset($CFG->patreon_link) && !empty($CFG->patreon_link)) || (isset($CFG->github_sponsors_link) && !empty($CFG->github_sponsors_link))) { ?>
<h2>Support This Project</h2>
<p>
<?php if (isset($CFG->patreon_link) && !empty($CFG->patreon_link)) { ?>
  <a href="<?php echo htmlspecialchars($CFG->patreon_link); ?>" target="_blank">Support on Patreon</a>
<?php } ?>
<?php if ((isset($CFG->patreon_link) && !empty($CFG->patreon_link)) && (isset($CFG->github_sponsors_link) && !empty($CFG->github_sponsors_link))) { ?>
  | 
<?php } ?>
<?php if (isset($CFG->github_sponsors_link) && !empty($CFG->github_sponsors_link)) { ?>
  <a href="<?php echo htmlspecialchars($CFG->github_sponsors_link); ?>" target="_blank">Sponsor on GitHub</a>
<?php } ?>
</p>
<?php } ?>

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
  
  // Navbar collapse handler is in nav.php and works on all pages
});
</script>
<?php
footerEnd();
?>
