<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Необходима авторизация']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$reservationId = $data['reservation_id'] ?? null;
$reason = $data['reason'] ?? 'Отменено пользователем';

if (!$reservationId) {
    echo json_encode(['status' => 'error', 'message' => 'Не указан ID бронирования']);
    exit;
}

try {
    // Проверяем, что бронирование принадлежит пользователю
    $stmt = $pdo->prepare("SELECT user_id FROM reservations WHERE id = ?");
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch();

    if (!$reservation || $reservation['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['status' => 'error', 'message' => 'Бронирование не найдено или не принадлежит вам']);
        exit;
    }

    // Обновляем статус бронирования
    $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled', cancel_reason = ? WHERE id = ?");
    $stmt->execute([$reason, $reservationId]);

    echo json_encode(['status' => 'success', 'message' => 'Бронирование успешно отменено']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}