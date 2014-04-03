<?php
session_start();
$_SESSION['error'] = "The About page is under construction.";
header("Location: index.php");
