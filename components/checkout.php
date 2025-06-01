<?php
session_start();
require_once '../components/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем данные пользователя
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

// Получаем товары в корзине
$cartStmt = $pdo->prepare("
    SELECT c.*, m.title, m.image 
    FROM cart c
    JOIN menu_items m ON c.menu_item_id = m.id
    WHERE c.user_id = ?
");
$cartStmt->execute([$_SESSION['user_id']]);
$cartItems = $cartStmt->fetchAll();

// Рассчитываем общую сумму
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Обработка формы оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    
    try {
        $pdo->beginTransaction();
        
        // Создаем заказ
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, customer_notes)
            VALUES (?, ?, ?)
        ");
        $orderStmt->execute([$_SESSION['user_id'], $total, $notes]);
        $orderId = $pdo->lastInsertId();
        
        // Добавляем товары в заказ
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, menu_item_id, variant_name, price, quantity)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($cartItems as $item) {
            $itemStmt->execute([
                $orderId,
                $item['menu_item_id'],
                $item['variant_name'],
                $item['price'],
                $item['quantity']
            ]);
        }
        
        // Очищаем корзину
        $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        
        header("Location: order_success.php?id=$orderId");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Ошибка при оформлении заказа: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Заголовок и стили -->
</head>
<body>
    <?php include '../components-page/header.php'; ?>
    
    <main class="container mt-4">
        <h1>Оформление заказа</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Ваши товары</h3>
                    </div>
                    <div class="card-body">
                        <!-- Таблица товаров аналогичная корзине -->
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Дополнительная информация</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Комментарий к заказу</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Подтвердить заказ</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Итого</h3>
                    </div>
                    <div class="card-body">
                        <h4>Сумма: <?= number_format($total, 2) ?> руб.</h4>
                        <p>Доставка: самовывоз</p>
                        <hr>
                        <h5>Контактные данные</h5>
                        <p><?= htmlspecialchars($user['full_name']) ?></p>
                        <p><?= htmlspecialchars($user['phone']) ?></p>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../components-page/footer.php'; ?>
</body>
</html>