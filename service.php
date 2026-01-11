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
  initCommonHandlers();
});
</script>
<?php
footerEnd();
?>
