<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'mysite';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Ошибка подключения к БД: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
?>