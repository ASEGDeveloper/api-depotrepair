<?php

$serverName = "192.168.5.139,1433";

$connectionInfo = [
    "Database" => "deporepair",
    "UID" => "depouser",
    "PWD" => "P@33w0rd",
    "Encrypt" => "no",                  // disable encryption
    "TrustServerCertificate" => "yes",  // avoid certificate validation
    "LoginTimeout" => 30
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn) {
    echo "Connected!";
} else {
    print_r(sqlsrv_errors());
}
