<?php
// Проверка авторизации и прав администратора
session_start();
require '../components/db_connect.php';


// Обработка удаления отзыва
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reviewId = $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        
        $_SESSION['message'] = 'Отзыв успешно удален';
        header('Location: review.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка при удалении отзыва: ' . $e->getMessage();
    }
}

// Получаем все отзывы с информацией о пользователях
$stmt = $pdo->query("
    SELECT r.*, u.full_name, u.avatar_path 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png" type="image/x-icon">
    <title>Управление отзывами</title>
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
        
        .admin-reviews {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem 12% 3rem;
        }
        
        h2 {
            color: var(--dark);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
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
        
        /* Таблица */
        .table-responsive {
            margin-top: 2rem;
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
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
        
        /* Аватар */
        .avatar-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #eee;
        }
        
        /* Рейтинг */
        .rating {
            color: #ffc107;
            font-size: 1.2em;
            letter-spacing: 2px;
        }
        
        /* Кнопки */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
        }
        
        .btn-sm {
            font-size: 0.875rem;
            padding: 0.25rem 0.75rem;
        }
        
        /* Уведомления */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-info {
            background-color: #e7f5fe;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .admin-reviews {
                margin: 1rem;
                padding: 1rem;
            }
            
            .table th, .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
            
            .avatar-img {
                width: 40px;
                height: 40px;
            }
            
            .rating {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="admin-reviews">
        <a href="../admin.php" class="back-link">← Назад в админ-панель</a>
        <h2>Управление отзывами</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <?php if (count($reviews) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Фото</th>
                            <th>Пользователь</th>
                            <th>Отзыв</th>
                            <th>Рейтинг</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?= $review['id']; ?></td>
                                <td>
                                    <img src="<?= !empty($review['avatar_path']) ? '../' . htmlspecialchars($review['avatar_path']) : '../img/default-avatar.png'; ?>" 
                                         alt="Аватар" class="avatar-img">
                                </td>
                                <td><?= htmlspecialchars($review['full_name']); ?></td>
                                <td><?= nl2br(htmlspecialchars($review['review_text'])); ?></td>
                                <td>
                                    <span class="rating">
                                        <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <a href="review.php?delete=<?= $review['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?');">
                                        Удалить
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Нет отзывов для отображения</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>