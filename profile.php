<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Подключение к базе данных
require_once 'components/db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Пользователь не найден");
    }

    $avatar_path = (isset($user['avatar_path']) && file_exists($user['avatar_path'])) 
        ? $user['avatar_path'] 
        : 'img/icon/default_avatar.png';

} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}

// Получение товаров в корзине пользователя
$cartItems = [];
$cartTotal = 0;
try {
    $stmt = $pdo->prepare("
        SELECT c.*, m.title, m.image 
        FROM cart c
        JOIN menu_items m ON c.menu_item_id = m.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
    
    foreach ($cartItems as $item) {
        $cartTotal += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    $cartError = "Ошибка при загрузке корзины";
}

// Получение заказов пользователя
$userOrders = [];
try {
    $stmt = $pdo->prepare("
        SELECT id, order_date, total_amount, status 
        FROM orders 
        WHERE user_id = ?
        ORDER BY order_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $userOrders = $stmt->fetchAll();
} catch (PDOException $e) {
    $ordersError = "Ошибка при загрузке заказов";
}

// Обработка оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    try {
        $deliveryType = $_POST['delivery_type'] ?? 'pickup';
        $deliveryAddress = $_POST['delivery_address'] ?? null;
        $deliveryTime = $_POST['delivery_time'];
        $notes = $_POST['customer_notes'] ?? null;
        
        // Формируем дату и время доставки
        $deliveryDateTime = date('Y-m-d') . ' ' . $deliveryTime . ':00';
        
        $pdo->beginTransaction();
        
        // Создаем заказ
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, status, delivery_type, delivery_address, delivery_time, customer_notes)
            VALUES (?, ?, 'new', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $cartTotal,
            $deliveryType,
            $deliveryAddress,
            $deliveryDateTime,
            $notes
        ]);
        $orderId = $pdo->lastInsertId();
        
        // Переносим товары в заказ и очищаем корзину (как в предыдущем коде)
        // ...
        
        $pdo->commit();
        
        header("Location: profile.php?order_success=" . $orderId);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $orderError = "Ошибка при оформлении заказа: " . $e->getMessage();
    }
}


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получение данных пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Получение бронирований пользователя
$query = "SELECT * FROM reservations WHERE user_id = ? ORDER BY date DESC, time DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/media.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond&family=EB+Garamond&display=swap" rel="stylesheet">
    <link rel="icon" href="img/favicon.png" type="image/x-icon">
    <title>Кофе с СоВой</title>
    <style>
        .avatar-upload-container { display: none; }
        .avatar-image { max-width: 150px; max-height: 150px; border-radius: 50%; }
        .order-card { border-left: 4px solid; margin-bottom: 15px; }
        .order-new { border-left-color: #0d6efd; }
        .order-processing { border-left-color: #fd7e14; }
        .order-shipped { border-left-color: #ffc107; }
        .order-completed { border-left-color: #198754; }
        .order-cancelled { border-left-color: #dc3545; }
        .order-status { font-weight: bold; }
        .cart-item-image { max-width: 50px; max-height: 50px; object-fit: cover; }
        .avatar-upload-container {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            display: none;
        }
        .cart-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .table th {
            background-color: #8b5e3c;
            color: white;
        }
        .quantity-input {
            width: 70px;
        }
        .for-otziv {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body class="container-0">
<?php include 'components/header.php'; ?>

<main class="gallery">
    <div class="profile">
        <div class="kroshka">
            <p><a href="index.php">Главная</a> > <a href="#">Профиль</a></p>
        </div>
        
        <?php if (isset($_GET['order_success'])): ?>
            <div class="alert alert-success">
                Заказ #<?= htmlspecialchars($_GET['order_success']) ?> успешно оформлен!
            </div>
        <?php endif; ?>
        
        <?php if (isset($orderError)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($orderError) ?></div>
        <?php endif; ?>

        <h1>Профиль</h1>
        
        <!-- Информация о пользователе -->
        <div class="profile-card card">
            <div class="user-card">
                <div class="user-card-img">
                    <img src="<?= htmlspecialchars($avatar_path) ?>" alt="Аватар" class="avatar-image">
                    <button type="button" class="btn btn-secondary btn-toggle-upload mt-2">Обновить аватар</button>
                    
                    <div class="avatar-upload-container">
                        <form id="avatarForm" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <input type="file" class="form-control" id="avatarInput" name="avatar" accept="image/*" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Загрузить</button>
                            <button type="button" class="btn btn-outline-secondary cancel-upload ms-2">Отмена</button>
                        </form>
                    </div>
                    
                    <p class="text-custom-1 mt-2"><?= htmlspecialchars($user['username']) ?></p>
                </div>
                <div class="user-card-info">
                    <p><span class="garmond-1">ФИО: </span><?= htmlspecialchars($user['full_name']) ?></p>
                    <p><span class="garmond-1">Телефон: </span><?= htmlspecialchars($user['phone']) ?></p>
                    <p><span class="garmond-1">Почта: </span><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>
            <div class="btn-logout mt-3">
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </div>
        </div>

        <!-- Корзина -->
        <div class="cart-section mt-5" id="cart">
            <h2>Ваша корзина</h2>
            
            <?php if (isset($cartError)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($cartError) ?></div>
            <?php elseif (empty($cartItems)): ?>
                <p>Ваша корзина пуста</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Вариант</th>
                                <th>Цена</th>
                                <th>Кол-во</th>
                                <th>Сумма</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['image']): ?>
                                            <img src="../<?= htmlspecialchars($item['image']) ?>" class="cart-item-image me-2">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($item['title']) ?>
                                    </td>
                                    <td><?= $item['variant_name'] ? htmlspecialchars($item['variant_name']) : '-' ?></td>
                                    <td><?= htmlspecialchars($item['price']) ?> руб.</td>
                                    <td>
                                        <input type="number" 
                                               class="form-control quantity-input" 
                                               value="<?= htmlspecialchars($item['quantity']) ?>" 
                                               min="1" max="100"
                                               data-cart-id="<?= $item['id'] ?>"
                                               data-old-value="<?= htmlspecialchars($item['quantity']) ?>">
                                    </td>
                                    <td><?= $item['price'] * $item['quantity'] ?> руб.</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger remove-from-cart" 
                                                data-cart-id="<?= $item['id'] ?>">
                                            Удалить
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-end mt-3">
                    <h4>Итого: <?= $cartTotal ?> руб.</h4>
                    <form method="POST">
                        <div class="text-end mt-3">
                            <h4>Итого: <?= $cartTotal ?> руб.</h4>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                Оформить заказ
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
<!-- Модальное окно оформления заказа -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">Оформление заказа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="orderForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Способ получения</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="delivery_type" id="pickup" value="pickup" checked>
                            <label class="form-check-label" for="pickup">Самовывоз</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="delivery_type" id="delivery" value="delivery">
                            <label class="form-check-label" for="delivery">Доставка</label>
                        </div>
                    </div>
                    
                    <div id="deliveryFields" style="display: none;">
                        <div class="mb-3">
                            <label for="delivery_address" class="form-label">Адрес доставки</label>
                            <input type="text" class="form-control" id="delivery_address" name="delivery_address">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="delivery_time" class="form-label">Желаемое время</label>
                        <select class="form-select" id="delivery_time" name="delivery_time" required>
                            <option value="" disabled selected>Выберите время</option>
                            <?php
                            // Генерация вариантов времени
                            $start = strtotime('10:00');
                            $end = strtotime('20:00');
                            $interval = 30 * 60; // 30 минут в секундах
                            
                            for ($i = $start; $i <= $end; $i += $interval) {
                                $time = date('H:i', $i);
                                echo "<option value=\"$time\">$time</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_notes" class="form-label">Комментарий к заказу</label>
                        <textarea class="form-control" id="customer_notes" name="customer_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" name="create_order" class="btn btn-primary">Подтвердить заказ</button>
                </div>
            </form>
        </div>
    </div>
</div>
        <!-- История заказов -->
        <div class="orders-section mt-5">
            <h2>Ваши заказы</h2>
            
            <?php if (isset($ordersError)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($ordersError) ?></div>
            <?php elseif (empty($userOrders)): ?>
                <p>У вас пока нет заказов</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($userOrders as $order): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card order-card order-<?= htmlspecialchars($order['status']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title">Заказ #<?= htmlspecialchars($order['id']) ?></h5>
                                    <p class="card-text">
                                        <strong>Дата заказа:</strong> <?= date('d.m.Y H:i', strtotime($order['order_date'])) ?><br>
                                        <strong>Сумма:</strong> <?= htmlspecialchars($order['total_amount']) ?> руб.<br>
                                        <strong>Способ получения:</strong> 
                                        <?= isset($order['delivery_type']) && $order['delivery_type'] === 'delivery' ? 'Доставка' : 'Самовывоз' ?><br>
                                        
                                        <?php if (isset($order['delivery_type']) && $order['delivery_type'] === 'delivery' && !empty($order['delivery_address'])): ?>
                                            <strong>Адрес доставки:</strong> <?= htmlspecialchars($order['delivery_address']) ?><br>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($order['delivery_time'])): ?>
                                            <strong>Время получения:</strong> <?= date('H:i', strtotime($order['delivery_time'])) ?><br>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($order['customer_notes'])): ?>
                                            <strong>Комментарий:</strong> <?= htmlspecialchars($order['customer_notes']) ?><br>
                                        <?php endif; ?>
                                        
                                        <strong>Статус:</strong> 
                                        <span class="order-status text-<?= 
                                            $order['status'] === 'new' ? 'primary' : 
                                            ($order['status'] === 'processing' ? 'warning' : 
                                            ($order['status'] === 'completed' ? 'success' : 
                                            ($order['status'] === 'cancelled' ? 'danger' : 'info')))
                                        ?>">
                                            <?= $order['status'] === 'new' ? 'Новый' : 
                                            ($order['status'] === 'processing' ? 'В обработке' : 
                                            ($order['status'] === 'completed' ? 'Завершен' : 
                                            ($order['status'] === 'cancelled' ? 'Отменен' : 'Отправлен'))) ?>
                                        </span>
                                        
                                        <?php if ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])): ?>
                                            <div class="mt-2 alert alert-danger">
                                                <strong>Причина отмены:</strong> <?= htmlspecialchars($order['cancellation_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

            <div class="col-md-8">
                <h4>Мои бронирования</h4>
                
                <?php if (empty($reservations)): ?>
                    <div class="alert alert-info">У вас нет активных бронирований</div>
                <?php else: ?>
                    <?php foreach ($reservations as $reservation): ?>
                        <div class="card reservation-card status-<?= $reservation['status'] ?>">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= $reservation['is_hall'] ? 'Бронирование зала' : 'Бронирование столика' ?>
                                    <span class="badge bg-<?= $reservation['status'] === 'pending' ? 'warning' : 
                                        ($reservation['status'] === 'confirmed' ? 'success' : 'danger') ?> float-end">
                                        <?= $reservation['status'] === 'pending' ? 'Ожидание' : 
                                            ($reservation['status'] === 'confirmed' ? 'Подтверждено' : 'Отменено') ?>
                                    </span>
                                </h5>
                                <p class="card-text">
                                    <strong>Дата:</strong> <?= date('d.m.Y', strtotime($reservation['date'])) ?><br>
                                    <strong>Время:</strong> <?= substr($reservation['time'], 0, 5) ?><br>
                                    <?php if (!$reservation['is_hall']): ?>
                                        <strong>Столик:</strong> <?= ($reservation['table_number'] ?? 0) + 1 ?><br>
                                    <?php endif; ?>
                                    <strong>Гостей:</strong> <?= $reservation['guests'] ?><br>
                                    <strong>Телефон:</strong> <?= htmlspecialchars($reservation['phone']) ?>
                                </p>
                                <?php if ($reservation['status'] === 'pending'): ?>
                                    <form method="post" action="cancel_booking.php" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Отменить бронь</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <!-- Блок отзывов -->
        <div class="for-otziv card mt-4">
            <div class="card-body">
                
                <div class="review-invitation mb-4">
                    <p class="lead text-muted" style="font-size: 1.5rem; line-height: 1.6;">
                        Нам очень важно ваше мнение! Поделитесь, пожалуйста, своими впечатлениями — 
                        это поможет нам становиться лучше и мотивирует нашу команду. 
                        Спасибо, что находите время для обратной связи!
                    </p>
                </div>

                <button id="openReviewForm" class="btn btn-custom" style="font-size: 16px;">Написать отзыв</button>

                <div id="reviewFormContainer" style="display: none; margin-top: 20px;">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Оставить отзыв</h5>
                            <form id="reviewForm" method="POST">
                                <div class="mb-3">
                                    <label for="reviewText" class="form-label">Ваш отзыв</label>
                                    <textarea class="form-control" id="reviewText" name="reviewText" rows="3" required 
                                              placeholder="Напишите здесь ваши впечатления..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="reviewRating" class="form-label">Оценка (1-5)</label>
                                    <select class="form-select" id="reviewRating" name="reviewRating" required>
                                        <option value="" selected disabled>Выберите оценку</option>
                                        <option value="5">5 - Отлично</option>
                                        <option value="4">4 - Хорошо</option>
                                        <option value="3">3 - Удовлетворительно</option>
                                        <option value="2">2 - Плохо</option>
                                        <option value="1">1 - Очень плохо</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Отправить отзыв</button>
                                <button type="button" id="cancelReview" class="btn btn-outline-secondary">Отмена</button>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</main>

<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик удаления из корзины
    document.getElementById('cart').addEventListener('click', async function(e) {
        if (e.target.classList.contains('remove-from-cart')) {
            e.preventDefault();
            const cartId = e.target.dataset.cartId;
            
            try {
                const response = await fetch(`components/remove_from_cart.php?id=${cartId}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Плавное исчезновение строки
                    const row = e.target.closest('tr');
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    
                    // Обновление через 300мс
                    setTimeout(() => {
                        location.reload();
                    }, 300);
                } else {
                    alert(`Ошибка: ${result.message}`);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении товара');
            }
        }
    });

    // Обработчик изменения количества
    document.getElementById('cart').addEventListener('change', async function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const input = e.target;
            const cartId = input.dataset.cartId;
            const newQuantity = parseInt(input.value);
            const oldValue = parseInt(input.dataset.oldValue);

            // Валидация
            if (isNaN(newQuantity) || newQuantity < 1 || newQuantity > 100) {
                alert('Количество должно быть от 1 до 100');
                input.value = oldValue;
                return;
            }

            try {
                const response = await fetch('components/update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cart_id: cartId,
                        quantity: newQuantity
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Обновляем старую цену
                    input.dataset.oldValue = newQuantity;
                    
                    // Пересчитываем сумму
                    const row = input.closest('tr');
                    const price = parseFloat(row.querySelector('td:nth-child(3)').textContent);
                    const sumCell = row.querySelector('td:nth-child(5)');
                    sumCell.textContent = (price * newQuantity).toFixed(2) + ' руб.';
                    
                    // Пересчитываем общую сумму
                    updateTotalSum();
                } else {
                    alert(`Ошибка: ${result.message}`);
                    input.value = oldValue;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Произошла ошибка при обновлении количества');
                input.value = oldValue;
            }
        }
    });

    // Функция для пересчета общей суммы
    function updateTotalSum() {
        let total = 0;
        document.querySelectorAll('#cart tbody tr').forEach(row => {
            const sumText = row.querySelector('td:nth-child(5)').textContent;
            total += parseFloat(sumText);
        });
        
        document.querySelector('#cart .text-end h4').textContent = `Итого: ${total.toFixed(2)} руб.`;
    }

    // Обработчики для формы отзывов
    const openBtn = document.getElementById('openReviewForm');
    const formContainer = document.getElementById('reviewFormContainer');
    const cancelBtn = document.getElementById('cancelReview');
    
    // Открытие формы
    openBtn.addEventListener('click', function() {
        formContainer.style.display = 'block';
        openBtn.style.display = 'none';
    });
    
    // Закрытие формы
    cancelBtn.addEventListener('click', function() {
        formContainer.style.display = 'none';
        openBtn.style.display = 'block';
        document.getElementById('reviewForm').reset();
    });
    
    // Отправка формы через AJAX
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'submit_review');
        
        fetch('modal/handle_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Отзыв успешно отправлен!');
                formContainer.style.display = 'none';
                openBtn.style.display = 'block';
                this.reset();
                loadUserReviews(); // Обновляем список отзывов
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при отправке отзыва');
        });
    });
    
    // Функция для загрузки отзывов пользователя
    function loadUserReviews() {
        fetch('modal/handle_review.php?action=get_reviews&user_id=<?= $_SESSION['user_id'] ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const reviewsContainer = document.getElementById('userReviews');
                reviewsContainer.innerHTML = '';
                
                if (data.reviews.length > 0) {
                    const table = document.createElement('table');
                    table.className = 'table table-striped';
                    
                    const thead = document.createElement('thead');
                    thead.innerHTML = `
                        <tr>
                            <th>Дата</th>
                            <th>Отзыв</th>
                            <th>Оценка</th>
                            <th>Действия</th>
                        </tr>
                    `;
                    table.appendChild(thead);
                    
                    const tbody = document.createElement('tbody');
                    data.reviews.forEach(review => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${new Date(review.created_at).toLocaleString()}</td>
                            <td>${review.review_text}</td>
                            <td>${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</td>
                            <td><button class="btn btn-sm btn-danger delete-review" data-id="${review.id}">Удалить</button></td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    table.appendChild(tbody);
                    reviewsContainer.appendChild(table);
                    
                    // Добавляем обработчики для кнопок удаления
                    document.querySelectorAll('.delete-review').forEach(btn => {
                        btn.addEventListener('click', function() {
                            if (confirm('Вы уверены, что хотите удалить этот отзыв?')) {
                                deleteReview(this.getAttribute('data-id'));
                            }
                        });
                    });
                } else {
                    reviewsContainer.innerHTML = '<p>У вас пока нет отзывов.</p>';
                }
            }
        });
    }
    
    // Функция для удаления отзыва
    function deleteReview(reviewId) {
        const formData = new FormData();
        formData.append('action', 'delete_review');
        formData.append('review_id', reviewId);
        
        fetch('modal/handle_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Отзыв удален');
                loadUserReviews(); // Обновляем список отзывов
            } else {
                alert('Ошибка: ' + data.message);
            }
        });
    }
    
    // Загружаем отзывы при загрузке страницы
    loadUserReviews();
});

// Показ/скрытие полей адреса доставки
document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('deliveryFields').style.display = 
            this.value === 'delivery' ? 'block' : 'none';
    });
});

// Обработка отправки формы заказа
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
    const deliveryTime = document.getElementById('delivery_time').value;
    
    if (!deliveryTime) {
        e.preventDefault();
        alert('Пожалуйста, выберите время получения заказа');
        return;
    }
    
    if (deliveryType === 'delivery' && !document.getElementById('delivery_address').value.trim()) {
        e.preventDefault();
        alert('Пожалуйста, укажите адрес доставки');
        return;
    }
});
</script>

<?php
// Обработка загрузки аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $uploadDir = __DIR__ . '/img/uploads/avatar/';
    
    // Создаем директорию, если ее нет
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['avatar']['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        die("Недопустимый тип файла. Разрешены только JPEG, PNG и GIF.");
    }
    
    // Генерируем уникальное имя файла
    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $newFileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
        // Обновляем путь в базе данных
        $relativePath = 'img/uploads/avatar/' . $newFileName;
        $stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
        $stmt->execute([$relativePath, $_SESSION['user_id']]);
        
        // Обновляем сессию и перезагружаем страницу
        $_SESSION['avatar_path'] = $relativePath;
        header("Location: profile.php");
        exit;
    } else {
        die("Ошибка при загрузке файла.");
    }
}
?>
</body>
</html>