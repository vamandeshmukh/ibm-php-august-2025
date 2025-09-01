<?php
require_once __DIR__ . '/../src/Services/AuthService.php';

session_start();
$authService = new App\Services\AuthService();

$authService->logout();

header('Location: index.php');
exit;