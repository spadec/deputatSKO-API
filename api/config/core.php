<?php
// показывать сообщения об ошибках 
error_reporting(E_ALL);
 
// установить часовой пояс по умолчанию 
date_default_timezone_set('Asia/Omsk');
 
// переменные, используемые для JWT 
$key = "big orange key";
$iss = "http://auth.my";
$aud = "http://auth.my";
$iat = 1356999524;
$nbf = 1357000000;
?>