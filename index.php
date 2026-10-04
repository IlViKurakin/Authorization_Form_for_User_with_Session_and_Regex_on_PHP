<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Проверка времени сессии (3 минуты = 180 секунд)
if (!isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}

if (time() - $_SESSION['login_time'] > 180) {
    session_destroy();
    setcookie('user_phone', '', time() - 1, '/');
    setcookie('user_hash',  '', time() - 1, '/');
    header('Location: login.php?expired=1');
    exit;
}

$u = $_SESSION['user'];
$remaining = 180 - (time() - $_SESSION['login_time']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Личный кабинет</title>
</head>
<body>

<h2>Добро пожаловать, <?= htmlspecialchars($u['name']) ?> <?= htmlspecialchars($u['surname']) ?>!</h2>

<p><b>Email:</b> <?= htmlspecialchars($u['email']) ?></p>
<p><b>Телефон:</b> <?= htmlspecialchars($u['phone']) ?></p>

<p style="color: blue;">⏱ Осталось времени в сессии: <b><?= $remaining ?></b> сек.</p>

<p><a href="logout.php">Выйти</a></p>

</body>
</html>