<?php
// Navigation bar include file
// Requires: $CFG, $_SESSION variables
?>
    <!-- Static navbar -->
    <div class="navbar navbar-default" role="navigation">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.php"><?php echo($CFG->servicename); ?></a>
      </div>
      <div class="navbar-collapse collapse">
        <!-- Wide screen navigation (dropdown) -->
        <ul class="nav navbar-nav navbar-right nav-wide">
          <li><a href="about.php">About</a></li>
          <?php if ( isset($_SESSION['id']) ) { ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo($_SESSION['displayname']);?><b class="caret"></b></a>
            <ul class="dropdown-menu">
				<?php /* <li><a href="profile.php">Profile</a></li> */ ?>
				<?php /* <li><a href="pair.php">Pair wih a Mobile Device</a></li> */ ?>
				<li><a href="privacy.php">Privacy Policy</a></li>
				<li><a href="service.php">Terms of Service</a></li>
				<li role="separator" class="divider"></li>
				<li><a href="delete.php">Delete All My Data</a></li>
				<li><a href="logout.php">Logout</a></li>
            </ul>
          </li>
          <?php } else { ?>
          <li><a href="login.php">Login</a></li>
          <?php } ?>
        </ul>
        <!-- Narrow screen navigation (flat list) -->
        <ul class="nav navbar-nav navbar-right nav-narrow">
          <li><a href="about.php">About</a></li>
          <?php if ( isset($_SESSION['id']) ) { ?>
				<?php /* <li><a href="profile.php">Profile</a></li> */ ?>
				<?php /* <li><a href="pair.php">Pair wih a Mobile Device</a></li> */ ?>
				<li><a href="privacy.php">Privacy Policy</a></li>
				<li><a href="service.php">Terms of Service</a></li>
				<li role="separator" class="divider"></li>
				<li><a href="delete.php">Delete All My Data</a></li>
				<li><a href="logout.php">Logout</a></li>
          <?php } else { ?>
          <li><a href="login.php">Login</a></li>
          <?php } ?>
        </ul>
      </div><!--/.nav-collapse -->
    </div>
<script type="text/javascript">
// Navbar collapse handler - works on all pages
(function() {
  function initNavbarCollapse() {
    var navbarToggle = document.querySelector('.navbar-toggle');
    var navbarCollapse = document.querySelector('.navbar-collapse');
    if (navbarToggle && navbarCollapse) {
      // Remove any existing listeners by cloning
      var newToggle = navbarToggle.cloneNode(true);
      navbarToggle.parentNode.replaceChild(newToggle, navbarToggle);
      
      newToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var collapse = document.querySelector('.navbar-collapse');
        if (collapse) {
          collapse.classList.toggle('collapse');
          collapse.classList.toggle('in');
        }
      });
    } else {
      // If elements not found, try again after a short delay
      setTimeout(initNavbarCollapse, 100);
    }
  }
  
  // Always wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavbarCollapse);
  } else {
    // Use setTimeout to ensure DOM is fully parsed
    setTimeout(initNavbarCollapse, 0);
  }
})();
</script>