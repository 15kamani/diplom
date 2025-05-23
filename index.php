<?php
    session_start();
    require 'components/db_connect.php';



    // Получаем активные новости
try {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE is_active = 1 ORDER BY date DESC");
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $news = [];
}
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
<body>
    <?php
        include "components/header.php";
        include "modal/login.php";
        include "modal/register.php";
    ?>
        <div class="offer">
        <div class="offer-text hidden">
            <h2>Где бы ты ни был, чем бы ни занимался:</h2>
            <div class="icons-for-offer">
                <div class="icon-offer">
                    <img src="img/offer/work.png" alt="">
                    <p>на работе</p>
                </div>
                <div class="icon-offer">
                    <img src="img/offer/romantic-date.png" alt="">
                    <p>романтическом свидании</p>
                </div>
                <div class="icon-offer">
                    <img src="img/offer/walking.png" alt="">
                    <p>на прогулке</p>
                </div>
            </div>
            <h2>Кофе с СоВой всегда рядом с тобой!</h2>

            <h3>Также у нас вы можете:</h3>
            <h3>- приобрести молотый кофе и чай</h3>
            <h3>- заказать кофейно-чайные букеты</h3>
            <div class="offer-btn">
                <a href="page/menu.html"><button class="btn btn-custom">Подробности ></button></a>
            </div>
        </div>
    </div>
        <div id="tooltip" class="tooltip">Номер скопирован!</div>
    <main>
        <!-- Основной контент -->
        <!-- карусель -->
        <?php
            include 'components/event.php';
            include 'components/news.php';
        ?>
</div>
    </main>

    <?php
        include "components/footer.php";
    ?>

    <!-- Подключите Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/script.js"></script>
<script src="js/script-modal.js"></script>
</body>

</html>