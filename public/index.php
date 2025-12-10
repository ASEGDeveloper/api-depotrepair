<?php

echo phpinfo();

exit;
// define("APP_DB_HOST", '192.168.5.139');
// define("APP_DB_USER", 'depouser');
// define("APP_DB_PASSWORD", 'P@33w0rd');
// define("APP_DB_DATABASE", 'deporepair');

// // SQLSRV connection config
// $connectionInfo = [
//     "Database" => APP_DB_DATABASE,
//     "UID"      => APP_DB_USER,
//     "PWD"      => APP_DB_PASSWORD,
//     "CharacterSet" => "UTF-8",
//     "Encrypt" => "No",  // Disable SSL for testing
//     "TrustServerCertificate" => true
// ];

// // Try to connect
// $conn = sqlsrv_connect(APP_DB_HOST, $connectionInfo);

// if ($conn) {
//     echo "<h3>Database Connected Successfully ✔</h3>";
// } else {
//     echo "<h3>Database Connection Failed ❌</h3>";
//     echo "<pre>";
//     print_r(sqlsrv_errors());
//     echo "</pre>";
// }
?>
