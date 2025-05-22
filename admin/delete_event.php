<?php
    require '../components/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    
    // Получаем информацию о событии для удаления изображений
    $stmt = $pdo->prepare("SELECT image1, image2, image3, image4 FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        // Удаляем связанные изображения
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($event["image{$i}"]) && file_exists($event["image{$i}"])) {
                unlink($event["image{$i}"]);
            }
        }
        
        // Удаляем запись из базы данных
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
    }
    
    $_SESSION['message'] = 'Событие успешно удалено!';
}

header('Location: ../admin.php?page=events');
exit();
?>