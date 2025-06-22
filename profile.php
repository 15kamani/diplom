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

    $avatar_path = (isset($user['avatar_path']) && !empty($user['avatar_path']) && file_exists($user['avatar_path'])) 
        ? $user['avatar_path'] 
        : 'img/icon/default_avatar.png';

} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}

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
        .avatar-upload-container {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            display: none;
        }
        .avatar-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
        }
        .cart-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .cart-item-image {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
        }
        .remove-from-cart {
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .cart-section table {
                font-size: 14px;
            }
            .cart-section th, 
            .cart-section td {
                padding: 5px;
            }
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
            border-collapse: collapse;
        }
        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
            text-align: left;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #8b5e3c;
            color: white;
        }
        .table tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }
        .table img {
            vertical-align: middle;
        }
        .quantity-input {
            display: inline-block;
            width: 70px;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .star-rating {
            color: gold;
            font-size: 1.2em;
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
        <h1>Профиль</h1>
        <div class="profile-card card-t">
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

                    <p class="text-custom-1 mt-2" id="cart"><?= htmlspecialchars($user['username']) ?></p>
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
    </div>
    
    <!-- Секция корзины -->
    <div class="cart-section mt-5">
        <h2>Ваша корзина</h2>
        <div id="cart-items">
            <?php
            $cartItems = [];
            $total = 0;

            try {
                if (isset($_SESSION['user_id'])) {
                    $stmt = $pdo->prepare("
                        SELECT c.*, m.title, m.image 
                        FROM cart c
                        JOIN menu_items m ON c.menu_item_id = m.id
                        WHERE c.user_id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $cartItems = $stmt->fetchAll();
                }

                if (empty($cartItems)) {
                    echo '<p>Ваша корзина пуста</p>';
                } else {
            ?>

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
                        <?php foreach ($cartItems as $item): 
                            $itemSum = $item['price'] * $item['quantity'];
                            $total += $itemSum;
                        ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" width="50" class="me-2">
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
                                        data-old-value="<?= htmlspecialchars($item['quantity']) ?>"
                                        style="width: 70px;">
                                </td>
                                <td><?= $itemSum ?> руб.</td>
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
                <h4>Итого: <?= $total ?> руб.</h4>
                <button class="btn btn-custom checkout-btn">Оформить заказ</button>
            </div>

            <?php 
                } // закрытие else
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Ошибка при загрузке корзины: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Секция бронирований -->
    <div class="reservations-section mt-5">
        <h2>Ваши бронирования</h2>
        <div id="reservations-list">
            <?php
            $reservations = [];
            
            try {
                if (isset($_SESSION['user_id'])) {
                    $stmt = $pdo->prepare("
                        SELECT * 
                        FROM reservations 
                        WHERE user_id = ? 
                        ORDER BY 
                            CASE status
                                WHEN 'cancelled' THEN 4
                                WHEN 'new' THEN 1
                                WHEN 'pending' THEN 2
                                WHEN 'confirmed' THEN 3
                            END,
                            date ASC, 
                            time ASC
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $reservations = $stmt->fetchAll();
                }

                if (empty($reservations)) {
                    echo '<p>У вас нет активных бронирований</p>';
                } else {
            ?>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th>Детали</th>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Гостей</th>
                            <th>Статус</th>
                            <th>Комментарий</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): 
                            // Форматируем дату и время
                            $date = date('d.m.Y', strtotime($reservation['date']));
                            $time = date('H:i', strtotime($reservation['time']));
                            
                            // Определяем тип бронирования
                            $type = $reservation['reservation_type'] == 'table' ? 'Столик' : 'Банкетный зал';
                            $details = $reservation['reservation_type'] == 'table' 
                                ? '№' . $reservation['table_number'] 
                                : ($reservation['event_type'] ? htmlspecialchars($reservation['event_type']) : '-');
                            
                            // Определяем стиль строки и статус
                            $statusText = '';
                            $badgeClass = '';
                            switch ($reservation['status']) {
                                case 'new':
                                    $statusText = 'Новое';
                                    $badgeClass = 'bg-primary';
                                    $rowClass = '';
                                    break;
                                case 'pending':
                                    $statusText = 'В обработке';
                                    $badgeClass = 'bg-warning text-dark';
                                    $rowClass = '';
                                    break;
                                case 'confirmed':
                                    $statusText = 'Подтверждено';
                                    $badgeClass = 'bg-success';
                                    $rowClass = '';
                                    break;
                                case 'cancelled':
                                    $statusText = 'Отменено';
                                    $badgeClass = 'bg-danger';
                                    $rowClass = 'table-secondary';
                                    break;
                            }
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= $type ?></td>
                                <td><?= $details ?></td>
                                <td><?= $date ?></td>
                                <td><?= $time ?></td>
                                <td><?= $reservation['guests'] ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <?php if ($reservation['status'] == 'cancelled' && !empty($reservation['cancel_reason'])): ?>
                                        <span class="text-danger"><?= htmlspecialchars($reservation['cancel_reason']) ?></span>
                                    <?php else: ?>
                                        <?= $reservation['comments'] ? htmlspecialchars($reservation['comments']) : '-' ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reservation['status'] != 'cancelled'): ?>
                                        <button class="btn btn-sm btn-danger cancel-reservation" 
                                                data-reservation-id="<?= $reservation['id'] ?>">
                                            Отменить
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Нет действий</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
                } // закрытие else
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Ошибка при загрузке бронирований: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>    

    <div class="for-otziv card-t">
        <!-- Текст с просьбой оставить отзыв -->
        <div class="review-invitation mb-4">
            <p class="lead text-muted" style="font-size: 1.5rem; line-height: 1.6;">
                Нам очень важно ваше мнение! Поделитесь, пожалуйста, своими впечатлениями — 
                это поможет нам становиться лучше и мотивирует нашу команду. 
                Спасибо, что находите время для обратной связи!
            </p>
            <!-- Кнопка для открытия формы -->
            <button id="openReviewForm" class="btn btn-custom" style="font-size: 16px;">Написать отзыв</button>
        </div>

        <!-- Скрытый div с формой -->
        <div id="reviewFormContainer" style="display: none; margin-top: 20px;">
            <div class="card-t">
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

        <!-- Секция для отображения отзывов пользователя -->
        <div id="userReviews" class="mt-5">
            <h3>Ваши отзывы</h3>
            <div class="reviews-list"></div>
        </div>
    </div>
</main>

<?php include 'components/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script src="js/script-modal.js"></script>

<script>
$(document).ready(function() {
    // Переключение формы загрузки аватара
    $('.btn-toggle-upload').click(function() {
        $('.avatar-upload-container').toggle();
    });
    
    $('.cancel-upload').click(function() {
        $('.avatar-upload-container').hide();
        $('#avatarForm')[0].reset();
    });

    // Обработчик удаления товара
    $(document).on('click', '.remove-from-cart', async function(e) {
        e.preventDefault();
        const cartId = $(this).data('cart-id');

        try {
            const response = await fetch(`components/remove_from_cart.php?id=${cartId}`);
            const result = await response.json();

            if (result.status === 'success') {
                // Плавное исчезновение строки
                $(this).closest('tr').css({
                    'transition': 'opacity 0.3s',
                    'opacity': '0'
                });

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
    });

    // Обработчик изменения количества
    $(document).on('change', '.quantity-input', async function() {
        const input = $(this);
        const cartId = input.data('cart-id');
        const newQuantity = parseInt(input.val());
        const oldValue = parseInt(input.data('old-value'));

        // Валидация
        if (isNaN(newQuantity) || newQuantity < 1 || newQuantity > 100) {
            alert('Количество должно быть от 1 до 100');
            input.val(oldValue);
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
                input.data('old-value', newQuantity);

                // Пересчитываем сумму
                const row = input.closest('tr');
                const price = parseFloat(row.find('td:nth-child(3)').text());
                const sumCell = row.find('td:nth-child(5)');
                sumCell.text((price * newQuantity).toFixed(2) + ' руб.');

                // Пересчитываем общую сумму
                updateTotalSum();
            } else {
                alert(`Ошибка: ${result.message}`);
                input.val(oldValue);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при обновлении количества');
            input.val(oldValue);
        }
    });

    // Функция для пересчета общей суммы
    function updateTotalSum() {
        let total = 0;
        $('#cart-items tbody tr').each(function() {
            const sumText = $(this).find('td:nth-child(5)').text();
            total += parseFloat(sumText);
        });

        $('.checkout-btn').prev().html(`Итого: ${total.toFixed(2)} руб.`);
    }

    // Обработчик оформления заказа
    $('.checkout-btn').click(function() {
        alert('Функционал оформления заказа будет реализован позже');
    });

    // Управление формой отзыва
    $('#openReviewForm').click(function() {
        $('#reviewFormContainer').show();
        $(this).hide();
    });

    $('#cancelReview').click(function() {
        $('#reviewFormContainer').hide();
        $('#openReviewForm').show();
        $('#reviewForm')[0].reset();
    });

    // Отправка формы отзыва
    $('#reviewForm').submit(function(e) {
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
                $('#reviewFormContainer').hide();
                $('#openReviewForm').show();
                this.reset();
                loadUserReviews();
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
        fetch('modal/handle_review.php?action=get_reviews')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const reviewsContainer = $('.reviews-list');
                reviewsContainer.empty();

                if (data.reviews.length > 0) {
                    const table = $('<table>').addClass('table table-striped');
                    const thead = $('<thead>').html(`
                        <tr>
                            <th>Дата</th>
                            <th>Отзыв</th>
                            <th>Оценка</th>
                            <th>Действия</th>
                        </tr>
                    `);
                    table.append(thead);

                    const tbody = $('<tbody>');
                    data.reviews.forEach(review => {
                        const row = $('<tr>').html(`
                            <td>${new Date(review.created_at).toLocaleString()}</td>
                            <td>${review.review_text}</td>
                            <td><span class="star-rating">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</span></td>
                            <td><button class="btn btn-sm btn-danger delete-review" data-id="${review.id}">Удалить</button></td>
                        `);
                        tbody.append(row);
                    });

                    table.append(tbody);
                    reviewsContainer.append(table);

                    // Добавляем обработчики для кнопок удаления
                    $('.delete-review').click(function() {
                        if (confirm('Вы уверены, что хотите удалить этот отзыв?')) {
                            deleteReview($(this).data('id'));
                        }
                    });
                } else {
                    reviewsContainer.html('<p>У вас пока нет отзывов.</p>');
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
                loadUserReviews();
            } else {
                alert('Ошибка: ' + data.message);
            }
        });
    }

    // Загружаем отзывы при загрузке страницы
    loadUserReviews();
});

// Обработчик отмены бронирования
$(document).on('click', '.cancel-reservation', function() {
    const reservationId = $(this).data('reservation-id');
    
    // Создаем модальное окно Bootstrap для подтверждения
    const modalHTML = `
        <div class="modal fade" id="cancelReservationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Отмена бронирования</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Причина отмены</label>
                            <textarea class="form-control" id="cancelReason" rows="3" placeholder="Укажите причину отмены"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="button" class="btn btn-danger" id="confirmCancel">Подтвердить отмену</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('cancelReservationModal'));
    modal.show();
    
    // Обработчик подтверждения отмены
    $('#confirmCancel').on('click', async function() {
        const reason = $('#cancelReason').val().trim() || 'Отменено пользователем';
        
        try {
            const response = await fetch('components/cancel_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reservation_id: reservationId,
                    reason: reason
                })
            });
            
            const result = await response.json();

            if (result.status === 'success') {
                modal.hide();
                $('#cancelReservationModal').remove();
                
                // Плавное исчезновение строки
                $(`button[data-reservation-id="${reservationId}"]`).closest('tr').css({
                    'transition': 'opacity 0.3s',
                    'opacity': '0'
                });

                // Обновление через 300мс
                setTimeout(() => {
                    location.reload();
                }, 300);
            } else {
                alert(`Ошибка: ${result.message}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при отмене бронирования');
        }
    });
    
    // Удаляем модальное окно при закрытии
    $('#cancelReservationModal').on('hidden.bs.modal', function () {
        $(this).remove();
    });
});
</script>
</body>
</html>