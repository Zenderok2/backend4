<?php
$all_langs = ['C', 'C++', 'Java', 'Python', 'JavaScript', 'PHP'];
?>

<form id="mainForm" action="" method="POST" class="form-container">
  <label>ФИО:</label><br />
  <input name="fio" class="<?= !empty($errors['fio']) ? 'error' : '' ?>"
         value="<?= htmlspecialchars($values['fio'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Email:</label><br />
  <input name="email" class="<?= !empty($errors['email']) ? 'error' : '' ?>"
         value="<?= htmlspecialchars($values['email'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Дата рождения:</label><br />
  <input type="date" name="dob" class="<?= !empty($errors['dob']) ? 'error' : '' ?>"
         value="<?= htmlspecialchars($values['dob'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Телефон:</label><br />
  <input name="phone" class="<?= !empty($errors['phone']) ? 'error' : '' ?>"
         value="<?= htmlspecialchars($values['phone'] ?? '', ENT_QUOTES); ?>" /><br /><br />

  <label>Сообщение:</label><br />
  <textarea name="bio" class="<?= !empty($errors['bio']) ? 'error' : '' ?>"><?= htmlspecialchars($values['bio'] ?? '', ENT_QUOTES); ?></textarea><br /><br />

  <label>Любимые языки программирования:</label><br />
  <?php foreach ($all_langs as $lang): ?>
    <label>
      <input type="checkbox" name="lang[]" value="<?= $lang ?>"
        <?= !empty($values['lang']) && in_array($lang, $values['lang']) ? 'checked' : '' ?>>
      <?= $lang ?>
    </label><br />
  <?php endforeach; ?>
  <?php if (!empty($errors['lang'])): ?>
    <p class="error-text">Выберите хотя бы один язык.</p>
  <?php endif; ?>

  <br />
  <input type="submit" value="Сохранить" />
</form>

<?php if (!empty($_SESSION['login'])): ?>
  <p>Вы вошли как <strong><?= $_SESSION['login'] ?></strong>.</p>
  <form method="post" action="logout.php">
    <input type="submit" value="Выйти" />
  </form>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("mainForm");
  if (!window.fetch || !form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const data = {};

    formData.forEach((value, key) => {
      const fixedKey = key === "lang[]" ? "lang" : key;
      if (fixedKey === "lang") {
        if (!data.lang) data.lang = [];
        data.lang.push(value);
      } else {
        data[fixedKey] = value;
      }
    });

    console.log("Отправляем JSON:", JSON.stringify(data));

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
