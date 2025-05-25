<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Требуется авторизация']);
    exit;
}

$cart_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$cart_id) {
    echo json_encode(['status' => 'error', 'message' => 'Неверный ID товара']);
    exit;
}

try {
    // Проверяем существование товара в корзине пользователя
    $checkStmt = $pdo->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$cart_id, $_SESSION['user_id']]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Товар не найден в вашей корзине']);
        exit;
    }

    // Удаляем товар
    $deleteStmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    $deleteStmt->execute([$cart_id]);
    
    echo json_encode(['status' => 'success', 'message' => 'Товар успешно удален']);
} catch (PDOException $e) {
    error_log("Remove from cart error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера при удалении товара']);
}