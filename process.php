<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $photo = $_FILES['photo'];

    if ($photo['error'] === UPLOAD_ERR_OK) {
        $tmp_file = $photo['tmp_name'];
        $flask_url = "http://127.0.0.1:5000/api/v1/check";

        $ch = curl_init($flask_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $cFile = curl_file_create(
            $photo['tmp_name'],
            $photo['type'],
            $photo['name']
        );
        $postFields = ['photo' => $cFile];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($ch);

        curl_close($ch);

        echo $response;
    } else {
        echo "Ошибка загрузки файла.";
    }
} else {
    echo "Файл не был отправлен.";
}