<?php
$apikey = '712573744682373f9f3dca1.53433331';
$site_id = '105895032'; // visible dans ton dashboard CinetPay
$transaction_id = uniqid(); // ID unique pour chaque transaction
$amount = 5000; // Montant à payer en FCFA
$currency = 'XOF'; // Devise
$description = "Achat sur Lavy Couture";

// URL de retour (quand le paiement est fini)
$return_url = 'https://tonsite.com/retour.php'; 
$notify_url = 'https://tonsite.com/notification.php'; // pour traitement automatique

$data = [
    'transaction_id' => $transaction_id,
    'amount' => $amount,
    'currency' => $currency,
    'description' => $description,
    'customer_name' => 'Nom du client',
    'customer_email' => 'email@client.com',
    'customer_phone_number' => '2250700000000',
    'notify_url' => $notify_url,
    'return_url' => $return_url
];

// Envoi des infos à CinetPay
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api-checkout.cinetpay.com/v2/payment');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: '.$apikey,
    'site_id: '.$site_id
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
if (isset($result['data']['payment_url'])) {
    // Rediriger vers le lien de paiement
    header('Location: ' . $result['data']['payment_url']);
    exit;
} else {
    echo "Erreur de paiement : ";
    print_r($result);
}
?>
