<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Проверяем, что бронь принадлежит пользователю
        $stmt = $pdo->prepare("SELECT user_id FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        if ($booking && ($booking['user_id'] == $_SESSION['user_id'])) {
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        // Логирование ошибки
        error_log("Ошибка при отмене брони: " . $e->getMessage());
        header("Location: profile.php?error=1");
        exit;
    }
} else {
    header("Location: ../profile.php");
    exit;
}