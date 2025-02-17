<?php
// callback.php – обробка повідомлень від WayForPay

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'log.php';

logg("Is callback");

$json = file_get_contents('php://input');
$data = $json ? json_decode($json, true) : $_POST;

if (empty($data)) {
    http_response_code(400);
    logg("Немає даних");
    exit("Немає даних");
}

logg("Отримані дані:");
logg($data);

// Витягуємо необхідні параметри
$merchantAccount   = $data['merchantAccount'] ?? '';
$orderReference    = $data['orderReference'] ?? '';
$amount            = $data['amount'] ?? '';
$currency          = $data['currency'] ?? '';
$authCode          = $data['authCode'] ?? '';
$cardPan           = $data['cardPan'] ?? '';
$transactionStatus = $data['transactionStatus'] ?? '';
$reasonCode        = $data['reasonCode'] ?? '';

logg("Формуємо рядок для підпису:");
$signatureString = $merchantAccount . ";" . $orderReference . ";" . $amount . ";" . $currency . ";" . $authCode . ";" . $cardPan . ";" . $transactionStatus . ";" . $reasonCode;
$calculatedSignature = hash_hmac("md5", $signatureString, SECRET_KEY);

$providedSignature = $data['merchantSignature'] ?? '';
if ($calculatedSignature !== $providedSignature) {
    http_response_code(400);
    logg("Невірний підпис");
    exit("Невірний підпис");
}

logg("Обробка успішного платежу");
$orderFile = "orders/{$orderReference}.json";
if (file_exists($orderFile)) {
    $orderData = json_decode(file_get_contents($orderFile), true);
    $chat_id = $orderData['chat_id'];
    
    // Якщо запрошення вже відправлено, не відправляємо повторно
    if (!empty($orderData['inviteSent']) && $orderData['inviteSent'] === true) {
        logg("Запрошення вже відправлено. Повторна відправка не потрібна.");
    } else {
        // Якщо посилання ще не згенеровано, генеруємо його
        if (empty($orderData['inviteLink'])) {
            logg("Генеруємо унікальне посилання-запрошення для закритого каналу");
            $inviteLink = getUniqueChatInviteLink();
            $orderData['inviteLink'] = $inviteLink;
        } else {
            $inviteLink = $orderData['inviteLink'];
        }
        // Якщо посилання успішно отримано, відправляємо повідомлення і встановлюємо прапорець
        if ($inviteLink) {
            $message = MESS_PAY_SUCCESS . $inviteLink;
            logg("Відправляємо повідомлення: " . $message);
            sendTelegramMessage($chat_id, $message);
            $orderData['inviteSent'] = true;
        } else {
            $message = MESS_PAY_FAULT;
            sendTelegramMessage($chat_id, $message);
        }
        // Зберігаємо оновлені дані замовлення
        file_put_contents($orderFile, json_encode($orderData));
    }
}

logg("Формування відповіді для WayForPay");
$responseData = [
    "orderReference" => $orderReference,
    "status"         => "accept",
    "time"           => time(),
    "signature"      => hash_hmac("md5", $orderReference . ";" . "accept" . ";" . time(), SECRET_KEY)
];

header('Content-Type: application/json');
echo json_encode($responseData);

function sendTelegramMessage($chat_id, $message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text'    => $message
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

function getUniqueChatInviteLink() {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/exportChatInviteLink";
    logg("Виклик URL: " . $url);
    $params = [
        'chat_id' => TELEGRAM_CHANNEL_ID
    ];
    logg("Параметри: " . print_r($params, true));
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    logg("Відповідь від Telegram: " . $response);
    
    $result = json_decode($response, true);
    logg("Результат: " . print_r($result, true));
    
    return ($result['ok'] ?? false) ? $result['result'] : false;
}
?>
