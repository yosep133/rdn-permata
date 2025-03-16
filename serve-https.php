<?php
// filepath: c:\Users\FAHRI\Documents\Yosep nitip\RDN Permata\Laravel Permata\rdn-permata\serve-https.php

$command = 'D:\php\php.exe -S 172.20.1.98:8095 -t public -c "' . __DIR__ . '/php.ini"';
$descriptorspec = [
    0 => ["pipe", "r"],  // stdin is a pipe that the child will read from
    1 => ["pipe", "w"],  // stdout is a pipe that the child will write to
    2 => ["pipe", "w"]   // stderr is a pipe that the child will write to
];

$process = proc_open($command, $descriptorspec, $pipes, __DIR__);

if (is_resource($process)) {
    echo "Server running on [https://172.20.1.98:8095].\n";
    while ($line = fgets($pipes[1])) {
        echo $line;
    }
    proc_close($process);
}