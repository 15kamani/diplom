<?php
session_start();
require '../components/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}
// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    $detailed_description = $_POST['detailed_description'] ?? '';
    $event_url = $_POST['event_url'] ?? '';
    
    // Обработка загрузки изображений
    $uploaded_images = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_FILES["image{$i}"]['name'])) {
            $target_dir = "../img/uploads/events/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES["image{$i}"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image{$i}"]["tmp_name"], $target_file)) {
                $uploaded_images["image{$i}"] = $target_file;
            }
        }
    }
    
    // Сохранение в базу данных
    $stmt = $pdo->prepare("INSERT INTO events (title, short_description, detailed_description, event_url, image1, image2, image3, image4) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $title,
        $short_description,
        $detailed_description,
        $event_url,
        $uploaded_images['image1'] ?? null,
        $uploaded_images['image2'] ?? null,
        $uploaded_images['image3'] ?? null,
        $uploaded_images['image4'] ?? null
    ]);
    
    $_SESSION['message'] = 'Событие успешно добавлено!';
    header('Location: event.php?page=events');
    exit();
}

// Получение списка событий для отображения
$events = $pdo->query("SELECT * FROM events ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png" type="image/x-icon">
    <title>События</title>
    <style>
    html{
        background-color: #f7eabd;
    }
    /* Основные стили из админ-панели */
    .admin-container {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-top: 2rem;
        margin-bottom: 3rem;
        margin-left: 12%;
        margin-right: 12%;
    }
    
    .btn-admin {
        background-color: var(--accent);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s;
    }
    
    .btn-admin:hover {
        background-color: #a57352;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-logout {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-logout:hover {
        background-color: #bb2d3b;
    }
    
    /* Специфичные стили для страницы событий */
    .event-form, .events-list {
        background-color: #f9f9f9;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }
    
    .event-form h3, .events-list h3 {
        color: var(--accent);
        border-left: 4px solid var(--accent);
        padding-left: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--dark);
    }
    
    .form-group input[type="text"],
    .form-group input[type="url"],
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: border 0.3s;
    }
    
    .form-group input[type="text"]:focus,
    .form-group input[type="url"]:focus,
    .form-group textarea:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(192, 135, 92, 0.2);
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-group small {
        display: block;
        margin-top: 0.5rem;
        color: #6c757d;
        font-size: 0.85rem;
    }
    
    .image-uploads {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .image-uploads input[type="file"] {
        padding: 0.5rem;
        background: #f1f1f1;
        border-radius: 8px;
    }
    
    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .event-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    
    .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .event-images {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .event-images img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .event-card h4 {
        color: var(--dark);
        margin-bottom: 0.75rem;
        font-size: 1.25rem;
    }
    
    .event-short-description {
        color: #555;
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    
    .event-detailed-description ul {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .event-detailed-description li {
        margin-bottom: 0.5rem;
        color: #555;
    }
    
    .event-link {
        display: inline-block;
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 1rem;
        transition: color 0.3s;
    }
    
    .event-link:hover {
        color: #a57352;
        text-decoration: underline;
    }
    
    .btn-delete {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .btn-delete:hover {
        background-color: #bb2d3b;
    }
    
    /* Кнопка "Назад" */
    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    
    .back-link:hover {
        color: #a57352;
        text-decoration: underline;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .admin-container {
            padding: 1rem;
        }
        
        .image-uploads {
            grid-template-columns: 1fr;
        }
        
        .events-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<div class="admin-container">
    <a href="../admin.php" class="back-link">← Назад в админ-панель</a>
    <h2 style="color: var(--dark); margin-bottom: 1.5rem;">Управление событиями</h2>
    
    <!-- Форма добавления нового события -->
    <div class="event-form">
        <h3>Добавить новое событие</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Название события:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="short_description">Краткое описание:</label>
                <textarea id="short_description" name="short_description" rows="3" required></textarea>
                <small>Краткое описание события, которое будет отображаться в превью</small>
            </div>
            
            <div class="form-group">
                <label for="detailed_description">Подробное описание (по пунктам):</label>
                <textarea id="detailed_description" name="detailed_description" rows="5" required></textarea>
                <small>Каждая новая строка будет преобразована в отдельный пункт при выводе</small>
            </div>
            
            <div class="form-group">
                <label for="event_url">Ссылка на событие:</label>
                <input type="url" id="event_url" name="event_url" required>
            </div>
            
            <div class="form-group">
                <label>Изображения (можно загрузить 3-4 фото):</label>
                <div class="image-uploads">
                    <input type="file" name="image1" accept="image/*">
                    <input type="file" name="image2" accept="image/*">
                    <input type="file" name="image3" accept="image/*">
                    <input type="file" name="image4" accept="image/*">
                </div>
            </div>
            
            <button type="submit" class="btn-admin">Сохранить событие</button>
        </form>
    </div>
    
    <!-- Список существующих событий -->
    <div class="events-list">
        <h3>Существующие события</h3>
        
        <?php if (empty($events)): ?>
            <p>Нет добавленных событий</p>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-images">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <?php if (!empty($event["image{$i}"])): ?>
                                    <img src="<?= htmlspecialchars($event["image{$i}"]) ?>" alt="Изображение события <?= $i ?>">
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        
                        <h4><?= htmlspecialchars($event['title']) ?></h4>
                        
                        <div class="event-short-description">
                            <?= nl2br(htmlspecialchars($event['short_description'])) ?>
                        </div>
                        
                        <div class="event-detailed-description">
                            <?php 
                            $lines = explode("\n", $event['detailed_description']);
                            echo '<ul>';
                            foreach ($lines as $line) {
                                echo '<li>' . htmlspecialchars(trim($line)) . '</li>';
                            }
                            echo '</ul>';
                            ?>
                        </div>
                        
                        <a href="<?= htmlspecialchars($event['event_url']) ?>" target="_blank" class="event-link">Перейти к событию</a>
                        
                        <form method="POST" action="delete_event.php" class="delete-form">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" class="btn-delete">Удалить</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</html>