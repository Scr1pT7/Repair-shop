<?php
require __DIR__.'/db.php';
if (isset($_SESSION['admin'])) { header("Location: admin.php"); }
else { header("Location: login.php"); }
exit;
