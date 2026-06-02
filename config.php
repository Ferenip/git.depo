<?php
// Bu dosyaya doğrudan erişimi engelle
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('Bu sayfaya doğrudan erişim engellenmiştir.');
}

// Yapay Zeka API Anahtarı (Bu dosyayı .gitignore'a eklemeyi unutmayın!)
define('GROQ_API_KEY', '');//apı key buraya girilecek
?>