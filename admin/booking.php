<?php
session_start();
require_once '../components/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Получение всех бронирований
$query = "SELECT r.*, u.full_name as user_name, u.email as user_email 
          FROM reservations r 
          LEFT JOIN users u ON r.user_id = u.id 
          ORDER BY r.date, r.time";
$reservations = $pdo->query($query)->fetchAll();

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'confirm') {
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($action === 'cancel') {
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header("Location: booking.php");
        exit;
    } catch (PDOException $e) {
        $error = "Ошибка при обновлении статуса: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Администрирование бронирований</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --dark: #24211C;
            --accent: #c0875c;
            --light: #f7eabd;
        }
        
        .admin-reservations {
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
        .reservations-table-container {
            margin-top: 2rem;
            overflow-x: auto;
        }
        
        .reservations-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .reservations-table th {
            background-color: var(--accent);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        
        .reservations-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .reservations-table tr:hover {
            background-color: #f9f9f9;
        }
        
        /* Статусы бронирования */
        .status-pending {
            color: #fd7e14;
            font-weight: 600;
        }
        
        .status-confirmed {
            color: #198754;
            font-weight: 600;
        }
        
        .status-cancelled {
            color: #dc3545;
            font-weight: 600;
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
        
        .btn-success {
            background-color: #198754;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #157347;
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
        
        /* Формы */
        .reservation-form {
            display: inline;
        }
        
        /* Уведомления */
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
            .admin-reservations {
                margin: 1rem;
                padding: 1rem;
            }
            
            .reservations-table th, 
            .reservations-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
            
            .btn-sm {
                padding: 0.2rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    
    <div class="admin-reservations">
        <a href="../admin.php" class="back-link">← Назад в админ-панель</a>
        <h2>Управление бронированиями</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="reservations-table-container">
            <table class="reservations-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тип</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?= $reservation['id'] ?></td>
                            <td><?= $reservation['is_hall'] ? 'Зал' : 'Столик' ?></td>
                            <td><?= htmlspecialchars($reservation['name']) ?></td>
                            <td><?= htmlspecialchars($reservation['phone']) ?></td>
                            <td><?= htmlspecialchars($reservation['email'] ?? $reservation['user_email'] ?? '') ?></td>
                            <td><?= date('d.m.Y', strtotime($reservation['date'])) ?></td>
                            <td><?= substr($reservation['time'], 0, 5) ?></td>
                            <td class="status-<?= $reservation['status'] ?>">
                                <?= $reservation['status'] === 'pending' ? 'Ожидание' : 
                                    ($reservation['status'] === 'confirmed' ? 'Подтверждено' : 'Отменено') ?>
                            </td>
                            <td>
                                <?php if ($reservation['status'] === 'pending'): ?>
                                    <form method="post" class="reservation-form">
                                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn btn-success btn-sm">Подтвердить</button>
                                    </form>
                                    <form method="post" class="reservation-form">
                                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-danger btn-sm">Отменить</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>