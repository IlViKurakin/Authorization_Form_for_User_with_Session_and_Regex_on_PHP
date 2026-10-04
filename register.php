<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require 'db.php';

$msg = '';
$msg_type = ''; // 'success' или 'error'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $pass    = $_POST['password'];

    // Экранируем для SQL
    $name    = mysqli_real_escape_string($conn, $name);
    $surname = mysqli_real_escape_string($conn, $surname);
    $email   = mysqli_real_escape_string($conn, $email);
    $phone   = mysqli_real_escape_string($conn, $phone);

    // Регулярные выражения
    $re_name    = '/^[А-ЯЁ][а-яё]+$/u';
    $re_surname = '/^[А-ЯЁ][а-яё]+$/u';
    $re_email   = '/^[a-zA-Z0-9._%+-]+@(mail\.ru|yandex\.ru)$/';
    $re_phone   = '/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/';
    $re_pass    = '/^(?=.*[A-Z])(?=.*\d)(?=.*-)[A-Za-z0-9-]{8}$/';

    // Проверки
    if (!preg_match($re_name, $name)) {
        $msg = 'Имя должно начинаться с заглавной буквы и содержать только русские буквы (например: Иван).';
    }
    elseif (!preg_match($re_surname, $surname)) {
        $msg = 'Фамилия должна начинаться с заглавной буквы и содержать только русские буквы (например: Иванов).';
    }
    elseif (!preg_match($re_email, $email)) {
        $msg = 'Email должен быть только в доменах @mail.ru или @yandex.ru (например: ivan@mail.ru).';
    }
    elseif (!preg_match($re_phone, $phone)) {
        $msg = 'Телефон должен быть строго в формате: +7 (999) 123-45-67 (с пробелами).';
    }
    elseif (!preg_match($re_pass, $pass)) {
        $msg = 'Пароль должен содержать ровно 8 символов: латиницу, хотя бы одну ЗАГЛАВНУЮ букву, цифру и символ "-". Пример: Abc-1234';
    }
    else {
        // Проверка на дубликат email
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $msg = 'Пользователь с таким email уже зарегистрирован.';
        }
        else {
            // Проверка на дубликат телефона
            $check_phone = mysqli_query($conn, "SELECT id FROM users WHERE phone='$phone'");
            if (mysqli_num_rows($check_phone) > 0) {
                $msg = 'Пользователь с таким телефоном уже зарегистрирован.';
            }
            else {
                $hash = md5($pass);
                $sql = "INSERT INTO users (name, surname, email, phone, password) 
                        VALUES ('$name', '$surname', '$email', '$phone', '$hash')";
                
                if (mysqli_query($conn, $sql)) {
                    $msg = '✅ Регистрация прошла успешно! Теперь вы можете <a href="login.php">войти на сайт</a>.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Ошибка базы данных: ' . mysqli_error($conn);
                }
            }
        }
    }

    // Если есть ошибка — помечаем тип
    if ($msg !== '' && $msg_type !== 'success') {
        $msg_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Регистрация</title>
</head>
<body>

<h2>Регистрация</h2>

<?php if ($msg !== ''): ?>
    <?php if ($msg_type == 'success'): ?>
        <p style="color: green; font-weight: bold; border: 1px solid green; padding: 10px; background: #e6ffe6;">
            <?= $msg ?>
        </p>
    <?php else: ?>
        <p style="color: red; font-weight: bold; border: 1px solid red; padding: 10px; background: #ffe6e6;">
            ❌ Ошибка регистрации: <?= $msg ?>
        </p>
    <?php endif; ?>
<?php endif; ?>

<form method="POST">
    <p><b>Имя</b> (с большой буквы, только русские):<br>
    <input type="text" name="name" placeholder="Иван" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"></p>

    <p><b>Фамилия</b> (с большой буквы, только русские):<br>
    <input type="text" name="surname" placeholder="Иванов" required value="<?= isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : '' ?>"></p>

    <p><b>Email</b> (только @mail.ru или @yandex.ru):<br>
    <input type="text" name="email" placeholder="ivan@mail.ru" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"></p>

    <p><b>Телефон</b> (строго с пробелами):<br>
    <input type="text" name="phone" placeholder="+7 (999) 123-45-67" required value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"></p>

    <p><b>Пароль</b> (8 символов: латиница, ЗАГЛАВНАЯ, цифра, дефис):<br>
    <input type="password" name="password" placeholder="Abc-1234" required></p>

    <p><input type="submit" value="Зарегистрироваться"></p>
</form>

<p>Уже есть аккаунт? <a href="login.php">Войти</a></p>

</body>
</html>