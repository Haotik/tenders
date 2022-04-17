<?php
//файл в файлс 
//ид тендера в _POST
//загрузка базового файла с лотами в тендер для дальнейшей рассылки

$path = __DIR__ . "/files/".$_POST['tender_id'];
$uploadfile = $path . "/base_data.xlsx";
mkdir($path);
move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

file_put_contents('log.txt', json_encode($_FILES).PHP_EOL.json_encode($_POST).PHP_EOL,FILE_APPEND);