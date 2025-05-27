<html>
<head>
  <meta charset="UTF-8">
  <style>
    .error {
      border: 2px solid red;
    }
  </style>
</head>
<body>

<?php
if (!empty($messages)) {
  print('<div id="messages">');
  foreach ($messages as $msg)
    print($msg);
  print('</div>');
}

if (!empty($_SESSION['login'])) {
  echo "<p>Вы вошли как <strong>{$_SESSION['login']}</strong>.</p>";
  echo '<form method="post" action="logout.php">
          <input type="submit" value="Выйти" />
        </form>';
}

$all_langs = ['C', 'C++', 'Java', 'Python', 'JavaScript', 'PHP'];
?>

<form id="mainForm" action="" method="POST">
  <label>ФИО:</label><br />
  <input name="fio" <?php if (!empty($errors['fio'])) print 'class="error"'; ?>
         value="<?= htmlspecialchars($values['fio'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Email:</label><br />
  <input name="email" <?php if (!empty($errors['email'])) print 'class="error"'; ?>
         value="<?= htmlspecialchars($values['email'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Дата рождения:</label><br />
  <input type="date" name="dob" <?php if (!empty($errors['dob'])) print 'class="error"'; ?>
         value="<?= htmlspecialchars($values['dob'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Телефон:</label><br />
  <input name="phone" <?php if (!empty($errors['phone'])) print 'class="error"'; ?>
         value="<?= htmlspecialchars($values['phone'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Сообщение:</label><br />
  <textarea name="bio" <?php if (!empty($errors['bio'])) print 'class="error"'; ?>><?= htmlspecialchars($values['bio'] ?? '', ENT_QUOTES); ?></textarea><br /><br />

  <label>Любимые языки программирования:</label><br />
  <?php foreach ($all_langs as $lang): ?>
    <label>
      <input type="checkbox" name="lang[]" value="<?= $lang ?>"
        <?php if (!empty($values['lang']) && in_array($lang, $values['lang'])) echo 'checked'; ?>>
      <?= $lang ?>
    </label><br />
  <?php endforeach; ?>

  <br />
  <input type="submit" value="Сохранить" />
</form>

<script>
document.addEventListener("DOMContentLoaded", () => {
  if (!window.fetch) return;

  const form = document.getElementById("mainForm");
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const data = {};

    formData.forEach((value, key) => {
      if (key === "lang[]") {
        if (!data.lang) data.lang = [];
        data.lang.push(value);
      } else {
        data[key] = value;
      }
    });

    try {
      const res = await fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      const result = await res.json();

      if (result.login && result.pass) {
        alert(`Пользователь создан:\nЛогин: ${result.login}\nПароль: ${result.pass}`);
        window.location.href = result.profile_url;
      } else if (result.status === "updated") {
        alert("Данные успешно обновлены.");
        window.location.reload();
      } else if (result.error) {
        alert("Ошибка: " + result.error);
      }
    } catch (err) {
      alert("Произошла ошибка: " + err.message);
    }
  });
});
</script>

</body>
</html>
