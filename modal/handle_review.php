<?php
session_start();
// Подключение к базе данных 
require_once '../components/db_connect.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    switch ($action) {
        case 'submit_review':
            $reviewText = trim($_POST['reviewText']);
            $rating = (int)$_POST['reviewRating'];
            
            if (empty($reviewText)) {
                throw new Exception('Текст отзыва не может быть пустым');
            }
            
            if ($rating < 1 || $rating > 5) {
                throw new Exception('Некорректная оценка');
            }
            
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, review_text, rating) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $reviewText, $rating]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'get_reviews':
            $stmt = $pdo->prepare("SELECT id, review_text, rating, created_at FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'reviews' => $reviews]);
            break;
            
        case 'delete_review':
            $reviewId = (int)$_POST['review_id'];
            
            // Проверяем, что отзыв принадлежит пользователю
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE id = ? AND user_id = ?");
            $stmt->execute([$reviewId, $userId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Отзыв не найден или вы не можете его удалить');
            }
            
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$reviewId]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}