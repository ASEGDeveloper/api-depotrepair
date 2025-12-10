<?php

$serverName = "192.168.5.139,1433"; // SQL Server IP and port

$connectionInfo = [
    "Database" => "deporepair",
    "UID" => "depouser",
    "PWD" => "P@33w0rd",
    // Disable encryption for SQL Server 2014 compatibility
    "Encrypt" => false,
    "TrustServerCertificate" => true,
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn) {
    echo "✔ Connected Successfully!";
} else {
    echo "❌ Connection Failed!<br>";
    if (($errors = sqlsrv_errors()) != null) {
        foreach ($errors as $error) {
            echo "SQLSTATE: " . $error['SQLSTATE'] . "<br>";
            echo "Code: " . $error['code'] . "<br>";
            echo "Message: " . $error['message'] . "<br>";
        }
    }
}

// Optional: Close connection
// sqlsrv_close($conn);
?>
