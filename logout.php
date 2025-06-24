<?php
require_once 'includes/functions.php';

session_destroy();
$_SESSION['message'] = displaySuccess('You have been logged out successfully.');
redirect('login.php'); 