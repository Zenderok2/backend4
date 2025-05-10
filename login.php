<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

// Подключение к БД
try {
    $db = new PDO('mysql:host=localhost;dbname=u68654', 'u68654', '1979564');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    print('Ошибка подключения к БД: ' . $e->getMessage());
    exit();
}

// Если уже вошли — редирект
if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>

    <form action="" method="post">
      <label>Логин:</label><br />
      <input name="login" /><br /><br />
      <label>Пароль:</label><br />
      <input name="pass" type="password" /><br /><br />
      <input type="submit" value="Войти" />
    </form>

    <?php
} else {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (empty($login) || empty($pass)) {
        echo 'Пожалуйста, заполните логин и пароль.';
        exit();
    }

    try {
        $stmt = $db->prepare("SELECT id, pass_hash FROM application WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($pass) === $user['pass_hash']) {
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $user['id'];
            header('Location: index.php');
            exit();
        } else {
            echo 'Неверный логин или пароль.';
        }
    } catch (PDOException $e) {
        print('Ошибка запроса: ' . $e->getMessage());
        exit();
    }
}
