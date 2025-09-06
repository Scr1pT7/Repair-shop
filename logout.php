<?php
require __DIR__.'/db.php';
unset($_SESSION['admin']);
session_destroy();
header("Location: login.php");
exit;
