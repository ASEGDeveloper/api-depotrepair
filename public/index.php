<?php

//echo phpinfo();
$server = "192.168.5.139";

$connectionInfo = [
    "Database" => "deporepair",
    "UID" => "depouser",
    "PWD" => "P@33w0rd",

    // Important for SQL Server without TLS
    "Encrypt" => "No",
    "TrustServerCertificate" => 1,

    // Extra options for compatibility
    "DisableStatementPooling" => true,
    "Mars" => false
];

$conn = sqlsrv_connect($server, $connectionInfo);

if ($conn) {
    echo "✔ Connected Successfully";
} else {
    echo "❌ Connection Failed<br>";
    print_r(sqlsrv_errors());
}


?>
