<?php

$dbStatus = [
    'connected' => false,
    'message' => 'Usando dados demonstrativos',
];

$server = 'localhost';
$database = 'Site Driver';
$username = '';
$password = '';

$dsn = "sqlsrv:Server=$server;Database=$database;Encrypt=false;TrustServerCertificate=true";
$pdo = null;

try {
    if (!in_array('sqlsrv', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('Driver PDO SQL Server nao esta habilitado no PHP.');
    }

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $dbStatus = [
        'connected' => true,
        'message' => 'Banco conectado',
    ];
} catch (Throwable $e) {
    $pdo = null;
    $dbStatus = [
        'connected' => false,
        'message' => $e->getMessage(),
    ];
}
