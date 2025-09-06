<?php
require __DIR__.'/db.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$error = '';
$success = '';

// Ensure schema exists
$pdo->exec("CREATE TABLE IF NOT EXISTS devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(190) NOT NULL,
  device_type VARCHAR(190) NOT NULL,
  serial_number VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40) NOT NULL,
  problem TEXT,
  status VARCHAR(60) DEFAULT 'جاري الإصلاح',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  receipt_date DATE NULL,
  public_token VARCHAR(64) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

function random_token() {
    return bin2hex(random_bytes(16));
}

// Add device
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO devices(customer_name, device_type, serial_number, phone, problem, status, receipt_date, public_token) VALUES(?,?,?,?,?,?,?,?)");
    $token = random_token();
    $receipt = !empty($_POST['receipt_date']) ? $_POST['receipt_date'] : null;
    $stmt->execute([
        trim($_POST['customer_name']),
        trim($_POST['device_type']),
        trim($_POST['serial_number']),
        trim($_POST['phone']),
        trim($_POST['problem']),
        trim($_POST['status'] ?? 'جاري الإصلاح'),
        $receipt,
        $token
    ]);
    header("Location: admin.php?ok=1"); exit;
}

// Update status/receipt date
if (isset($_POST['update'])) {
    $stmt = $pdo->prepare("UPDATE devices SET status=?, receipt_date=? WHERE id=?");
    $receipt = !empty($_POST['receipt_date']) ? $_POST['receipt_date'] : null;
    $stmt->execute([$_POST['status'], $receipt, (int)$_POST['id']]);
    header("Location: admin.php?updated=1"); exit;
}

// Delete device (optional)
if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM devices WHERE id=?");
    $stmt->execute([(int)$_POST['id']]);
    header("Location: admin.php?deleted=1"); exit;
}

$rows = $pdo->query("SELECT * FROM devices ORDER BY id DESC")->fetchAll();
$statuses = ['جاري الفحص', 'قيد الإصلاح', 'بانتظار قطع', 'جهز للاستلام', 'تم التسليم', 'مرفوض'];
$trackBase = base_url() . '/track.php?t=';
?>
<!DOCTYPE html><html lang="ar"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="style.css">
<title>لوحة التحكم</title>
</head><body>
<div class="container">
  <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if (isset($_GET['ok'])): ?><div class="alert">تمت الإضافة بنجاح ✅</div><?php endif; ?>
  <?php if (isset($_GET['updated'])): ?><div class="alert">تم التحديث ✅</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert">تم الحذف ✅</div><?php endif; ?>
  <div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
      <h1>لوحة التحكم</h1>
      <div class="actions">
        <a class="btn alt" href="logout.php">تسجيل خروج</a>
      </div>
    </div>
    <form method="post" style="margin-top:12px">
      <h2>إضافة جهاز</h2>
      <div class="grid">
        <div class="col-4"><label>اسم الزبون</label><input name="customer_name" required></div>
        <div class="col-4"><label>نوع الجهاز</label><input name="device_type" required></div>
        <div class="col-4"><label>الرقم التسلسلي</label><input name="serial_number" required></div>
        <div class="col-4"><label>الهاتف</label><input name="phone" required></div>
        <div class="col-12"><label>المشكلة</label><textarea name="problem" placeholder="اكتب وصف المشكلة بالتفصيل"></textarea></div>
        <div class="col-4"><label>الحالة</label>
          <select name="status">
            <?php foreach ($statuses as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-4"><label>تاريخ الاستلام (اختياري)</label><input type="date" name="receipt_date"></div>
        <div class="col-12 actions"><button class="btn" type="submit" name="add">إضافة</button></div>
      </div>
    </form>
  </div>

  <div class="card">
    <h2>الأجهزة</h2>
    <div style="overflow:auto">
    <table>
      <thead><tr>
        <th>#</th><th>الزبون</th><th>الجهاز</th><th>تسلسلي</th><th>الهاتف</th><th>الحالة</th><th>تاريخ الاستلام</th><th>QR</th><th>تحكم</th>
      </tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['customer_name']) ?></td>
            <td><?= htmlspecialchars($r['device_type']) ?></td>
            <td><?= htmlspecialchars($r['serial_number']) ?></td>
            <td><?= htmlspecialchars($r['phone']) ?></td>
            <td><span class="badge"><?= htmlspecialchars($r['status']) ?></span></td>
            <td><?= $r['receipt_date'] ? htmlspecialchars($r['receipt_date']) : '—' ?></td>
            <td><a class="btn alt" href="print_qr.php?id=<?= (int)$r['id'] ?>" target="_blank">طباعة QR</a></td>
            <td>
              <form method="post" class="actions" style="margin:0">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <select name="status">
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>" <?= $r['status']===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="date" name="receipt_date" value="<?= htmlspecialchars($r['receipt_date'] ?? '') ?>">
                <button class="btn" name="update">تحديث</button>
                <button class="btn danger" name="delete" onclick="return confirm('حذف هذا الجهاز؟')">حذف</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <footer>🔗 رابط التتبع لكل جهاز يظهر داخل صفحة الطباعة وعلى شكل QR. يمكن إعطاؤه للزبون ليتابع حالة جهازه.</footer>
</div>
</body></html>
