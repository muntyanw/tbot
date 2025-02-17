<?php
// payment.php – формирование платежного запроса для WayForPay

require_once 'config.php';

if (!isset($_GET['orderReference']) || !isset($_GET['chat_id'])) {
    die("Неверный запрос");
}

$orderReference = $_GET['orderReference'];
$chat_id = $_GET['chat_id'];

// Используем абсолютный путь для файла заказа
$orderFile = ORDER_DIR . "/{$orderReference}.json";
if (!file_exists($orderFile)) {
    die("Заказ не найден");
}
$orderData = json_decode(file_get_contents($orderFile), true);
if ($orderData['status'] !== 'pending') {
    die("Заказ уже обработан");
}

// Параметры платежа
$orderDate = time();
$amount = AMOUNT; // Сумма заказа
$currency = "UAH";

// Детали заказа – одна позиция «Подписка»
$productNames  = ["Підписка"];
$productCounts = [1];
$productPrices = [$amount];

// Формируем строку для подписи согласно документации WayForPay
$signatureString = MERCHANT_ACCOUNT . ";" . MERCHANT_DOMAIN . ";" . $orderReference . ";" . $orderDate . ";" 
                   . $amount . ";" . $currency . ";" . implode(";", $productNames) . ";" . implode(";", $productCounts) . ";" . implode(";", $productPrices);
$merchantSignature = hash_hmac("md5", $signatureString, SECRET_KEY);
?>
<html>
<head>
    <meta charset="utf-8">
    <title>Оплата подписки</title>
</head>
<body onload="document.forms[0].submit();">
    <form method="post" action="<?php echo WAYFORPAY_URL; ?>">
        <input type="hidden" name="merchantAccount" value="<?php echo MERCHANT_ACCOUNT; ?>">
        <input type="hidden" name="merchantAuthType" value="SimpleSignature">
        <input type="hidden" name="merchantDomainName" value="<?php echo MERCHANT_DOMAIN; ?>">
        <input type="hidden" name="merchantSignature" value="<?php echo $merchantSignature; ?>">
        <input type="hidden" name="orderReference" value="<?php echo $orderReference; ?>">
        <input type="hidden" name="orderDate" value="<?php echo $orderDate; ?>">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="currency" value="<?php echo $currency; ?>">
        <input type="hidden" name="orderTimeout" value="49000">
        <?php foreach ($productNames as $name): ?>
            <input type="hidden" name="productName[]" value="<?php echo $name; ?>">
        <?php endforeach; ?>
        <?php foreach ($productPrices as $price): ?>
            <input type="hidden" name="productPrice[]" value="<?php echo $price; ?>">
        <?php endforeach; ?>
        <?php foreach ($productCounts as $count): ?>
            <input type="hidden" name="productCount[]" value="<?php echo $count; ?>">
        <?php endforeach; ?>
        <!-- Передаём идентификатор клиента (chat_id) -->
        <input type="hidden" name="clientAccountId" value="<?php echo $chat_id; ?>">
        <input type="hidden" name="returnUrl" value="<?php echo RETURN_URL; ?>">
        <input type="hidden" name="serviceUrl" value="<?php echo SERVICE_URL; ?>">
        <noscript>
            <button type="submit">Оплатить подписку</button>
        </noscript>
    </form>
    <p>Перенаправление на страницу оплаты...</p>
</body>
</html>
