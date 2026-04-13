<?php
// On affiche les erreurs pour ne pas avoir de page blanche
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. Vérifier que le panier n'est pas vide
if (!isset($_SESSION['total_a_payer']) || $_SESSION['total_a_payer'] <= 0) {
    header("Location: panier.php");
    exit;
}

// 2. Préparer l'appel à l'API (EcoTechBank)
$apiUrl = "http://100.65.154.19:8081/init_payment.php";
$apiKey = "9d4b4e36e28bdac67139ff98d097bc78"; // Ta clé API

$data = [
    "amount"      => $_SESSION['total_a_payer'],
    "description" => "Commande Digital Games de " . ($_SESSION['pseudo'] ?? 'Client'),
    // Correction de l'espace dans l'URL ("Projets dev" devient "Projets%20dev")
    "return_url"  => "http://localhost/Projet%20dev/confirmation.php",
    
    // ⚠️ RAPPEL : La banque réclamait ces deux infos tout à l'heure pour ne pas planter !
    "buyer_id"    => $_SESSION['user_id'] ?? 0,
    "token"       => $_SESSION['ecotech_token'] ?? ''
];

// 3. Envoi de la requête à ton serveur
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-API-KEY: $apiKey",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
// CORRECTION : On enlève le "http_code:" qui créait une erreur de syntaxe PHP
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

// 4. Redirection vers le guichet bancaire (bank_checkout.php)
// 4. Redirection vers le guichet bancaire
if ($httpCode === 200 && isset($result['checkout_url'])) {
    
    $url_banque = $result['checkout_url'];

    // CORRECTION : Si la banque ne renvoie que "bank_checkout.php?token=...", on ajoute son adresse devant !
    if (strpos($url_banque, 'http') === false) {
        $url_banque = "http://100.65.154.19:8081/" . $url_banque;
    }

    header("Location: " . $url_banque);
    exit;

} else {
    // Mode débogage activé si ça échoue...
    die("<div style='background:#1a1c24; color:white; padding:20px; font-family:monospace;'>
            <h2 style='color:#ff4757;'>❌ Erreur Banque ($httpCode)</h2>
            <strong>Ce que la banque répond :</strong><br>
            <pre style='color:#2ecc71; background:#000; padding:10px;'>" . htmlspecialchars($response) . "</pre>
         </div>");
}
?>