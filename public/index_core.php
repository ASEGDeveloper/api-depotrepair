<?php



// $serverName = "192.168.5.139,1433";
// $connectionInfo = [
//     "Database" => "deporepair",
//     "UID" => "depouser",
//     "PWD" => "P@33w0rd",
//    "Encrypt" => "DISABLE",
//     "TrustServerCertificate" => true
// ];

$serverName = "192.168.5.139,1433";

$connectionInfo = [
    "Database" => "DEPOREPAIR",
    "UID" => "pdms",
    "PWD" => "PhpC@ase2608",
    "Encrypt" => false,
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    echo "Connection failed:\n";
    die(print_r(sqlsrv_errors(), true));
} else {
    echo "Connected successfully!";
    sqlsrv_close($conn);
}