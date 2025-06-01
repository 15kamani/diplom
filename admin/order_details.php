<?php
session_start();
require_once '../components/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Получение ID заказа из параметра URL
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    header("Location: orders.php");
    exit;
}

// Получение информации о заказе
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.full_name AS full_name, u.phone AS phone, u.email AS email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Заказ не найден");
    }
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $reason = filter_input(INPUT_POST, 'cancellation_reason', FILTER_SANITIZE_STRING);
    
    try {
        $pdo->beginTransaction();
        
        // Обновляем статус и причину отмены
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?, 
                cancellation_reason = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $status,
            $status === 'cancelled' ? $reason : null, // Сохраняем причину только для отмененных
            $orderId
        ]);
        
        $pdo->commit();
        
        // Обновляем данные заказа
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Здесь можно добавить отправку уведомления пользователю
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Ошибка при обновлении статуса: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали заказа #<?= $order['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    
    .admin-order-details {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin: 2rem 12% 3rem;
    }
    
    h1 {
        color: var(--dark);
        border-bottom: 2px solid var(--accent);
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    h3 {
        color: var(--dark);
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
    
    /* Карточки с информацией */
    .order-info-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    
    .order-info-card .card-header {
        background-color: var(--accent);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 1rem;
    }
    
    .order-info-card .card-body {
        padding: 1.5rem;
    }
    
    /* Таблица товаров */
    .table-responsive {
        overflow-x: auto;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th {
        background-color: #f8f9fa;
        color: var(--dark);
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
    
    /* Изображения товаров */
    .order-item-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    /* Форма статуса */
    .status-form .form-label {
        font-weight: 600;
        color: var(--dark);
    }
    
    .status-select {
        padding: 0.5rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        width: 100%;
    }
    
    .status-select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(192, 135, 92, 0.2);
    }
    
    /* Кнопки */
    .btn {
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: background-color 0.2s;
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
    
    /* Итого */
    .order-total {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark);
    }
    
    /* Поля формы */
    .form-control {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        width: 100%;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(192, 135, 92, 0.2);
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .admin-order-details {
            margin: 1rem;
            padding: 1rem;
        }
        
        .table th, .table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .order-item-image {
            width: 40px;
            height: 40px;
        }
    }
    </style>
</head>
<body>
    
    <main class="admin-order-details">
        <h1>Детали заказа #<?= $order['id'] ?></h1>
        
        <!-- Информация о клиенте -->
        <div class="order-info-card">
            <div class="card-header">
                <h3>Информация о клиенте</h3>
            </div>
            <div class="card-body">
                <p><strong>Имя:</strong> 
                    <?= isset($order['full_name']) ? htmlspecialchars($order['full_name']) : 'Не указано' ?>
                </p>
                <p><strong>Телефон:</strong> 
                    <?= isset($order['phone']) ? htmlspecialchars($order['phone']) : 'Не указан' ?>
                </p>
                <p><strong>Email:</strong> 
                    <?= isset($order['email']) ? htmlspecialchars($order['email']) : 'Не указан' ?>
                </p>
                <?php if (isset($order['username'])): ?>
                    <p><strong>Логин:</strong> <?= htmlspecialchars($order['username']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Информация о доставке -->
        <div class="order-info-card">
            <div class="card-header">
                <h3>Информация о доставке</h3>
            </div>
            <div class="card-body">
                <p><strong>Способ получения:</strong> 
                    <?= isset($order['delivery_type']) && $order['delivery_type'] === 'delivery' ? 'Доставка' : 'Самовывоз' ?>
                </p>
                
                <?php if (isset($order['delivery_type']) && $order['delivery_type'] === 'delivery' && !empty($order['delivery_address'])): ?>
                    <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($order['delivery_address']) ?></p>
                <?php endif; ?>
                
                <p><strong>Желаемое время:</strong> 
                    <?= isset($order['delivery_time']) ? date('H:i', strtotime($order['delivery_time'])) : 'Не указано' ?>
                </p>
                
                <?php if (!empty($order['customer_notes'])): ?>
                    <p><strong>Комментарий:</strong> <?= htmlspecialchars($order['customer_notes']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Товары в заказе -->
        <div class="order-info-card">
            <div class="card-header">
                <h3>Состав заказа</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Вариант</th>
                                <th>Цена</th>
                                <th>Кол-во</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Инициализируем переменную как пустой массив, если она не определена
                            $items = $items ?? [];
                            
                            foreach ($items as $item): 
                                $itemSum = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="../<?= htmlspecialchars($item['image']) ?>" class="order-item-image">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($item['title']) ?>
                                    </td>
                                    <td><?= !empty($item['variant_name']) ? htmlspecialchars($item['variant_name']) : '-' ?></td>
                                    <td><?= number_format($item['price'], 2) ?> руб.</td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= number_format($itemSum, 2) ?> руб.</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <h4 class="order-total">Итого: <?= number_format($order['total_amount'], 2) ?> руб.</h4>
                </div>
            </div>
        </div>
        
        <!-- Форма изменения статуса -->
        <div class="order-info-card">
            <div class="card-header">
                <h3>Управление заказом</h3>
            </div>
            <div class="card-body">
                <form method="POST" id="statusForm" class="status-form">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-4">
                            <label class="form-label"><strong>Статус заказа:</strong></label>
                        </div>
                        <div class="col-md-6">
                            <select name="status" id="statusSelect" class="status-select">
                                <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>>Новый</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Отправлен</option>
                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Завершен</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="cancellationReasonField" style="display: none;" class="mb-3">
                        <label for="cancellation_reason" class="form-label"><strong>Причина отмены:</strong></label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" 
                                rows="3" placeholder="Укажите причину отмены заказа"><?= 
                                htmlspecialchars($order['cancellation_reason'] ?? '') ?></textarea>
                        <small class="text-muted">Этот комментарий увидит пользователь</small>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" name="update_status" class="btn btn-primary">Обновить статус</button>
                    </div>
                </form>
            </div>
        </div>
        
        <a href="orders.php" class="btn btn-secondary">Вернуться к списку заказов</a>
    </main>
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('statusSelect');
            const reasonField = document.getElementById('cancellationReasonField');
            
            // Показываем/скрываем поле причины в зависимости от выбранного статуса
            function toggleReasonField() {
                reasonField.style.display = statusSelect.value === 'cancelled' ? 'block' : 'none';
            }
            
            // Инициализация при загрузке
            toggleReasonField();
            
            // Обработчик изменения статуса
            statusSelect.addEventListener('change', toggleReasonField);
            
            // Валидация формы
            document.getElementById('statusForm').addEventListener('submit', function(e) {
                if (statusSelect.value === 'cancelled' && 
                    !document.getElementById('cancellation_reason').value.trim()) {
                    e.preventDefault();
                    alert('Пожалуйста, укажите причину отмены заказа');
                }
            });
        });
    </script>
</body>
</html>