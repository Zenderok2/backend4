<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

try
{
    $db = new PDO('mysql:host=localhost;dbname=u68654', 'u68654', '1979564');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    print('Ошибка подключения к БД: ' . $e->getMessage());
    exit();
}

if (!empty($_SESSION['login']))
{
    header('Location: index.php');
    exit();
}

include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    ?>

    <h2>Вход в аккаунт</h2>
    <form action="" method="post" class="form-container">
        <label>Логин:</label><br />
        <input name="login" required /><br /><br />

        <label>Пароль:</label><br />
        <input name="pass" type="password" required /><br /><br />

        <input type="submit" value="Войти" />
    </form>

    <?php
}
else
{
    $login = $_POST['login'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (empty($login) || empty($pass))
    {
        echo '<p class="error-text">Пожалуйста, заполните логин и пароль.</p>';
        include 'footer.php';
        exit();
    }

    try
    {
        $stmt = $db->prepare("SELECT id, pass_hash FROM application WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($pass) === $user['pass_hash'])
        {
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $user['id'];
            header('Location: index.php');
            exit();
        }
        else
        {
            echo '<p class="error-text">Неверный логин или пароль.</p>';
        }
    }
    catch (PDOException $e)
    {
        echo '<p class="error-text">Ошибка запроса: ' . $e->getMessage() . '</p>';
    }
}

include 'footer.php';
