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

<div class="admin-container">
    <a href="../admin.php">Назад</a>
    <h2>Управление событиями</h2>
    
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
            
            <button type="submit" class="btn">Сохранить событие</button>
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

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.event-form, .events-list {
    background: #f9f9f9;
    padding: 20px;
    margin-bottom: 30px;
    border-radius: 5px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input[type="text"],
.form-group input[type="url"],
.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group textarea {
    min-height: 100px;
}

.image-uploads {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.btn {
    background: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn:hover {
    background: #45a049;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.event-card {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 5px;
    background: white;
}

.event-images {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 10px;
}

.event-images img {
    width: 100%;
    height: auto;
    border-radius: 3px;
}

.event-short-description {
    margin: 10px 0;
    line-height: 1.5;
}

.event-detailed-description ul {
    padding-left: 20px;
    margin: 10px 0;
}

.event-link {
    display: inline-block;
    margin-top: 10px;
    color: #2196F3;
    text-decoration: none;
}

.event-link:hover {
    text-decoration: underline;
}

.btn-delete {
    background: #f44336;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    margin-top: 10px;
}

.btn-delete:hover {
    background: #d32f2f;
}
</style>