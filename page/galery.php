<?php
session_start();
require_once '../components/db_connect.php';

// Получаем данные из базы
$items = $pdo->query("SELECT * FROM media_content ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Подключение Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Подключение стилей проекта -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/media.css">
    <!-- Шрифты -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=EB+Garamond:ital,wght@0,400..800;1,400..800&display=swap"
        rel="stylesheet">
    <!-- Иконка -->
    <link rel="icon" href="../img/favicon.png" type="image/x-icon">
    <title>Новости - Кофе с СоВой</title>
</head>
<body>
        <?php
            include "../components-page/header.php";
            include "../modal/login.php";
            include "../modal/register-for-page.php";
        ?>

        <main style="
    padding-top: 14%;
    padding-bottom: 2%;
">
            <div id="tooltip" class="tooltip">Номер скопирован!</div>
            <div class="gallery">
                <div class="kroshka">
                    <p><a href="../index.php">Главная</a> > <a href="#">Галерея</a></p>
                </div>
                <h1 class="text-center mb-4">Галерея</h1>
                
                <?php if (!empty($items)): ?>
                    <div class="row gallery-grid container-gallery">
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-4 col-sm-6 mb-5">
                                <div class="gallery-item">
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['caption']) ?>" class="img-fluid">
                                    <div class="gallery-overlay">
                                        <p class="gallery-text"><?= htmlspecialchars($item['caption']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">В галерее пока нет изображений</div>
                <?php endif; ?>
                
                <div class="text-off-gallery">
                    <h2>Ещё больше красивых фотографий ты сможешь увидеть в группе ВК:</h2>
                    <a href="https://vk.com/albums-169204320"><button class="btn btn-custom">Увидеть больше ></button></a>
                </div>
            </div>
        </main>

        <?php
            include "../components-page/footer.php";
        ?>

        <!-- Подключите Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

    <script src="../js/script.js"></script>
    <script src="../js/script-modal.js"></script>
</body>
</html>