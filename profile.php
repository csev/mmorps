<?php
session_start();
$_SESSION['error'] = "The Profile page is under construction.";
header("Location: index.php");
