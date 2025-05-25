<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Требуется авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $menu_item_id = filter_var($data['menu_item_id'] ?? null, FILTER_VALIDATE_INT);
    $variant_name = filter_var($data['variant_name'] ?? null, FILTER_SANITIZE_STRING);
    $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $user_id = $_SESSION['user_id'];

    if (!$menu_item_id || !$price) {
        echo json_encode(['status' => 'error', 'message' => 'Неверные данные товара']);
        exit;
    }

    try {
        // Проверяем, есть ли уже такой товар в корзине
        $query = "SELECT * FROM cart WHERE user_id = ? AND menu_item_id = ? AND variant_name = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $menu_item_id, $variant_name]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Увеличиваем количество
            $query = "UPDATE cart SET quantity = quantity + 1 WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$existing['id']]);
        } else {
            // Добавляем новый товар
            $query = "INSERT INTO cart (user_id, menu_item_id, variant_name, price) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $menu_item_id, $variant_name, $price]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Товар добавлен в корзину']);
    } catch (PDOException $e) {
        error_log("Cart error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при добавлении в корзину']);
    }
}