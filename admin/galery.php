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
        :root {
            --dark: #24211C;
            --accent: #c0875c;
            --light: #f7eabd;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-gallery {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem 12% 3rem;
        }
        
        h1, h2 {
            color: var(--dark);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        h1 {
            font-size: 2rem;
        }
        
        h2 {
            font-size: 1.5rem;
        }
        
        /* Кнопка "Назад" */
        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            color: #a57352;
            text-decoration: underline;
        }
        
        /* Форма */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        input[type="file"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Кнопки */
        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background-color: var(--accent);
            color: white;
        }
        
        .btn:hover {
            background-color: #a57352;
        }
        
        /* Галерея */
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .gallery-item {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1rem;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            transition: transform 0.2s;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .gallery-item img {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .gallery-item p {
            margin: 0.5rem 0;
            color: var(--dark);
        }
        
        /* Кнопка удаления */
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .delete-btn:hover {
            background-color: #bb2d3b;
        }
        
        /* Сообщения об ошибках */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .admin-gallery {
                margin: 1rem;
                padding: 1rem;
            }
            
            .gallery {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-gallery">
        <a href="../admin.php" class="back-link">← Назад в админ-панель</a>
        <h1>Админ-панель галереи</h1>
        
        <!-- Форма добавления -->
        <h2>Добавить новое изображение</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="image">Изображение:</label>
                <input type="file" name="image" id="image" required accept="image/jpeg, image/png, image/gif">
            </div>
            <div class="form-group">
                <label for="caption">Подпись:</label>
                <textarea name="caption" id="caption" rows="3"></textarea>
            </div>
            <button type="submit" class="btn">Загрузить</button>
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
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>