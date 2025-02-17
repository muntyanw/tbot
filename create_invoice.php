<?php
// create_invoice.php – формирование счета (invoice) через API WayForPay

require_once 'config.php';

// Параметры для создания счета
$transactionType  = "CREATE_INVOICE";
$merchantAuthType = "SimpleSignature";
$apiVersion       = 1;
$language         = "ru";
$serviceUrl       = SERVICE_URL;

$orderReference   = "order_" . time();
$orderDate        = time();
$amount           = 1547.36;
$currency         = "UAH";
$orderTimeout     = 86400;

$productNames   = ["Подписка"];
$productCounts  = [1];
$productPrices  = [$amount];

$paymentSystems = "card;privat24";
$clientFirstName = "Bulba";
$clientLastName  = "Taras";
$clientEmail     = "rob@mail.com";
$clientPhone     = "380556667788";

// Формируем строку для подписи согласно документации WayForPay
$signatureString = MERCHANT_ACCOUNT . ";" .
                   MERCHANT_DOMAIN . ";" .
                   $orderReference . ";" .
                   $orderDate . ";" .
                   $amount . ";" .
                   $currency . ";" .
                   implode(";", $productNames) . ";" .
                   implode(";", $productCounts) . ";" .
                   implode(";", $productPrices);

$merchantSignature = hash_hmac("md5", $signatureString, SECRET_KEY);

// Формируем данные для запроса
$postData = [
    "transactionType"    => $transactionType,
    "merchantAccount"    => MERCHANT_ACCOUNT,
    "merchantAuthType"   => $merchantAuthType,
    "merchantDomainName" => MERCHANT_DOMAIN,
    "merchantSignature"  => $merchantSignature,
    "apiVersion"         => $apiVersion,
    "language"           => $language,
    "serviceUrl"         => $serviceUrl,
    "orderReference"     => $orderReference,
    "orderDate"          => $orderDate,
    "amount"             => $amount,
    "currency"           => $currency,
    "orderTimeout"       => $orderTimeout,
    "productName"        => $productNames,
    "productPrice"       => $productPrices,
    "productCount"       => $productCounts,
    "paymentSystems"     => $paymentSystems,
    "clientFirstName"    => $clientFirstName,
    "clientLastName"     => $clientLastName,
    "clientEmail"        => $clientEmail,
    "clientPhone"        => $clientPhone
];

$ch = curl_init("https://api.wayforpay.com/api");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}
curl_close($ch);

header("Content-Type: application/json");
echo $response;
?>
