<?php
// bot.php – обработчик входящих сообщений от Telegram

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'config.php';
require_once 'log.php';

logg("Получаем входящие данные от Telegram");
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'] ?? '';

    if ($text === '/start') {
        logg("Генерируем уникальный идентификатор заказа (invoice)"); 
        $orderReference = 'order_' . $chat_id . '_' . time();

        if (!is_dir(ORDER_DIR)) {
            logg("Create folder orders"); 
            mkdir(ORDER_DIR, 0777, true);
        }

        $orderData = [
            'chat_id'        => $chat_id,
            'orderReference' => $orderReference,
            'status'         => 'pending',
            'created'        => time()
        ];

        $pathOrder = ORDER_DIR . "/{$orderReference}.json";
        logg("pathOrder = " . $pathOrder);
        logg("orderData = " . $orderData);
        file_put_contents($pathOrder, json_encode($orderData));

        logg("Формируем ссылку на страницу оплаты");
        $paymentLink = "https://" . MERCHANT_DOMAIN . "/payment.php?orderReference=" 
                        . urlencode($orderReference) 
                        . "&chat_id=" . urlencode($chat_id);

        logg("Отправляем сообщение с ссылкой на оплату");
        $message = MESS_PAY_INVITE . $paymentLink;
        sendTelegramMessage($chat_id, $message);
    }
}

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
?>
