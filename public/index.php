<?php

//echo phpinfo();
$serverName = "tcp:192.168.5.139,1433";

$connectionInfo = [
    "Database" => "deporepair",
    "UID" => "depouser",
    "PWD" => "P@33w0rd",
    "Encrypt" => 0,
    "TrustServerCertificate" => 1
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn) {
    echo "✔ Connected Successfully using Driver 17";
} else {
    echo "❌ Connection Failed<br>";
    print_r(sqlsrv_errors());
}


?>
