<?php
    // define server connection
    define("SERVER", "localhost:8889");
    define("USERNAME", "root");
    define("PASSWORD", "root");

    header('Access-Control-Allow-Origin: *');

function encrypt($plainText) {
    $secretKey = md5("njkq23ro900efdiovnq43rq09234nqkdf0023");
    $iv = substr( hash( 'sha256', "aaaabbbbcccccddddeweee" ), 0, 16 );
    $encryptedText = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $iv);
    return base64_encode($encryptedText);
}

function decrypt($encryptedText) {
    $key = md5("njkq23ro900efdiovnq43rq09234nqkdf0023");
    $iv = substr( hash( 'sha256', "aaaabbbbcccccddddeweee" ), 0, 16 );
    $decryptedText = openssl_decrypt(base64_decode($encryptedText), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $decryptedText;
}