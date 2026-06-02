<?php
session_start();
session_destroy(); // Tüm oturum bilgilerini yok et
header("Location: login.php"); // Giriş ekranına geri yolla
exit;
?>