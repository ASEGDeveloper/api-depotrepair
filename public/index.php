<?php

 // phpinfo();

// exit; 
$serverName = "192.168.5.139,1433";
$connectionInfo = [
    "Database" => "deporepair",
    "UID" => "depouser",
    "PWD" => "P@33w0rd",
   "Encrypt" => true, // This is the crucial change to fix the 'unsupported protocol' error
    "TrustServerCertificate" => false,
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
} else {
    echo "Connected successfully!";
}
