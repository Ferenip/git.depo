<?php include 'baglan.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colombia Coffee - Menü</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="kahve-sayfası.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="icon.png">
    <link  href="index.php">
    <link  href="menu.php">
    
</head>
<body>
    <header>
        <div class="logo">
            <img src="icon.png" alt="Colombia Coffee Logo"> 
        </div>
        
        <div class="right-menu">
            <nav id="navbar">
                <a href="index.php">Ana Sayfa</a>
                <div class="dropdown">
                    <a href="kurumsal.php" class="dropbtn">Kurumsal <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 3px;"></i></a>
                    <div class="dropdown-content">
                        <a href="kurumsal.php?tab=hakkimizda">Hakkımızda</a>
                        <a href="kurumsal.php?tab=magazalar">Mağazalarımız</a>
                        <a href="https://www.kariyer.net/firma-profil/colombia-coffee-293821-338005" target="_blank">Kariyer</a>
                    </div>
                </div>
                <a href="menu.php">Menü</a>
                <a href="iletisim.php">İletişim</a>
            </nav>
            <div class="lang-search">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b4/Flag_of_Turkey.svg" alt="TR">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
            </div>
        </div>
    </header>