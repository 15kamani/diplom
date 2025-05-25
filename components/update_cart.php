<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Требуется авторизация']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$cart_id = filter_var($data['cart_id'] ?? null, FILTER_VALIDATE_INT);
$quantity = filter_var($data['quantity'] ?? null, FILTER_VALIDATE_INT);

if (!$cart_id || !$quantity || $quantity < 1 || $quantity > 100) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректные данные']);
    exit;
}

try {
    // Проверяем принадлежность товара пользователю
    $checkStmt = $pdo->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$cart_id, $_SESSION['user_id']]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Товар не найден в вашей корзине']);
        exit;
    }

    // Обновляем количество
    $updateStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $updateStmt->execute([$quantity, $cart_id]);
    
    echo json_encode(['status' => 'success', 'message' => 'Количество обновлено']);
} catch (PDOException $e) {
    error_log("Update cart error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера при обновлении']);
}