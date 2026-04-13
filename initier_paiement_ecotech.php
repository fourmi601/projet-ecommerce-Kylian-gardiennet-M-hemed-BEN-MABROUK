<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; 
require_once 'marchands-config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['total_a_payer'])) {
    header('Location: panier.php');
    exit();
}

$id_user = $_SESSION['user_id'];
$montant = $_SESSION['total_a_payer'];
$email_client = $_SESSION['email'] ?? ''; 

try {
    $stmt = $pdo->prepare("INSERT INTO commande (id_user, prix_total) VALUES (?, ?)");
    $stmt->execute([$id_user, $montant]);
    $order_id = $pdo->lastInsertId();

    $stmt_user = $pdo->prepare("SELECT email FROM utilisateur WHERE id_user = ?");
    $stmt_user->execute([$id_user]);
    $user_data = $stmt_user->fetch();
    $email_client = $user_data['email'] ?? '';

    $base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $return_url = $base_url . "/confirmation.php?order=" . $order_id;
    $cancel_url = $base_url . "/panier.php";

    $data = json_encode([
        'amount'     => $montant,
        'order_id'   => $order_id,
        'email'      => $email_client,
        'buyer_id'   => $id_user, 
        'token'      => $_SESSION['ecotech_token'] ?? '',
        'return_url' => $return_url,
        'cancel_url' => $cancel_url 
    ]);
    $ch = curl_init(ECOTECH_API_URL_PAYMENT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-KEY: ' . ECOTECH_API_KEY
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $resultat = json_decode($response, true);

        if (isset($resultat['payment_url'])) {
            header('Location: ' . $resultat['payment_url']);
            exit();
        } 
        elseif (isset($resultat['status']) && $resultat['status'] === 'success') {
            header('Location: confirmation.php?order=' . $order_id);
            exit();
        } else {
            echo "<div style='background:#1a1c24; color:white; padding:20px; font-family:monospace;'>";
            echo "<h2 style='color:#ff4757;'>❌ La banque a refusé les données</h2>";
            echo "<strong>Réponse de la banque :</strong><br>";
            echo "<pre style='background:#000; padding:10px; color:#2ecc71;'>" . htmlspecialchars($response) . "</pre>";
            echo "<hr><strong>Ce qu'on a envoyé :</strong><br>";
            echo "<pre style='background:#000; padding:10px; color:#3498db;'>" . $data . "</pre>";
            echo "</div>";
        }
    } else {
        echo "Le serveur bancaire ne répond pas (Code $httpCode). Réponse : " . htmlspecialchars($response);
    }

} catch (Exception $e) {
    echo "Erreur fatale : " . $e->getMessage();
}
?>