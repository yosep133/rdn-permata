<?php
$host = '127.20.1.98';
$port = 8095;
$publicDir = __DIR__ . '/public';
$cert = __DIR__ . '/server.crt';
$key = __DIR__ . '/server.key';

$command = sprintf(
    'php -S %s:%d -t %s -d "openssl.cafile=%s" -d "openssl.capath=%s" -d "openssl.keyfile=%s"',
    $host,
    $port,
    $publicDir,
    $cert,
    $cert,
    $key
);

echo "Laravel development server running on https://$host:$port\n";
passthru($command);