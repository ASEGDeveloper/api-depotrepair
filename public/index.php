<?php

  phpinfo();

// exit; 
$serverName = "192.168.5.139,1433";
$connectionInfo = [
    "Database" => "deporepair",
    "UID" => "depouser",
    "PWD" => "P@33w0rd",
    "Encrypt" => true,                 // enable encryption
    "TrustServerCertificate" => true,  // bypass certificate check
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
} else {
    echo "Connected successfully!";
}
