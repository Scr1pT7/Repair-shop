<?php
// Rename this file to env.php and edit the values for your server.
return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'repair_shop',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    // IMPORTANT: Put your public base URL here WITHOUT trailing slash.
    // Examples:
    //  - 'http://localhost/repair-shop-fixed' (for local XAMPP)
    //  - 'https://yourdomain.com/repair'
    //  - 'https://xxxx-xx-xx-xx-xx.ngrok-free.app'
    'BASE_URL' => 'http://localhost/repair-shop-fixed'

,
// Shop branding (used by print_qr.php)
'SHOP_NAME' => 'The Pro PC',
'SHOP_PHONE_1' => 'رقم المحل: 0789633888 ',
'SHOP_PHONE_2' => 'رقم الصيانة: 0796352788',
// Relative path from project root. Put your logo here as PNG/SVG/JPG
'SHOP_LOGO' => 'assets/logo.jpeg'

];
