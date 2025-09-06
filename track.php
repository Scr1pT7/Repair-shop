<?php
require __DIR__.'/db.php';

$token = $_GET['t'] ?? null;
$serial = $_GET['serial'] ?? null;

if (!$token && !$serial) {
    http_response_code(400);
    die("❌ رابط غير صالح: مفقود t أو serial.");
}

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE public_token=? LIMIT 1");
    $stmt->execute([$token]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE serial_number=? LIMIT 1");
    $stmt->execute([$serial]);
}
$device = $stmt->fetch();
if (!$device) {
    http_response_code(404);
    die("❌ لم يتم العثور على الجهاز.");
}

?>
<!DOCTYPE html><html lang="ar"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="style.css">
<title>تتبع الجهاز</title>
<meta name="robots" content="noindex">
<style>
.box{margin-top:6px;border:1px solid #e5e7eb;background:#f9fafb;border-radius:8px;padding:10px;white-space:pre-wrap;word-break:break-word}
</style>
</head><body>
<div class="container">
  <div class="card">
    <h1>تتبع حالة جهازك</h1>
    <p>مرحبًا <b><?= htmlspecialchars($device['customer_name']) ?></b>. تفاصيل جهازك:</p>
    <div class="grid">
      <div class="col-6"><b>النوع:</b> <?= htmlspecialchars($device['device_type']) ?></div>
      <div class="col-6"><b>تسلسلي:</b> <?= htmlspecialchars($device['serial_number']) ?></div>
      <div class="col-6"><b>الهاتف:</b> <?= htmlspecialchars($device['phone']) ?></div>
      <div class="col-6"><b>أضيف في:</b> <?= htmlspecialchars($device['created_at']) ?></div>
      <div class="col-6"><b>الحالة الحالية:</b> <span class="badge"><?= htmlspecialchars($device['status']) ?></span></div>
      <div class="col-6"><b>تاريخ الاستلام:</b> <?= $device['receipt_date'] ? htmlspecialchars($device['receipt_date']) : 'سيتم التحديث عند الجاهزية' ?></div>
      <div class="col-12"><b>المشكلة:</b><div class="box"><?= nl2br(htmlspecialchars($device['problem'] ?? '')) ?></div></div>
    </div>
  </div>

  <div class="card">
    <h2>مشاركة الرابط</h2>
    <?php $link = base_url() . '/track.php?t=' . urlencode($device['public_token']); ?>
    <p>يمكنك نسخ الرابط وإرساله: <br><a href="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars($link) ?></a></p>
  </div>
</div>
</body></html>
