<?php
// BDD locale XAMPP — changer creds en prod !
$host     = 'localhost';
$dbname   = 'projet_dev';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // On n'affiche pas le message d'erreur complet aux visiteurs
    die("Erreur de connexion à la base de données. Contactez l'administrateur.");
}
?>