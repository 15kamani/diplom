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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --dark: #24211C;
            --accent: #c0875c;
            --light: #f7eabd;
        }
        
        .admin-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 95%;
        }
        
        h2 {
            color: var(--dark);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table th {
            background-color: var(--accent);
            color: white;
            vertical-align: middle;
        }
        
        .status-badge {
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
        
        .action-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .cancel-reason {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .modal-cancel textarea {
            min-height: 100px;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        
        .pagination .page-link {
            color: var(--accent);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
                margin: 1rem;
            }
            
            .action-buttons .btn {
                display: block;
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="admin-container">
            <a href="../admin.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Назад в админ-панель
            </a>
            <h2><i class="bi bi-calendar-check"></i> Управление бронированиями</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flashMessage['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
                                <td colspan="8" class="text-center py-4">Нет бронирований</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?= $reservation['id'] ?></td>
                                    <td>
                                        <?php if ($reservation['reservation_type'] === 'hall'): ?>
                                            <span class="badge bg-warning text-dark">Зал</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Столик <?= $reservation['table_number'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($reservation['name']) ?>
                                        <?php if ($reservation['user_id']): ?>
                                            <br><small class="text-muted">Аккаунт: <?= htmlspecialchars($reservation['user_name'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($reservation['phone']) ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($reservation['email'] ?? $reservation['user_email'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?= date('d.m.Y', strtotime($reservation['date'])) ?>
                                        <br>
                                        <small><?= substr($reservation['time'], 0, 5) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($reservation['reservation_type'] === 'hall'): ?>
                                            Мероприятие: <?= htmlspecialchars($reservation['event_type']) ?>
                                            <br>Гостей: <?= $reservation['guests'] ?>
                                        <?php else: ?>
                                            Гостей: <?= $reservation['guests'] ?>
                                        <?php endif; ?>
                                        <?php if (!empty($reservation['comments'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($reservation['comments']) ?></small>
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
                                            <div class="cancel-reason">
                                                <small><strong>Причина:</strong> <?= htmlspecialchars($reservation['cancel_reason']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <?php if ($reservation['status'] === 'new' || $reservation['status'] === 'pending'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                                <input type="hidden" name="action" value="confirm">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check-circle"></i> Подтвердить
                                                </button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal" data-id="<?= $reservation['id'] ?>">
                                                <i class="bi bi-x-circle"></i> Отменить
                                            </button>
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
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">Назад</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">Вперед</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Модальное окно отмены бронирования -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Отмена бронирования</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" class="modal-cancel">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="cancelReservationId">
                        <input type="hidden" name="action" value="cancel">
                        
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Укажите причину отмены:</label>
                            <textarea class="form-control" id="cancelReason" name="reason" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-danger">Подтвердить отмену</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Инициализация модального окна отмены
        var cancelModal = document.getElementById('cancelModal');
        if (cancelModal) {
            cancelModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var reservationId = button.getAttribute('data-id');
                var modalInput = cancelModal.querySelector('#cancelReservationId');
                modalInput.value = reservationId;
            });
        }
    </script>
</body>
</html>