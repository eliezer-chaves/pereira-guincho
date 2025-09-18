<?php
if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
    echo "Capturando rota API: " . $_SERVER['REQUEST_URI'];
    include '../click-tracker/public/index.php';
    exit;
}
?>