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

<h1>Terms of Service</h1>

<p>This is a highly reliable, scalable and secure cloud-based service, but outages may occur. Since there are no fees for this service, there is no formal service level agreement. If you are having issues with the performance or reliability of this site, please contact us.</p>

<p>This site cares greatly about protecting anyone who uses these servers. Please review our <a href="privacy.php">Privacy Policy</a> for more details.</p>

<h2>Account Registration</h2>
<p>To use this service, you must register for an account using OAuth authentication providers such as Google, GitHub, or Patreon. By registering, you agree to provide accurate and complete information. You are responsible for maintaining the security of your account credentials.</p>

<h2>User Conduct</h2>
<p>You agree to use this service only for lawful purposes and in a way that does not infringe the rights of others or restrict their use of the service. You agree not to:</p>
<ul>
  <li>Attempt to gain unauthorized access to the service or other users' accounts</li>
  <li>Interfere with or disrupt the service or servers</li>
  <li>Use automated systems to access the service without permission</li>
  <li>Engage in any activity that violates applicable laws or regulations</li>
</ul>

<h2>Service Availability</h2>
<p>We strive to maintain high availability, but we do not guarantee uninterrupted access to the service. We reserve the right to modify, suspend, or discontinue any part of the service at any time with or without notice.</p>

<h2>Limitation of Liability</h2>
<p>This service is provided "as is" without warranties of any kind. We are not liable for any damages arising from your use of or inability to use the service.</p>

<h2>Notifications</h2>
<p>In order to be in line with Fair Information Practices, we will take the following responsive action should a data breach occur: We will notify you via email.</p>

<h2>Contact Information</h2>
<pre><code>MMO Rock Paper Scissors (mmorps.com)
c/o Learning Experiences (learnxp.com)
2190 Aurelius Rd. Unit 175
Holt MI 48842-9998
United States</code></pre>

<p>Last edited: <?php echo date('d-M-Y'); ?></p>

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
