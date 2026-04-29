<?php
// init paiement → POST Ecotech → redirect checkout_url (plan B si timeout)
session_start();

if (!isset($_SESSION['total_a_payer']) || $_SESSION['total_a_payer'] <= 0) {
    header("Location: erreur_paiement.php?code=panier_vide&message=" . urlencode("Votre panier est vide ou le montant est invalide."));
    exit;
}

$apiUrl = "http://100.65.154.19:8081/init_payment.php";
$apiKey = "9d4b4e36e28bdac67139ff98d097bc78";

$data = [
    "amount"      => $_SESSION['total_a_payer'],
    "description" => "Commande Digital Games de " . ($_SESSION['pseudo'] ?? 'Client'),
    "return_url"  => "http://localhost/Projet%20dev/confirmation.php",
    "buyer_id"    => $_SESSION['user_id'] ?? 0,
    "token"       => $_SESSION['ecotech_token'] ?? ''
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-KEY: $apiKey", "Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['checkout_url'])) {
    $url_banque = $result['checkout_url'];
    if (strpos($url_banque, 'http') === false) {
        $url_banque = "http://100.65.154.19:8081/" . $url_banque;
    }
    header("Location: " . $url_banque);
    exit;

} elseif ($httpCode === 0) {
    // Timeout — la banque ne répond pas du tout.
    // On redirige vers le plan B plutôt que vers une page d'erreur sèche.
    header("Location: plan_b_paiement.php");
    exit;

} else {
    // La banque répond mais renvoie une erreur (mauvaise clé, montant invalide, etc.)
    $msg = "La banque a retourné une erreur (code HTTP $httpCode).";
    header("Location: erreur_paiement.php?code=banque_refus&message=" . urlencode($msg) . "&contexte=" . urlencode($response));
    exit;
}
