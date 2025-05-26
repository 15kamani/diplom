<?php

session_start();
require '../components/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

// Обработка действий
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// Обработка удаления новости
if ($action === 'delete' && $id > 0) {
    // Получаем информацию о новости для удаления изображения
    $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    
    if ($news) {
        // Удаляем изображение, если оно есть
        if (!empty($news['image']) && file_exists('../' . $news['image'])) {
            unlink('../' . $news['image']);
        }
        
        // Удаляем запись из БД
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = 'Новость успешно удалена!';
    } else {
        $_SESSION['error'] = 'Новость не найдена!';
    }
    
    header('Location: news.php?page=news');
    exit;
}

// Обработка формы добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация и очистка данных
    $title = trim($_POST['title']);
    $short_desc = trim($_POST['short_desc']);
    $full_desc = trim($_POST['full_desc'] ?? '');
    $html_code = trim($_POST['html_code'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    $id = $_POST['id'] ?? 0;
    
    // Обработка загрузки изображения
    $image_path = $_POST['existing_image'] ?? '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/uploads/news/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('news_') . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        // Проверка типа файла
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Удаляем старое изображение, если оно есть
                if (!empty($image_path) && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                $image_path = 'img/uploads/news/' . $file_name;
            }
        }
    }
    
    // Сохранение данных в БД
    if (!empty($title) && !empty($short_desc)) {
        if ($id > 0) {
            // Редактирование существующей новости
            $stmt = $pdo->prepare("UPDATE news SET image = ?, title = ?, short_desc = ?, full_desc = ?, html_code = ?, date = ? WHERE id = ?");
            $stmt->execute([$image_path, $title, $short_desc, $full_desc, $html_code, $date, $id]);
            $_SESSION['message'] = 'Новость успешно обновлена!';
        } else {
            // Добавление новой новости
            $stmt = $pdo->prepare("INSERT INTO news (image, title, short_desc, full_desc, html_code, date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$image_path, $title, $short_desc, $full_desc, $html_code, $date]);
            $_SESSION['message'] = 'Новость успешно добавлена!';
        }
        
        header('Location: news.php?page=news');
        exit;
    } else {
        $error = 'Пожалуйста, заполните обязательные поля (название и краткое описание)';
    }
}

// Получение данных для редактирования
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    
    if (!$news) {
        $_SESSION['error'] = 'Новость не найдена!';
        header('Location: news.php?page=news');
        exit;
    }
}
?>

<div class="admin-news">
    <a href="../admin.php" class="back-link">← Назад в админ-панель</a>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if ($action === 'list' || $action === 'delete'): ?>
        <div class="news-header">
            <h2>Управление новостями</h2>
            <a href="news.php?page=news&action=add" class="btn-custom">Добавить новость</a>
        </div>
        
        <?php
        // Получение списка новостей
        $stmt = $pdo->query("SELECT * FROM news ORDER BY date DESC, id DESC");
        $newsList = $stmt->fetchAll();
        ?>
        
        <?php if (count($newsList) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Изображение</th>
                            <th>Название</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newsList as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="img-thumbnail">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= date('d.m.Y', strtotime($item['date'])) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="news.php?page=news&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>
                                        <a href="news.php?page=news&action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту новость?')">Удалить</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Новостей пока нет</div>
        <?php endif; ?>
    
    <?php else: ?>
        <h2><?= ($action === 'add') ? 'Добавить новость' : 'Редактировать новость' ?></h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $news['id'] ?? 0 ?>">
            
            <?php if (isset($news['image']) && !empty($news['image'])): ?>
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($news['image']) ?>">
                <div class="form-group mb-3">
                    <label>Текущее изображение:</label>
                    <img src="../<?= htmlspecialchars($news['image']) ?>" alt="Current image" class="img-thumbnail" style="max-width: 200px;">
                </div>
            <?php endif; ?>
            
            <div class="form-group mb-3">
                <label for="image">Изображение:</label>
                <input type="file" id="image" name="image" class="form-control">
                <small class="form-text text-muted">Оставьте пустым, чтобы оставить текущее изображение</small>
            </div>
            
            <div class="form-group mb-3">
                <label for="title">Название *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($news['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-group mb-3">
                <label for="short_desc">Краткое описание *</label>
                <textarea id="short_desc" name="short_desc" class="form-control" rows="3" required><?= 
                    htmlspecialchars($news['short_desc'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label for="full_desc">Полное описание</label>
                <textarea id="full_desc" name="full_desc" class="form-control" rows="5"><?= 
                    htmlspecialchars($news['full_desc'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label for="html_code">HTML код для вставки</label>
                <textarea id="html_code" name="html_code" class="form-control" rows="5"><?= 
                    htmlspecialchars($news['html_code'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label for="date">Дата публикации</label>
                <input type="date" id="date" name="date" class="form-control" 
                       value="<?= htmlspecialchars($news['date'] ?? date('Y-m-d')) ?>">
            </div>
            
            <div class="form-group d-flex gap-2">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="news.php?page=news" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
    html{
        background-color: #f7eabd;
    }

    .btn-custom {
    background-color: #c0875c;
    /* Основной цвет */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    color: #ffffff;
    /* Цвет текста (темный для контраста) */
    padding: 1%;
    border-radius: 10px;
    }

    .btn-custom:hover {
        background-color: #702f27;
        /* Цвет при наведении (немного темнее) */
        border-color: #c0875c;
        /* Цвет границы при наведении */
        color: #e0d4a8;
        /* Цвет текста при наведении */
    }
    /* Общие стили из админ-панели */
    .admin-news {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-top: 2rem;
        margin-bottom: 3rem;
        margin-left: 12%;
        margin-right: 12%;
    }
    
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
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-info {
        background-color: #e7f5fe;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }
    
    h2 {
        color: var(--dark);
        margin-bottom: 1.5rem;
        font-size: 1.75rem;
        border-bottom: 2px solid var(--accent);
        padding-bottom: 0.5rem;
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
    
    /* Стили для таблицы новостей */
    .news-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
    }
    
    .table th {
        background-color: var(--accent);
        color: white;
        padding: 1rem;
        text-align: left;
    }
    
    .table td {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .table tr:hover {
        background-color: #f9f9f9;
    }
    
    .table img {
        max-width: 100px;
        border-radius: 4px;
    }
    
    /* Стили для формы */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--dark);
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
    }
    
    .form-control:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(192, 135, 92, 0.2);
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    .img-thumbnail {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 0.25rem;
        background-color: white;
    }
    
    /* Кнопки (без анимации) */
    .btn {
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }
    
    .btn-primary {
        background-color: var(--accent);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #a57352;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    
    .btn-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .btn-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .admin-news {
            padding: 1rem;
        }
        
        .news-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .table th, .table td {
            padding: 0.75rem 0.5rem;
        }
        
        .table img {
            max-width: 60px;
        }
    }
</style>