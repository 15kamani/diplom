<?php
session_start();
require_once '../components/db_connect.php';

// Получение всех бронирований с пагинацией
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT SQL_CALC_FOUND_ROWS r.*, u.full_name as user_name, u.email as user_email 
          FROM reservations r 
          LEFT JOIN users u ON r.user_id = u.id 
          ORDER BY r.date DESC, r.time DESC
          LIMIT $limit OFFSET $offset";
$reservations = $pdo->query($query)->fetchAll();

// Получаем общее количество записей для пагинации
$total = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages = ceil($total / $limit);

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : null;
        
        try {
            if ($action === 'confirm') {
                $stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
                $stmt->execute([$id]);
            } elseif ($action === 'cancel') {
                if (empty($reason)) {
                    throw new Exception("Необходимо указать причину отмены");
                }
                $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled', cancel_reason = ? WHERE id = ?");
                $stmt->execute([$reason, $id]);
            }
            
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Статус бронирования успешно обновлен'
            ];
            header("Location: booking.php");
            exit;
        } catch (Exception $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

// Обработка сообщений
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Администрирование бронирований</title>
    <link rel="icon" href="../img/favicon.png" type="image/x-icon">
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
        
        /* Таблица */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 1.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--accent);
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: rgba(192, 135, 92, 0.05);
        }
        
        /* Бейджи статусов */
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-new {
            background-color: #0dcaf0;
            color: white;
        }
        
        .status-pending {
            background-color: #fd7e14;
            color: white;
        }
        
        .status-confirmed {
            background-color: #198754;
            color: white;
        }
        
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        
        /* Бейджи типов */
        .type-badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .type-hall {
            background-color: #ffc107;
            color: var(--dark);
        }
        
        .type-table {
            background-color: #0dcaf0;
            color: white;
            text-align: center;
        }
        
        /* Кнопки */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .btn-confirm {
            background-color: #198754;
            color: white;
        }
        
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        /* Форма отмены */
        .cancel-form {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .cancel-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            min-height: 100px;
            margin-bottom: 1rem;
            resize: vertical;
        }
        
        /* Сообщения */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border-left-color: #198754;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        /* Пагинация */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }
        
        .page-item {
            list-style: none;
        }
        
        .page-link {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        
        .page-item.active .page-link {
            background-color: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .admin-gallery {
                margin: 1rem;
                padding: 1rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-gallery">
        <a href="../admin.php" class="back-link">← Назад в админ-панель</a>
        <h1>Управление бронированиями</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?>">
                <?= htmlspecialchars($flashMessage['message']) ?>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тип</th>
                        <th>Клиент</th>
                        <th>Контакты</th>
                        <th>Дата/Время</th>
                        <th>Детали</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">Нет бронирований</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?= $reservation['id'] ?></td>
                                <td>
                                    <?php if ($reservation['reservation_type'] === 'hall'): ?>
                                        <span class="type-badge type-hall">Зал</span>
                                    <?php else: ?>
                                        <span class="type-badge type-table">Столик <?= $reservation['table_number'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($reservation['name']) ?>
                                    <?php if ($reservation['user_id']): ?>
                                        <br><small style="color: #6c757d;">Аккаунт: <?= htmlspecialchars($reservation['user_name'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($reservation['phone']) ?>
                                    <br>
                                    <small style="color: #6c757d;"><?= htmlspecialchars($reservation['email'] ?? $reservation['user_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= date('d.m.Y', strtotime($reservation['date'])) ?>
                                    <br>
                                    <small style="color: #6c757d;"><?= substr($reservation['time'], 0, 5) ?></small>
                                </td>
                                <td>
                                    <?php if ($reservation['reservation_type'] === 'hall'): ?>
                                        Мероприятие: <?= htmlspecialchars($reservation['event_type']) ?>
                                        <br>Гостей: <?= $reservation['guests'] ?>
                                    <?php else: ?>
                                        Гостей: <?= $reservation['guests'] ?>
                                    <?php endif; ?>
                                    <?php if (!empty($reservation['comments'])): ?>
                                        <br><small style="color: #6c757d;"><?= htmlspecialchars($reservation['comments']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'status-' . $reservation['status'];
                                    $statusText = [
                                        'new' => 'Новый',
                                        'pending' => 'Ожидает',
                                        'confirmed' => 'Подтверждено',
                                        'cancelled' => 'Отменено'
                                    ][$reservation['status']];
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= $statusText ?>
                                    </span>
                                    <?php if ($reservation['status'] === 'cancelled' && !empty($reservation['cancel_reason'])): ?>
                                        <div style="margin-top: 0.5rem;">
                                            <small style="color: #6c757d;"><strong>Причина:</strong> <?= htmlspecialchars($reservation['cancel_reason']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reservation['status'] === 'new' || $reservation['status'] === 'pending'): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="btn btn-confirm">Подтвердить</button>
                                        </form>
                                        
                                        <button class="btn btn-cancel" onclick="showCancelForm(<?= $reservation['id'] ?>)">Отменить</button>
                                        
                                        <form method="post" id="cancelForm<?= $reservation['id'] ?>" class="cancel-form">
                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <textarea name="reason" placeholder="Укажите причину отмены..." required></textarea>
                                            <button type="submit" class="btn btn-cancel">Подтвердить отмену</button>
                                            <button type="button" class="btn" onclick="hideCancelForm(<?= $reservation['id'] ?>)" style="background-color: #6c757d; color: white;">Отмена</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <div class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Назад</a>
                    </div>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <div class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </div>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <div class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Вперед</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showCancelForm(id) {
            document.getElementById('cancelForm' + id).style.display = 'block';
        }
        
        function hideCancelForm(id) {
            document.getElementById('cancelForm' + id).style.display = 'none';
        }
    </script>
</body>
</html>