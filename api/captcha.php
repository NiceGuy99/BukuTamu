<?php
/**
 * Endpoint API untuk Generate & Refresh Math Captcha (Penjumlahan)
 */

require_once __DIR__ . '/../config/app.php';

$num1 = rand(1, 15);
$num2 = rand(1, 15);
$_SESSION['login_captcha_sum'] = $num1 + $num2;

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'num1' => $num1,
    'num2' => $num2,
    'question' => "{$num1} + {$num2} = ?"
]);
