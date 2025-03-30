<?php
$directory = './fake-1000/';
$image_files = array_diff(scandir($directory), array('..', '.'));

// Массив для хранения значений encoding
$encodings = [];

// Лимитируем количество картинок до 1000
$image_files = array_slice($image_files, 0, 500);

// Функция для отправки картинки через cURL
function sendImage($image_path) {
    $ch = curl_init();

    // Открываем изображение
    $cFile = curl_file_create(
        $image_path
    );
    $postFields = ['photo' => $cFile];
    // Параметры запроса
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:5000/api/v1/check'); // Укажите ваш URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    // Выполнение запроса и получение ответа
    $response = curl_exec($ch);

    // Проверка на ошибки
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    // Закрываем cURL
    curl_close($ch);

    return $response;
}

// Цикл по картинкам
foreach ($image_files as $image_file) {
    $image_path = $directory . $image_file;

    // Отправляем картинку и получаем ответ
    $response = sendImage($image_path);

    // Если ответ получен, обрабатываем его
    if ($response) {
        // Преобразуем ответ в массив
        $response_data = json_decode($response, true);

        // Проверяем, что поле 'encoding' существует
        if (isset($response_data['data']['encoding'])) {
            // Добавляем encoding в массив
            $encodings[] = $response_data['data']['encoding'];
        }
    }
}

$encodings_string = '"' . implode('","', $encodings) . '"';

// Записываем массив encodings в текстовый файл
file_put_contents('encodings.txt', $encodings_string);

echo "Процесс завершен. Все encodings записаны в файл encodings.txt.\n";
