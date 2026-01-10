<?php
// Navigation bar include file
// Requires: $CFG, $_SESSION variables
?>
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
				<?php /* <li><a href="profile.php">Profile</a></li> */ ?>
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
