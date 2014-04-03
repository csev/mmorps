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
<p>
Hello and welcome to <b><?php echo($CFG->servicename); ?></b>.
Generally this system is used to provide cloud-hosted learning tools that are plugged
into a Learning Management systems like Sakai, Coursera, or Blackboard using 
IMS Learning Tools Interoperability.  You can sign in to this system 
and create a profile and as you use tools from various courses you can 
associate those tools and courses with your profile.
</p>
<p>
Other than logging in and setting up your profile, there is nothing much you can 
do at this screen.  Things happen when your instructor starts using the tools
hosted on this server in their LMS systems.  If you are an instructor and would
like to experiment with these tools (it is early days) send a note to Dr. Chuck.
You can look at the source code for this software at 
<a href="https://github.com/csev/mmorps" target="_blank">https://github.com/csev/mmorps</a>.
</p>
      </div> <!-- /container -->

<?php footerContent(); 
