<?php
require __DIR__.'/db.php';

// If first run, ensure at least one admin exists (admin/admin123)
$pdo->exec("CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $pdo->prepare("SELECT COUNT(*) c FROM admins");
$stmt->execute();
if ((int)$stmt->fetch()['c'] === 0) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO admins(username,password) VALUES('admin', ?)")->execute([$hash]);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=? LIMIT 1");
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['admin'] = ['id'=>$row['id'], 'username'=>$row['username']];
        header("Location: admin.php");
        exit;
    } else {
        $error = 'بيانات الدخول غير صحيحة';
    }
}
?>
<!DOCTYPE html><html lang="ar"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="style.css">
<title>تسجيل الدخول</title>
</head><body>
<div class="container">
  <div class="card" style="max-width:420px;margin:60px auto">
    <h1>تسجيل الدخول</h1>
    <?php if($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="grid">
        <div class="col-12"><label>اسم المستخدم</label><input name="username" required></div>
        <div class="col-12"><label>كلمة المرور</label><input type="password" name="password" required></div>
      </div>
      <div class="actions" style="margin-top:10px">
        <button class="btn" type="submit">دخول</button>
      </div>
    </form>
    <footer>افتراضيًا: المستخدم <b>admin</b> وكلمة المرور <b>admin123</b>. غيّرها بعد الدخول.</footer>
  </div>
</div>
</body></html>
