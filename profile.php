<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Подключение к базе данных
require_once 'components/db_connect.php'; // Файл с настройками подключения к БД

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Пользователь не найден");
    }

    // Путь к аватару (если NULL, подставляем дефолтный)
    $avatar_path = $user['avatar_path'] ?: 'img/icon/default_avatar.png';

} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Подключение Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- Подключение стилей проекта -->
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/media.css">
        <!-- Шрифты -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=EB+Garamond:ital,wght@0,400..800;1,400..800&display=swap"
            rel="stylesheet">
        <!-- Иконка -->
        <link rel="icon" href="img/favicon.png" type="image/x-icon">
        <title>Кофе с СоВой</title>
    </head>
<body class="container-0">
<?php 
    include 'components/header.php';
?>



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
                        <button type="button" class="btn-toggle-upload">Обновить аватар</button>
                        
                        <div class="avatar-upload-container" style="display: none;">
                            <form id="avatarForm" method="POST" action="upload_avatar.php" enctype="multipart/form-data">
                                <div class="avatar-upload">
                                    <input type="file" id="avatarInput" name="avatar" accept="image/*" class="form-control mb-2" required>
                                    <button type="submit" class="btn btn-primary">Загрузить</button>
                                </div>
                            </form>
                        </div>
                        
                        <p class="text-custom-1" id="cart"><?= htmlspecialchars($user['username']) ?></p>
                    </div>
                    <div class="user-card-info">
                        <p><span class="garmond-1">ФИО: </span><?= htmlspecialchars($user['full_name']) ?></p>
                        <p><span class="garmond-1">Телефон: </span><?= htmlspecialchars($user['phone']) ?></p>
                        <p><span class="garmond-1">Почта: </span><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
                <div class="btn-logout">
                    <a href="logout.php" class="btn btn-danger">Выйти</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Показ/скрытие формы загрузки аватара
        document.querySelector('.btn-toggle-upload').addEventListener('click', function() {
            const container = document.querySelector('.avatar-upload-container');
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
        });
    </script>
    <?php
    include './components/footer.php';
    ?>

        <!-- Подключите Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

<script src="js/script.js"></script>
<script src="js/script-modal.js"></script>
</body>
<script>
document.querySelector('.btn-toggle-upload').addEventListener('click', function() {
    const uploadContainer = document.querySelector('.avatar-upload-container');
    if (uploadContainer.style.display === 'none') {
        uploadContainer.style.display = 'block';
        this.textContent = 'Скрыть';
    } else {
        uploadContainer.style.display = 'none';
        this.textContent = 'Обновить аватар';
    }
});

document.getElementById('avatarForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            location.reload(); // Перезагружаем страницу после успешной загрузки
        } else {
            alert('Ошибка при загрузке аватара');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    });
});
</script>
</html>