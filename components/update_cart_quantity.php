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
    echo json_encode(['status' => 'error', 'message' => 'Неверные данные']);
    exit;
}

try {
    // Проверяем, что товар принадлежит пользователю
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Количество обновлено']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Товар не найден в вашей корзине']);
    }
} catch (PDOException $e) {
    error_log("Update cart quantity error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при обновлении количества']);
}