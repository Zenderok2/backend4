<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();

try {
    $db = new PDO('mysql:host=localhost;dbname=u68654', 'u68654', '1979564');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка подключения к базе данных']);
    exit();
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректный JSON']);
    exit();
}

// Проверка обязательных полей
$fields = ['fio', 'email', 'dob', 'phone', 'bio'];
foreach ($fields as $f) {
    if (empty($data[$f]) || !preg_match('/^[^<>{}]+$/u', $data[$f])) {
        http_response_code(400);
        echo json_encode(['error' => "Некорректное значение поля: $f"]);
        exit();
    }
}

// Проверка языков программирования
$all_langs = ['C', 'C++', 'Java', 'Python', 'JavaScript', 'PHP'];
$langs = $data['lang'] ?? [];

if (!is_array($langs)) {
    http_response_code(400);
    echo json_encode(['error' => 'Поле lang должно быть массивом']);
    exit();
}

$clean_langs = array_intersect($langs, $all_langs);
if (empty($clean_langs)) {
    http_response_code(400);
    echo json_encode(['error' => 'Не выбраны допустимые языки']);
    exit();
}

$lang_str = implode(',', $clean_langs);

// Если пользователь авторизован — обновляем
if (!empty($_SESSION['login'])) {
    try {
        $stmt = $db->prepare("UPDATE application SET fio=?, email=?, dob=?, phone=?, bio=?, lang=? WHERE login=?");
        $stmt->execute([
            $data['fio'],
            $data['email'],
            $data['dob'],
            $data['phone'],
            $data['bio'],
            $lang_str,
            $_SESSION['login']
        ]);
        echo json_encode(['status' => 'updated']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка при обновлении']);
    }
    exit();
}

// Иначе — регистрация нового пользователя
$login = 'u' . substr(uniqid(), -5);
$pass = substr(md5(rand()), 0, 8);
$pass_hash = md5($pass);

try {
    $stmt = $db->prepare("INSERT INTO application (fio, email, dob, phone, bio, lang, login, pass_hash)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['fio'],
        $data['email'],
        $data['dob'],
        $data['phone'],
        $data['bio'],
        $lang_str,
        $login,
        $pass_hash
    ]);
    echo json_encode([
        'login' => $login,
        'pass' => $pass,
        'profile_url' => 'index.php'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при создании пользователя']);
}
