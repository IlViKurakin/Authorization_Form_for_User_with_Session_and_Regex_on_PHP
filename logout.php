<?php
session_start();
session_destroy();

setcookie('user_phone', '', time() - 1, '/');
setcookie('user_hash',  '', time() - 1, '/');

header('Location: login.php');
exit;
?>