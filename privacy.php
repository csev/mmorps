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

<h1>Privacy Policy</h1>

<p>This privacy policy has been compiled to better serve those who are concerned with how their 'Personally Identifiable Information' (PII) is being used online. PII, as described in US privacy law and information security, is information that can be used on its own or with other information to identify, contact, or locate a single person, or to identify an individual in context. Please read our privacy policy carefully to get a clear understanding of how we collect, use, protect or otherwise handle your Personally Identifiable Information in accordance with our website.</p>

<h2>What personal information do we collect?</h2>
<p>When you register on our site using OAuth authentication (Google, GitHub, or Patreon), we collect:</p>
<ul>
  <li>Your name and display name</li>
  <li>Your email address</li>
  <li>Your OAuth provider identifier</li>
  <li>Game statistics (wins, losses, games played)</li>
</ul>

<p><strong>OAuth Authentication:</strong> When you log in using Google OAuth, Google shares your basic profile information (name, email address) with us. We use this information solely to create and maintain your account on this service. We do not share this information with third parties except as necessary to provide the service.</p>

<h2>When do we collect information?</h2>
<p>We collect information from you when you register on our site using OAuth authentication or when you play games on our platform.</p>

<h2>How do we use your information?</h2>
<p>We use the information we collect from you to:</p>
<ul>
  <li>Create and maintain your user account</li>
  <li>Display your game statistics and leaderboard rankings</li>
  <li>Provide customer support</li>
  <li>Improve our service</li>
</ul>

<h2>How do we protect your information?</h2>
<p>Your personal information is contained behind secured networks and is only accessible by a limited number of persons who have special access rights to such systems, and are required to keep the information confidential. In addition, all sensitive information you supply is encrypted via Secure Socket Layer (SSL) technology.</p>

<h2>Do we use 'cookies'?</h2>
<p>Yes. Cookies are small files that a site or its service provider transfers to your computer's hard drive through your Web browser (if you allow) that enables the site's or service provider's systems to recognize your browser and capture and remember certain information.</p>

<p><strong>We use cookies to:</strong></p>
<ul>
  <li>Understand and save user's preferences and identity for future visits</li>
  <li>Maintain your login session</li>
</ul>

<p><strong>If users disable cookies in their browser:</strong></p>
<p>If you turn cookies off, you won't be able to log in to the site directly.</p>

<h2>Third-party disclosure</h2>
<p>We do not sell, trade, or otherwise transfer to outside parties your Personally Identifiable Information.</p>

<h2>Third-party services</h2>
<p>We use the following third-party services:</p>
<ul>
  <li><strong>OAuth Providers (Google, GitHub, Patreon):</strong> We use OAuth for authentication. These providers handle your login credentials. Please review their privacy policies for information on how they handle your data.</li>
<?php if ( isset($CFG->analytics_key) && $CFG->analytics_key ) { ?>
  <li><strong>Google Analytics:</strong> We use Google Analytics to track usage and improve the site unless you request not to be tracked using a Do Not Track signal. Users may opt-out of Google Analytics using Google-provided opt-out capabilities.</li>
<?php } ?>
</ul>

<h2>How does our site handle Do Not Track signals?</h2>
<p>We honor Do Not Track signals. When DNT is indicated, we suppress the gathering of analytics data.</p>

<h2>COPPA (Children Online Privacy Protection Act)</h2>
<p>We do not specifically market to children under the age of 13 years old.</p>

<h2>Data Retention</h2>
<p>We actively expire inactive data based on a schedule. When data expires or is manually removed, this service does not retain a copy of the data. By only keeping active data in the system, we reduce the overall amount of 'Personally Identifiable Information' (PII) on the system at any time.</p>

<h2>Data Analysis</h2>
<p>We will limit our analysis of PII data to reports requested by the customer or reports that are necessary for accounting, technical support, and overall statistics for the system. We may do internal data analysis to improve the technical reliability, functionality, or performance of the system.</p>

<h2>General Philosophy</h2>
<p>It is often said that if you are not paying for a service, then you are the product. For this service, you are not the product and your data/activity is not the product. We retain only as much of your data as is needed to accomplish your gaming goals and for no other purpose. Wherever possible, we give you control over the retention of your data and respect your wishes with respect to the retention of your data.</p>

<p>Our goal is to comply both with the letter and spirit of privacy laws like the Family Educational Rights and Privacy Act (FERPA) and EU General Data Protection Regulation (GDPR) regulations. Our goal is to have a commitment to user privacy that exceeds the minimum requirements of these laws.</p>

<h2>Notifications</h2>
<p>In order to be in line with Fair Information Practices, we will take the following responsive action should a data breach occur: We will notify you via email.</p>

<h2>Contact Information</h2>
<p>If there are any questions regarding this privacy policy, you may contact us using the information below:</p>
<pre><code>C Programming for Everybody (cc4e.com)
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
