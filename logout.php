<?php
require_once("config.php");
require_once("lib/util.php");
session_start();
session_unset();
delete_secure_cookie();

header('Location: index.php');
