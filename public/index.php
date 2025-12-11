<?php



// $serverName = "192.168.5.139,1433";
// $connectionInfo = [
//     "Database" => "deporepair",
//     "UID" => "depouser",
//     "PWD" => "P@33w0rd",
//    "Encrypt" => "DISABLE",
//     "TrustServerCertificate" => true
// ];



// $serverName = "192.168.5.139"; // No need to include port if default 1433

// $connectionInfo = [
//     "Database" => "DEPOREPAIR",
//     "UID" => "pdms",
//     "PWD" => "PhpC@ase2608",
//     "Encrypt" => "Optional",       // Use Optional for ODBC 18
//     "TrustServerCertificate" => true
// ];
try {
    $dsn = "sqlsrv:Server=192.168.5.139,1433;Database=DEPOREPAIR;Encrypt=no;TrustServerCertificate=yes";
    $conn = new PDO($dsn, "pdms", "PhpC@ase2608");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully with PDO!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
