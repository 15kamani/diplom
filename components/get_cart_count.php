<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'success', 'count' => 0]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'status' => 'success',
        'count' => $result['total'] ?: 0
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при получении количества товаров']);
}