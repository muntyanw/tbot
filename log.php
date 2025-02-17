<?php
/**
 * Функция для логирования сообщений, массивов и объектов.
 *
 * @param mixed  $message Текст сообщения, массив или объект.
 * @param string $level   Уровень логирования (например, INFO, ERROR).
 * @param string $logFile Путь к файлу лога.
 */
function logg($message, string $level = 'INFO', string $logFile = 'log.log'): void {
    // Если сообщение является массивом или объектом, преобразуем его в строку
    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true);
    }

    // Форматируем сообщение: [ВРЕМЯ] [УРОВЕНЬ] Сообщение
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = sprintf("[%s] [%s] %s", $timestamp, $level, $message);

    // Запись сообщения в файл (добавляем в конец файла)
    file_put_contents($logFile, $formattedMessage . PHP_EOL, FILE_APPEND);

    // Вывод сообщения в консоль:
    if (php_sapi_name() === 'cli') {
        echo $formattedMessage . PHP_EOL;
    } else {
        // Используем встроенную функцию error_log
        error_log($formattedMessage);
    }
}
?>
