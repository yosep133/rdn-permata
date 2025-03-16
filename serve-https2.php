<?php
$host = '127.20.1.98';
$port = 8095;
$publicDir = __DIR__ . '\public';
$cert = __DIR__ . '\CABundle.crt';
$key = __DIR__ . '\key_star_kiwoom_co_id_2024.key';

$command = sprintf(
    'D:\php\php.exe -S %s:%d -t %s -d "openssl.cafile=%s" -d "openssl.capath=%s" -d "openssl.keyfile=%s"',
    $host,
    $port,
    $publicDir,
    $cert,
    $cert,
    $key
);

echo "Laravel development server running on https://$host:$port\n";
passthru($command);