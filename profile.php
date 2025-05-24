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

</body>
</html>