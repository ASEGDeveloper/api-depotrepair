<?php

//echo phpinfo();

 

$server = "192.168.5.139";
$connectionInfo = [
    "Database" => "deporepair",
    "UID" => "depouser",
    "PWD" => "P@33w0rd",
    "Encrypt" => "No",
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($server, $connectionInfo);

if ($conn) {
    echo "✔ Connected Successfully";
} else {
    echo "❌ Connection Failed<br>";
    print_r(sqlsrv_errors());
}


?>
