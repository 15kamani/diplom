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
        /* Стили для корзины */
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

/* Адаптивные стили */
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

<div class="for-otziv card">
    <!-- Текст с просьбой оставить отзыв -->
    <div class="review-invitation mb-4">
        <p class="lead text-muted" style="font-size: 1.5rem; line-height: 1.6;">
            Нам очень важно ваше мнение! Поделитесь, пожалуйста, своими впечатлениями — 
            это поможет нам становиться лучше и мотивирует нашу команду. 
            Спасибо, что находите время для обратной связи!
        </p>
    </div>

    <!-- Кнопка для открытия формы -->
    <button id="openReviewForm" class="btn btn-custom" style="font-size: 16px;">Написать отзыв</button>

    <!-- Скрытый div с формой -->
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

</main>

<?php include 'components/footer.php'; ?>
    <!-- Подключите Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

<script src="js/script.js"></script>
<script src="js/script-modal.js"></script>

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
<!-- Скрипты для работы с корзиной -->
<script>
// Обработчик удаления товара
document.getElementById('cart-items').addEventListener('click', async function(e) {
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
document.getElementById('cart-items').addEventListener('change', async function(e) {
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
    document.querySelectorAll('#cart-items tbody tr').forEach(row => {
        const sumText = row.querySelector('td:nth-child(5)').textContent;
        total += parseFloat(sumText);
    });
    
    document.querySelector('.checkout-btn').previousElementSibling.innerHTML = 
        `Итого: ${total.toFixed(2)} руб.`;
}
    
    // Обработчик оформления заказа
    document.querySelector('.checkout-btn')?.addEventListener('click', function() {
        alert('Функционал оформления заказа будет реализован позже');
});


document.addEventListener('DOMContentLoaded', function() {
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
        fetch('modal/handle_review.php?action=get_reviews')
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
</script>

</body>
</html>