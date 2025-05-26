<?php
header("Content-Type: application/json");
session_start();
require_once 'db_connect.php';
// Очистка выходного буфера на случай лишних данных
while (ob_get_level()) ob_end_clean();

$response = ['success' => false, 'message' => ''];

try {
    // Только POST-запросы
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Метод не поддерживается");
    }

    // Основные данные
    $is_hall = ($_POST['reservation_type'] ?? '') === 'hall';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    
    // Валидация
    if (empty($date) || empty($time)) {
        throw new Exception("Не указаны дата или время");
    }

    // Подготовка данных
    $reservationData = [
        'is_hall' => $is_hall ? 1 : 0,
        'date' => $date,
        'time' => $time,
        'status' => 'pending'
    ];

    // Данные пользователя
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT full_name, phone, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        $reservationData['user_id'] = $_SESSION['user_id'];
        $reservationData['name'] = $user['full_name'];
        $reservationData['phone'] = $user['phone'];
        if ($is_hall) $reservationData['email'] = $user['email'];
    } else {
        $reservationData['name'] = $_POST['name'] ?? '';
        $reservationData['phone'] = $_POST['phone'] ?? '';
        if ($is_hall) $reservationData['email'] = $_POST['email'] ?? '';
        
        if (empty($reservationData['name']) || empty($reservationData['phone'])) {
            throw new Exception("Не заполнены обязательные поля");
        }
    }

    // Сохранение в БД
    $columns = implode(', ', array_keys($reservationData));
    $placeholders = ':' . implode(', :', array_keys($reservationData));
    
    $stmt = $pdo->prepare("INSERT INTO reservations ($columns) VALUES ($placeholders)");
    $stmt->execute($reservationData);

    $response = [
        'success' => true,
        'message' => $is_hall 
            ? "Зал успешно забронирован!" 
            : "Столик успешно забронирован!"
    ];

} catch (PDOException $e) {
    $response['message'] = "Ошибка базы данных: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Гарантированно чистый JSON вывод
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;