<?php
header('Content-Type: application/json');

// Vos informations de bot Telegram
$botToken = '8460461285:AAHv5sPYCo9YTKA8tDLWTFMee3GplEgqPPs';
$chatId = '-1002968572368';

// Récupérer le corps de la requête POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Définir un fichier de log pour le débogage
$logFile = 'telegram_debug.log';

// Enregistrer les données reçues
file_put_contents($logFile, "Requête reçue: " . print_r($data, true) . "\n", FILE_APPEND);

if (isset($data['message'])) {
    $message = $data['message'];
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $payload = [
        'chat_id' => $chatId,
        'text' => $message,
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Enregistrer la réponse de l'API Telegram
    file_put_contents($logFile, "Réponse de Telegram (HTTP {$httpCode}): " . $response . "\n", FILE_APPEND);
    file_put_contents($logFile, "Erreur cURL: " . $curlError . "\n", FILE_APPEND);
    
    if ($httpCode == 200) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Message envoyé avec succès.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du message.', 'errorDetails' => $curlError, 'telegramResponse' => json_decode($response, true)]);
    }

} else {
    file_put_contents($logFile, "Erreur: Données invalides.\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données invalides.']);
}
?>