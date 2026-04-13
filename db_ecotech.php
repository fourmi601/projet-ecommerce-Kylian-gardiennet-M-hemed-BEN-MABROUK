<?php
$host_bank     = "100.65.154.19";
$user_bank     = "dev_remote";
$pass_bank     = "ezechiel";
$dbname_bank   = "ecotech_db";
$charset_bank  = "utf8mb4";

try {
    $dsn_bank = "mysql:host=$host_bank;dbname=$dbname_bank;charset=$charset_bank";
    $pdo_bank = new PDO($dsn_bank, $user_bank, $pass_bank, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à Ecotech Bank : " . $e->getMessage());
}