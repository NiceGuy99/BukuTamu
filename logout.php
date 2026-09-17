<?php
/**
 * Handler Logout Petugas
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/helpers/functions.php';

logoutUser();
setFlash('info', 'Anda telah berhasil logout dari sistem.');
header('Location: ' . BASE_URL . 'login.php');
exit;
