<?php
session_start();
require '../components/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Обработка загрузки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = '../img/uploads/';
    
    // Создаем папку для загрузок, если ее нет
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Генерируем уникальное имя файла
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $filename;
    
    // Проверяем тип файла
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['image']['type'];
    
    if (in_array($fileType, $allowedTypes)) {
        // Пытаемся загрузить файл
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $caption = $_POST['caption'] ?? '';
            
            // Защита от SQL-инъекций
            $stmt = $pdo->prepare("INSERT INTO media_content (image_path, caption) VALUES (?, ?)");
            $stmt->execute([$targetFile, $caption]);
            
            // Редирект, чтобы избежать повторной отправки формы при обновлении
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Ошибка при загрузке файла.";
        }
    } else {
        $error = "Разрешены только файлы изображений (JPEG, PNG, GIF).";
    }
}

// Удаление записи
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Сначала получаем путь к файлу
    $stmt = $pdo->prepare("SELECT image_path FROM media_content WHERE id = ?");
    $stmt->execute([$id]);
    $fileToDelete = $stmt->fetchColumn();
    
    if ($fileToDelete) {
        // Удаляем файл
        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
        }
        
        // Удаляем запись из БД
        $stmt = $pdo->prepare("DELETE FROM media_content WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Получение существующих записей
$items = $pdo->query("SELECT * FROM media_content ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель галереи</title>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .gallery-item { border: 1px solid #ddd; padding: 10px; position: relative; }
        .gallery-item img { max-width: 100%; height: auto; display: block; }
        .gallery-item .delete-btn { 
            position: absolute; 
            top: 5px; 
            right: 5px; 
            background: red; 
            color: white; 
            border: none; 
            border-radius: 50%; 
            width: 25px; 
            height: 25px; 
            cursor: pointer;
        }
        .error { color: red; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Админ-панель галереи</h1>
        
        <!-- Форма добавления -->
        <h2>Добавить новое изображение</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="image">Изображение:</label>
                <input type="file" name="image" id="image" required accept="image/jpeg, image/png, image/gif">
            </div>
            <div class="form-group">
                <label for="caption">Подпись:</label>
                <textarea name="caption" id="caption" rows="3" style="width: 100%;"></textarea>
            </div>
            <button type="submit">Загрузить</button>
        </form>

        <!-- Список существующих записей -->
        <h2>Текущие записи</h2>
        <?php if (empty($items)): ?>
            <p>Нет загруженных изображений.</p>
        <?php else: ?>
            <div class="gallery">
                <?php foreach ($items as $item): ?>
                    <div class="gallery-item">
                        <button class="delete-btn" onclick="if(confirm('Удалить эту запись?')) window.location.href='?delete=<?= $item['id'] ?>'">×</button>
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['caption']) ?>">
                        <p><?= htmlspecialchars($item['caption']) ?></p>
                        <!-- <small>Добавлено: <?= $item['created_at'] ?></small> -->
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>