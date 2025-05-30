<?php
header('Content-Type: text/html; charset=UTF-8');

// HTTP-аутентификация
if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== 'admin' ||
    md5($_SERVER['PHP_AUTH_PW']) !== md5('123'))
{
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Zone"');
    echo '<h1>401 Требуется авторизация</h1>';
    exit();
}

// Подключение к БД
try
{
    $db = new PDO('mysql:host=localhost;dbname=u68654', 'u68654', '1979564');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    exit('Ошибка подключения к БД: ' . $e->getMessage());
}

// Удаление записи
if (!empty($_GET['delete_id']))
{
    $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header('Location: admin.php');
    exit();
}

// Обновление записи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['edit_id']))
{
    $stmt = $db->prepare("UPDATE application SET fio=?, email=?, dob=?, phone=?, bio=?, lang=? WHERE id=?");
    $stmt->execute([
        $_POST['fio'],
        $_POST['email'],
        $_POST['dob'],
        $_POST['phone'],
        $_POST['bio'],
        $_POST['lang'],
        $_POST['edit_id']
    ]);
    header('Location: admin.php');
    exit();
}

// Загрузка всех записей
$stmt = $db->query("SELECT * FROM application ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Подсчёт статистики по языкам
$lang_stats = [];
foreach ($data as $row)
{
    $langs = array_map('trim', explode(',', $row['lang']));
    foreach ($langs as $l)
    {
        if ($l !== '')
            $lang_stats[$l] = ($lang_stats[$l] ?? 0) + 1;
    }
}

include 'header.php';
?>

<h2>Статистика по языкам программирования</h2>
<ul>
<?php foreach ($lang_stats as $lang => $count): ?>
    <li><strong><?= htmlspecialchars($lang) ?></strong>: <?= $count ?> чел.</li>
<?php endforeach; ?>
</ul>

<h2>Все отправленные данные</h2>
<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; background: #fff;">
    <thead>
        <tr style="background: #eee;">
            <th>ID</th>
            <th>ФИО</th>
            <th>Email</th>
            <th>Дата рождения</th>
            <th>Телефон</th>
            <th>Сообщение</th>
            <th>Языки</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row): ?>
        <tr>
            <form method="post">
                <td><?= $row['id'] ?></td>
                <td><input name="fio" value="<?= htmlspecialchars($row['fio']) ?>" /></td>
                <td><input name="email" value="<?= htmlspecialchars($row['email']) ?>" /></td>
                <td><input type="date" name="dob" value="<?= $row['dob'] ?>" /></td>
                <td><input name="phone" value="<?= htmlspecialchars($row['phone']) ?>" /></td>
                <td><input name="bio" value="<?= htmlspecialchars($row['bio']) ?>" /></td>
                <td><input name="lang" value="<?= htmlspecialchars($row['lang']) ?>" /></td>
                <td>
                    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>" />
                    <input type="submit" value="Сохранить" />
                    <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Удалить запись?')">Удалить</a>
                </td>
            </form>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>
