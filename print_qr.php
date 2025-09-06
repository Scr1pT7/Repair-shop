<?php
require __DIR__.'/db.php';

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$config = require (file_exists(__DIR__.'/env.php') ? __DIR__.'/env.php' : __DIR__.'/env.sample.php');
$shopName   = $config['SHOP_NAME']   ?? 'اسم المحل';
$shopPhone1 = $config['SHOP_PHONE_1'] ?? '';
$shopPhone2 = $config['SHOP_PHONE_2'] ?? '';
$shopLogoRel= $config['SHOP_LOGO']    ?? 'assets/logo.png';
$shopLogoAbs= __DIR__ . '/' . $shopLogoRel;
$logoExists = is_file($shopLogoAbs);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM devices WHERE id=?");
$stmt->execute([$id]);
$device = $stmt->fetch();
if (!$device) { die("❌ الجهاز غير موجود."); }

$trackUrl = base_url() . '/track.php?t=' . urlencode($device['public_token']);

// We use a free QR service (goqr) for simplicity. You can swap later to an internal library.
$qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($trackUrl);
?>
<!DOCTYPE html><html lang="ar"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>طباعة QR</title>
<style>
  body{font-family:system-ui,Segoe UI,Roboto,'Cairo',sans-serif;direction:rtl;padding:20px}
  .ticket{max-width:780px;margin:auto;border:1px dashed #999;padding:16px;border-radius:14px;background:#fff}
  .brand{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:10px;border-bottom:1px dashed #ddd;padding-bottom:10px}
  .brand .left{display:flex;align-items:center;gap:12px}
  .brand .logo{width:80px;height:80px;border-radius:12px;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fafafa}
  .brand .logo img{width:100%;height:100%;object-fit:contain}
  .brand .shopname{font-weight:700;font-size:20px}
  .brand .phones{font-size:14px;color:#333;display:flex;flex-direction:column;gap:2px}
  .hdr{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;margin-top:6px}
  .qr{border:6px solid #111;padding:8px;background:#fff;border-radius:12px}
  .info{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:14px 0}
  .full{grid-column:1 / -1}
  .b{font-weight:700}
  .box{margin-top:6px;border:1px solid #ddd;background:#f9fafb;border-radius:8px;padding:8px;white-space:pre-wrap;word-break:break-word}
  .link{word-break:break-all;background:#f9fafb;border:1px dashed #ddd;padding:8px;border-radius:8px}
  .notice{margin-top:12px;padding:10px;border:1px dashed #aaa;border-radius:10px;background:#fffffb}
  .notice p{margin:4px 0}
  @media print {.no-print{display:none} .ticket{border:0}}
</style>
</head>
<body>
<div class="ticket">
  <div class="brand">
    <div class="left">
      <div class="logo">
        <?php if ($logoExists): ?>
          <img src="<?= htmlspecialchars($shopLogoRel) ?>" alt="Logo">
        <?php else: ?>
          <span style="font-size:12px;color:#777;padding:6px;text-align:center">لا يوجد شعار<br>ضع ملف logo.png</span>
        <?php endif; ?>
      </div>
      <div>
        <div class="shopname"><?= htmlspecialchars($shopName) ?></div>
        <div class="phones">
          <?php if ($shopPhone1): ?><div>📞 <?= htmlspecialchars($shopPhone1) ?></div><?php endif; ?>
          <?php if ($shopPhone2): ?><div>📞 <?= htmlspecialchars($shopPhone2) ?></div><?php endif; ?>
        </div>
      </div>
    </div>
    <div style="text-align:left;opacity:.8">
      <div>تاريخ الطباعة: <?= date('Y-m-d H:i') ?></div>
      <div>رقم البطاقة: #<?= (int)$device['id'] ?></div>
    </div>
  </div>

  <div class="hdr">
    <div>
      <h2 style="margin:0">بطاقة صيانة</h2>
      <div class="info">
        <div><span class="b">الزبون:</span> <?= htmlspecialchars($device['customer_name']) ?></div>
        <div><span class="b">الهاتف:</span> <?= htmlspecialchars($device['phone']) ?></div>
        <div><span class="b">الجهاز:</span> <?= htmlspecialchars($device['device_type']) ?></div>
        <div><span class="b">تسلسلي:</span> <?= htmlspecialchars($device['serial_number']) ?></div>
        <div><span class="b">الحالة:</span> <?= htmlspecialchars($device['status']) ?></div>
        <div><span class="b">التاريخ:</span> <?= htmlspecialchars($device['created_at']) ?></div>
              <div class="full">
          <span class="b">المشكلة:</span>
          <div class="box"><?= nl2br(htmlspecialchars($device['problem'] ?? '')) ?></div>
        </div>
      </div>
      <div class="link">رابط التتبع: <a href="<?= htmlspecialchars($trackUrl) ?>"><?= htmlspecialchars($trackUrl) ?></a></div>
      <p>📱 امسح QR بهاتفك لفتح الرابط مباشرة.</p>
      <div class="notice">
        <p>يرجى ابراز وصل الصيانه عند الاستلام</p>
        <p>المحل غير مسئول عن الجهاز بعد مرور شهر على تسليمه</p>
      </div>
    </div>
    <div class="qr"><img src="<?= htmlspecialchars($qrSrc) ?>" width="220" height="220" alt="QR"></div>
  </div>

  <div class="no-print" style="margin-top:12px">
    <button onclick="window.print()">🖨️ طباعة</button>
  </div>
</div>
</body></html>
