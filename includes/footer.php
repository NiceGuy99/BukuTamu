<?php
/**
 * Template Footer Buku Tamu Digital
 */
?>
<footer class="mt-auto py-3 bg-white border-top text-center text-muted small">
    <div class="container">
        <span>&copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong> &bull; <?= APP_ORG ?>. Seluruh hak cipta dilindungi.</span>
    </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- QR Code Library (EasyQRCodeJS / QRCode.js) -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<!-- Custom App JS -->
<script src="<?= BASE_URL ?>assets/js/app.js?v=<?= time() ?>"></script>

<?php if (isset($extraScript)) echo $extraScript; ?>
</body>
</html>
