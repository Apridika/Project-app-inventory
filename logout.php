<?php

require_once 'includes/auth_check.php';

$_SESSION = [];
session_unset();
session_destroy();

redirectTo('login.php');