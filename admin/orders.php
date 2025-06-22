<?php
session_start();
require_once '../components/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    if ($orderId && $status) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
    }
}

// Получение списка заказов
$orders = $pdo->query("
    SELECT o.*, u.username, u.full_name, u.phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - Админ панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../img/favicon.png" type="image/x-icon">

    <style>
        .status-new { color: #0d6efd; }
        .status-processing { color: #fd7e14; }
        .status-shipped { color: #ffc107; }
        .status-completed { color: #198754; }
        .status-cancelled { color: #dc3545; }
        .delivery-type { font-weight: bold; }
        .delivery-pickup { color: #0d6efd; }
        .delivery-delivery { color: #20c997; }
    </style>
</head>
<body>
    
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
    
    .admin-orders-container {
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
    .orders-table-container {
        margin-top: 2rem;
        overflow-x: auto;
    }
    
    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .orders-table th {
        background-color: var(--accent);
        color: white;
        padding: 1rem;
        text-align: left;
    }
    
    .orders-table td {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .orders-table tr:hover {
        background-color: #f9f9f9;
    }
    
    /* Способы доставки */
    .delivery-type {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
    }
    
    .delivery-pickup {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    
    .delivery-delivery {
        background-color: rgba(32, 201, 151, 0.1);
        color: #20c997;
    }
    
    /* Селектор статуса */
    .status-select {
        padding: 0.5rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .status-select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(192, 135, 92, 0.2);
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
    
    .btn-info {
        background-color: #0dcaf0;
        color: white;
    }
    
    .btn-info:hover {
        background-color: #0da0c0;
    }
    
    .btn-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.75rem;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .admin-orders-container {
            margin: 1rem;
            padding: 1rem;
        }
        
        .orders-table th, 
        .orders-table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .delivery-type {
            font-size: 0.75rem;
        }
        
        .status-select {
            font-size: 0.75rem;
        }
    }
    /* Адаптивность */
    @media (max-width: 768px) {
        .admin-orders-container {
            margin: 1rem;
            padding: 1rem;
        }
        
        .orders-table th, 
        .orders-table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .delivery-type {
            font-size: 0.75rem;
        }
        
        .status-select {
            font-size: 0.75rem;
        }
    }
</style>

    <main class="admin-orders-container">
        <a href="../admin.php" class="back-link">← Назад в админ-панель</a>
        <h1>Управление заказами</h1>
        
        <div class="orders-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Способ</th>
                        <th>Время</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= htmlspecialchars($order['full_name']) ?></td>
                            <td><?= htmlspecialchars($order['phone']) ?></td>
                            <td>
                                <span class="delivery-type delivery-<?= $order['delivery_type'] ?>">
                                    <?= $order['delivery_type'] === 'delivery' ? 'Доставка' : 'Самовывоз' ?>
                                </span>
                                <?php if ($order['delivery_type'] === 'delivery' && !empty($order['delivery_address'])): ?>
                                    <br><small><?= htmlspecialchars(mb_strimwidth($order['delivery_address'], 0, 20, '...')) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= date('H:i', strtotime($order['delivery_time'])) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> руб.</td>
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>>Новый</option>
                                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Отправлен</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Завершен</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>
                                <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-info btn-sm">Подробнее</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>