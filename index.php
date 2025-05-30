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

$all_langs = ['C', 'C++', 'Java', 'Python', 'JavaScript', 'PHP'];

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $messages = [];

    if (!empty($_COOKIE['save']))
    {
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('pass', '', 100000);
        $messages[] = 'Спасибо, результаты сохранены.';

        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass']))
        {
            $messages[] = sprintf(
                'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong>.',
                htmlspecialchars($_COOKIE['login']),
                htmlspecialchars($_COOKIE['pass'])
            );
        }
    }

    $fields = ['fio', 'email', 'dob', 'phone', 'bio'];
    $errors = [];
    $values = [];

    foreach ($fields as $f)
    {
        $errors[$f] = !empty($_COOKIE[$f . '_error']);
        $values[$f] = empty($_COOKIE[$f . '_value']) ? '' : htmlspecialchars($_COOKIE[$f . '_value']);
        setcookie($f . '_error', '', 100000);
    }

    $values['lang'] = [];
    if (!empty($_COOKIE['lang_value']))
        $values['lang'] = explode(',', $_COOKIE['lang_value']);

    $errors['lang'] = !empty($_COOKIE['lang_error']);
    setcookie('lang_error', '', 100000);

    if (!empty($_SESSION['login']))
    {
        try
        {
            $stmt = $db->prepare("SELECT * FROM application WHERE login = ?");
            $stmt->execute([$_SESSION['login']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user)
            {
                foreach ($fields as $f)
                    $values[$f] = htmlspecialchars($user[$f]);

                $values['lang'] = explode(',', $user['lang']);
            }
        }
        catch (PDOException $e)
        {
            $messages[] = 'Ошибка при загрузке данных.';
        }
    }

    include 'header.php';
    if (!empty($messages))
    {
        echo '<div id="messages">';
        foreach ($messages as $msg)
            echo "<p>$msg</p>";
        echo '</div>';
    }
    include 'form.php';
    ?>

    <h2>Галерея</h2>
    <div class="gallery">
        <img src="media/img1.jpg" alt="Фото 1">
        <img src="media/img2.jpg" alt="Фото 2">
        <img src="media/img3.jpg" alt="Фото 3">
        <img src="media/img4.jpg" alt="Фото 4">
        <img src="media/img5.jpg" alt="Фото 5">
    </div>

    <?php
    include 'footer.php';
    exit();
}

// POST-запрос — валидация
$fields = ['fio', 'email', 'dob', 'phone', 'bio'];
$errors = false;

foreach ($fields as $f)
{
    if (empty($_POST[$f]) || !preg_match('/^[^<>{}]+$/u', $_POST[$f]))
    {
        setcookie($f . '_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    else
    {
        setcookie($f . '_value', $_POST[$f], time() + 30 * 24 * 60 * 60);
    }
}

if (empty($_POST['lang']) || !is_array($_POST['lang']))
{
    setcookie('lang_error', '1', time() + 24 * 60 * 60);
    $errors = true;
}
else
{
    $clean_langs = array_intersect($_POST['lang'], $all_langs);
    setcookie('lang_value', implode(',', $clean_langs), time() + 30 * 24 * 60 * 60);
}

if ($errors)
{
    header('Location: index.php');
    exit();
}
else
{
    foreach ($fields as $f)
        setcookie($f . '_error', '', 100000);

    setcookie('lang_error', '', 100000);
}

$lang_str = implode(',', $clean_langs);

if (!empty($_SESSION['login']))
{
    try
    {
        $stmt = $db->prepare("UPDATE application SET fio=?, email=?, dob=?, phone=?, bio=?, lang=? WHERE login=?");
        $stmt->execute([
            $_POST['fio'],
            $_POST['email'],
            $_POST['dob'],
            $_POST['phone'],
            $_POST['bio'],
            $lang_str,
            $_SESSION['login']
        ]);
    }
    catch (PDOException $e)
    {
        print('Ошибка при обновлении: ' . $e->getMessage());
        exit();
    }
}
else
{
    $login = 'u' . substr(uniqid(), -5);
    $pass = substr(md5(rand()), 0, 8);
    $pass_hash = md5($pass);

    setcookie('login', $login);
    setcookie('pass', $pass);

    try
    {
        $stmt = $db->prepare("INSERT INTO application (fio, email, dob, phone, bio, lang, login, pass_hash)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['fio'],
            $_POST['email'],
            $_POST['dob'],
            $_POST['phone'],
            $_POST['bio'],
            $lang_str,
            $login,
            $pass_hash
        ]);
    }
    catch (PDOException $e)
    {
        print('Ошибка при сохранении: ' . $e->getMessage());
        exit();
    }
}

setcookie('save', '1');
header('Location: index.php');
