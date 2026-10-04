<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require 'db.php';

$msg = '';
$msg_type = '';

// Сообщение об истечении сессии
if (isset($_GET['expired'])) {
    $msg = 'Время вашей сессии истекло (3 минуты). Пожалуйста, войдите снова.';
    $msg_type = 'warning';
}

// Авто-вход по куке
if (!isset($_SESSION['user']) && isset($_COOKIE['user_phone']) && isset($_COOKIE['user_hash'])) {
    $phone = mysqli_real_escape_string($conn, $_COOKIE['user_phone']);
    $hash  = mysqli_real_escape_string($conn, $_COOKIE['user_hash']);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE phone='$phone' AND password='$hash'");
    if ($u = mysqli_fetch_assoc($res)) {
        $_SESSION['user'] = $u;
        $_SESSION['login_time'] = time();
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone    = trim($_POST['phone']);
    $pass     = $_POST['password'];
    $remember = isset($_POST['remember']);
    $hash     = md5($pass);

    $phone_esc = mysqli_real_escape_string($conn, $phone);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE phone='$phone_esc' AND password='$hash'");

    if ($u = mysqli_fetch_assoc($res)) {
        $_SESSION['user'] = $u;
        $_SESSION['login_time'] = time();

        if ($remember) {
            setcookie('user_phone', $phone, time() + 3600 * 24 * 7, '/');
            setcookie('user_hash',  $hash,  time() + 3600 * 24 * 7, '/');
        }

        header('Location: index.php');
        exit;
    } else {
        $msg = 'Неверный номер телефона или пароль.';
        $msg_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Вход</title>
</head>
<body>

<h2>Вход на сайт</h2>

<?php if ($msg !== ''): ?>
    <?php if ($msg_type == 'warning'): ?>
        <p style="color: orange; font-weight: bold; border: 1px solid orange; padding: 10px; background: #fff8e6;">
            ⚠️ <?= $msg ?>
        </p>
    <?php else: ?>
        <p style="color: red; font-weight: bold; border: 1px solid red; padding: 10px; background: #ffe6e6;">
            ❌ <?= $msg ?>
        </p>
    <?php endif; ?>
<?php endif; ?>

<form method="POST">
    <p><b>Телефон</b> (в формате +7 (XXX) XXX-XX-XX):<br>
    <input type="text" name="phone" placeholder="+7 (999) 123-45-67" required></p>

    <p><b>Пароль</b>:<br>
    <input type="password" name="password" required></p>

    <p><label><input type="checkbox" name="remember"> Запомнить меня (куки на 7 дней)</label></p>

    <p><input type="submit" value="Войти"></p>
</form>

<p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>

</body>
</html>