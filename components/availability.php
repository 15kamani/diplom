<?php

require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    $date = $_GET['date'] ?? date('Y-m-d');
    $time = $_GET['time'] ?? '';
    $table_number = $_GET['table_number'] ?? null;
    $is_hall = isset($_GET['is_hall']) ? (bool)$_GET['is_hall'] : false;
    
    // Проверяем, забронирован ли зал на эту дату (учитываем только активные брони)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as hall_count 
        FROM reservations 
        WHERE date = ? 
        AND is_hall = 1 
        AND status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$date]);
    $hallCount = $stmt->fetch()['hall_count'];
    $isHallBooked = $hallCount > 0;
    
    // Проверяем, есть ли брони столиков на эту дату (если пытаемся забронировать зал)
    $isTablesBooked = false;
    if ($is_hall) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as tables_count 
            FROM reservations 
            WHERE date = ? 
            AND is_hall = 0 
            AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$date]);
        $isTablesBooked = $stmt->fetch()['tables_count'] > 0;
    }
    
    // Получаем занятые столики с учетом временного интервала
    $bookedTables = [];
    if (!$isHallBooked && !$isTablesBooked && !empty($time)) {
        $requested_time = strtotime($time);
        $requested_minutes = date('H', $requested_time) * 60 + date('i', $requested_time);
        
        $stmt = $pdo->prepare("
            SELECT DISTINCT table_number, time 
            FROM reservations 
            WHERE date = ? 
            AND is_hall = 0 
            AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$date]);
        
        while ($row = $stmt->fetch()) {
            $existing_time = strtotime($row['time']);
            $existing_minutes = date('H', $existing_time) * 60 + date('i', $existing_time);
            
            // Если разница меньше 2 часов, считаем столик занятым
            if (abs($existing_minutes - $requested_minutes) < 120) {
                $bookedTables[] = (int)$row['table_number'];
            }
        }
    }
    
    // Проверяем, можно ли забронировать выбранный вариант
    $canBook = true;
    $message = '';
    
    if ($is_hall) {
        if ($isHallBooked) {
            $canBook = false;
            $message = 'Зал уже забронирован на эту дату';
        } elseif ($isTablesBooked) {
            $canBook = false;
            $message = 'Нельзя забронировать зал, когда есть брони столиков на эту дату';
        } elseif ($hallCount >= 1) {
            $canBook = false;
            $message = 'Зал можно забронировать только один раз в день';
        }
    } else {
        if ($isHallBooked) {
            $canBook = false;
            $message = 'Зал уже забронирован на эту дату';
        } elseif (in_array($table_number, $bookedTables)) {
            $canBook = false;
            $message = 'Этот столик уже забронирован на это время или с интервалом менее 2 часов';
        }
    }
    
    echo json_encode([
        'bookedTables' => array_unique($bookedTables),
        'isHallBooked' => $isHallBooked,
        'isTablesBooked' => $isTablesBooked,
        'isDayBlocked' => $isHallBooked || $isTablesBooked,
        'canBook' => $canBook,
        'message' => $message,
        'hallBookingsCount' => $hallCount // Добавил для отладки
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}