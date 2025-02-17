<?php
// config.php – настройки мерчанта и Telegram-бота

define('MERCHANT_ACCOUNT', 'freelance_user_67ad8c73421d0');
define('MERCHANT_DOMAIN', 'tbot.kirro.ro');
define('SECRET_KEY', '64e20d6b02bfb5cae7e77d2f1f848cb3805db786');  // Секретный ключ для HMAC_MD5 (выдается WayForPay)
define('WAYFORPAY_URL', 'https://secure.wayforpay.com/pay');
define('RETURN_URL', 'https://tbot.kirro.ro/return.php');
define('SERVICE_URL', 'https://tbot.kirro.ro/callback.php');
define('API_VERSION', 1); // версия API для Check Status

define('TELEGRAM_BOT_TOKEN', '7789086814:AAHdJEXpdhCiG8V6FZoOj0PI7YSP0PjamOI');
// Идентификатор вашего приватного канала
define('TELEGRAM_CHANNEL_ID', '-1002364418673'); // ставити -100 перед айді

define('ORDER_DIR', __DIR__ . '/orders');
define('MESS_PAY_SUCCESS', "Ваш платіж підтверджено!\nПриєднайтесь до каналу за наступним посиланням: " );
define('MESS_PAY_FAULT', "Ваш платіж підтверджено, але виникла помилка при генерації посилання. Зверніться до підтримки." );
define('MESS_PAY_INVITE', "Здравствуйте!\nДля активации подписки оплатите заказ по ссылке:\n"  );
define('AMOUNT', "1.00");
?>
