<?php
/**
 * Template Header Buku Tamu Digital
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/functions.php';

$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= APP_ORG ?></title>
    
    <!-- Favicon / Logo Tab Bar -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/img/logo.png">
    
    <!-- Google Fonts (Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= time() ?>">
    
    <script>
        window.APP_BASE_URL = '<?= BASE_URL ?>';
    </script>
    
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>
