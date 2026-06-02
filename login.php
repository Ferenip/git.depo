<?php
session_start();
include 'baglan.php';

// Eğer zaten giriş yapılmışsa direkt admin paneline yönlendir
if (isset($_SESSION['admin_oturum'])) {
    header("Location: admin.php");
    exit;
}

$hata = "";

// Form gönderildiğinde çalışacak kod
if (isset($_POST['giris_yap'])) {
    $kullanici = trim($_POST['kullanici_adi']);
    $sifre = trim($_POST['sifre']);

    // Veritabanında bu kullanıcıyı arıyoruz
    $sorgu = $db->prepare("SELECT * FROM yoneticiler WHERE kullanici_adi = ?");
    $sorgu->execute([$kullanici]);
    $yonetici = $sorgu->fetch(PDO::FETCH_ASSOC);


    // Kodun içinde hiçbir şifre yazmıyor! Sadece girilen şifre ile DB'deki hash karşılaştırılıyor.
    if ($yonetici && password_verify($sifre, $yonetici['sifre'])) {
        // Oturumu başlat ve yönlendir
        $_SESSION['admin_oturum'] = true;
        $_SESSION['admin_isim'] = $yonetici['kullanici_adi'];
        header("Location: admin.php");
        exit;
    } else {
        $hata = "Hatalı kullanıcı adı veya şifre girdiniz!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Yönetici Girişi - Colombia Coffee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>">
</head>

<body>

    <div class="login-kutu">
        <h2>Yönetici Girişi</h2>

        <?php if ($hata != "") {
            echo "<div class='hata-mesaji'>$hata</div>";
        } ?>

        <form method="POST">
            <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required>
            <input type="password" name="sifre" placeholder="Şifre" required>
            <button type="submit" name="giris_yap">Giriş Yap</button>
        </form>
    </div>

</body>

</html>