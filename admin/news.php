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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Управление новостями</h2>
            <a href="news.php?page=news&action=add" class="btn btn-primary">Добавить новость</a>
        </div>
        
        <?php
        // Получение списка новостей
        $stmt = $pdo->query("SELECT * FROM news ORDER BY date DESC, id DESC");
        $newsList = $stmt->fetchAll();
        ?>
        
        <?php if (count($newsList) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
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
                                        <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="max-width: 100px;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= date('d.m.Y', strtotime($item['date'])) ?></td>
                                <td>
                                    <a href="news.php?page=news&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>
                                    <a href="news.php?page=news&action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту новость?')">Удалить</a>
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
                <div class="mb-3">
                    <label>Текущее изображение:</label>
                    <img src="../<?= htmlspecialchars($news['image']) ?>" alt="Current image" class="img-thumbnail" style="max-width: 200px; display: block;">
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
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="news.php?page=news" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    <?php endif; ?>
</div>