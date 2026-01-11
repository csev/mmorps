<?php
include_once("config.php");
require_once("pdo.php");
require_once("sanity.php");
require_once("lib/util.php");

header('Content-Type: text/html; charset=utf-8');
session_start();

// Require login
if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = "You must be logged in to delete your data.";
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];
$p = $CFG->dbprefix;
$deleted = false;
$error = false;

// Handle form submission
if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    try {
        // Delete all RPS games where user is either player 1 or player 2
        $delete_stmt = $pdo->prepare("DELETE FROM {$p}rps WHERE user1_id = :UID OR user2_id = :UID");
        $delete_stmt->execute(array(':UID' => $user_id));
        
        // Reset wins and losses to 0
        $reset_stmt = $pdo->prepare("UPDATE {$p}user SET wins = 0, losses = 0 WHERE user_id = :UID");
        $reset_stmt->execute(array(':UID' => $user_id));
        
        $deleted = true;
        $_SESSION['success'] = "All your game data has been deleted successfully.";
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        error_log('Delete data error: ' . $e->getMessage());
        $error = "An error occurred while deleting your data. Please try again.";
    }
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    // User cancelled
    header('Location: index.php');
    exit;
}

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

<h1>Delete All My Data</h1>

<?php if ($error) { ?>
<div class="alert alert-danger">
  <p><strong>Error:</strong> <?php echo htmlspecialchars($error); ?></p>
</div>
<?php } ?>

<?php if (!$deleted) { ?>
<div class="alert alert-warning">
  <p><strong>Warning:</strong> This action cannot be undone!</p>
  <p>If you proceed, all of your game data will be permanently deleted, including:</p>
  <ul>
    <li>All your game history</li>
    <li>All your wins and losses</li>
    <li>Your leaderboard position</li>
  </ul>
  <p>Your account will remain active, but all game statistics will be reset to zero.</p>
</div>

<form method="post" action="delete.php">
  <div style="margin: 20px 0;">
    <button type="submit" name="confirm" value="yes" class="btn btn-danger" style="margin-right: 10px;">
      Yes, Delete All My Data
    </button>
    <button type="submit" name="confirm" value="no" class="btn btn-default">
      No, Cancel
    </button>
  </div>
</form>
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
