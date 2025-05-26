<?php
session_start();
require_once '../components/db_connect.php';

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
    <div class="offer-about-us">
        <div class="offer-text hidden auo">
            <div class="kroshka-0">
                <p><a href="../index.html">Главная</a> > <a href="#">О Нас</a></p>
            </div>
            <h2>Кофе с СоВой</h2>
            <div class="icons-for-offer">
                <div class="icon-offer-2 hidden ">
                    <img src="../img/coffee-table.png" alt="">
                </div>
            </div>
            <div class="about-us-tf">
                <h3>Наша история началась с любви к кофе и желания создать пространство,</h3>
                <h3>где каждый сможет почувствовать себя как дома.</h3>
                <h3>Мы тщательно отбираем лучшие сорта кофе со всего мира,</h3>
                <h3>чтобы предложить вам напитки с насыщенным вкусом и неповторимым ароматом.</h3>
            </div>

        </div>
    </div>
    <main>
        <div id="tooltip" class="tooltip">Номер скопирован!</div>
        <div class="about-us-first">
            <img src="../img/about-us.png" alt="">
            <div class="about-us-first-text garmond-0">
                <h3>"Кофе с Совой" — это не только про кофе</h3>
                <p>Это про теплые встречи, душевные разговоры и моменты, которые хочется сохранить в памяти. Мы гордимся
                    тем, что создали место, где можно отдохнуть от суеты, насладиться вкусными десертами и провести
                    время в приятной компании.</p>
                <p>Приходите к нам, чтобы почувствовать атмосферу уюта и вдохновения.</p>
                <p id="contact">Мы всегда рады видеть вас!</p>
            </div>
        </div>
        <!-- контакты -->
        <div class="about-contact">
            <h2 class="text-center mb-4">Контакты</h2>
            <div class="contact">
                <div class="map">
                    <iframe
                        src="https://yandex.ru/map-widget/v1/?um=constructor%3A5ec089a8099928896b2499e25231a32603ba7d310d05d68aeaca7df79a196c73&amp;source=constructor"
                         frameborder="0" style="border-radius: 10px;"></iframe>
                </div>
                <div class="text-contact">
                    <p class="garmond-0"><span class="garmond-1">Мы всегда рады видеть вас в нашей кофейне!</span></p>
                    <p>Если у вас возникли вопросы, есть пожелания или вы хотите поделиться впечатлениями, свяжитесь с
                        нами любым удобным для вас способом.</p>
                    <div class="tc-contacts">
                        <div class="tc-media">
                            <img src="../img/phone-deliver.png" alt="">
                            <div class="text-to-copy" onclick="copyText(this)">
                                <p>8911 509 00 35</p>
                            </div>
                        </div>
                        <div class="tc-media">
                            <img src="../img/location-dark.png" alt="">
                            <p>г. Вытегра, ул. Вянгинская, 29</p>
                        </div>
                    </div>
                    <div class="tc-media-all">
                        <p class="garmond-1">Наши социальные сети:</p>
                        <div class="tc-contact tc-c">
                            <a href="#"><img src="../img/icon/vk-brown.png" alt=""></a>
                            <a href="#"><img src="../img/icon/instagram-brown.png" alt=""></a>
                            <a href="#"><img src="../img/icon/whatsapp-brown.png" alt=""></a>
                        </div>
                    </div>
                    <div class="tc-work-time">
                        <p class="garmond-1">Режим работы:</p>
                        <div class="work-time">
                            <div class="day-work-time">
                                <p>ПН-ПТ</p>
                                <p>8:00 -20:00</p>
                            </div>
                            <div class="day-work-time">
                                <p>CБ</p>
                                <p>09:00 - 20:00</p>
                            </div>
                            <div class="day-work-time" id="impotant">
                                <p>ВСК</p>
                                <p>10:00 - 20:00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="about-impotant">
            <h2 class="text-center mb-4">Наши создатели:</h2>
            <div class="about-impotant-persons">
                <div class="impotant-person card">
                    <img src="../img/person-1.png" alt="">
                    <div class="text-person">
                        <p class="garmond-1">Фокина Марина</p>
                        <span>Руководитель Кофейни</span>
                    </div>
                </div>
                <div class="impotant-person card">
                    <img src="../img/person-2.png" alt="">
                    <div class="text-person">
                        <p class="garmond-1">Александр Филькин</p>
                        <span>Руководитель Пекарни</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="my-5">
            <h2 class="text-center mb-4">Отзывы</h2>
            <div class="reviews-slider">
                <?php
                
                // Получаем отзывы с информацией о пользователях
                $stmt = $pdo->query("
                    SELECT r.*, u.full_name, u.avatar_path 
                    FROM reviews r
                    JOIN users u ON r.user_id = u.id
                    ORDER BY r.created_at DESC
                    LIMIT 10
                ");
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($reviews) > 0) {
                    $first = true;
                    foreach ($reviews as $review) {
                        // Определяем класс active для первого элемента
                        $activeClass = $first ? 'active' : '';
                        $first = false;
                        
                        // Генерируем звездочки рейтинга
                        $ratingStars = str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']);
                        
                        // Путь к аватару (используем дефолтный, если нет)
                        $avatarPath = !empty($review['avatar_path']) ? '../' . htmlspecialchars($review['avatar_path']) : '../img/default-avatar.png';
                        
                        echo '
                        <div class="review-card '.$activeClass.'">
                            <div class="card">
                                <img src="'.$avatarPath.'" class="card-img-retwit" alt="Фото автора">
                                <div class="card-body">
                                    <h5 class="card-title">'.htmlspecialchars($review['full_name']).'</h5>
                                    <div class="rating mb-2 text-warning">'.$ratingStars.'</div>
                                    <p class="card-text">'.nl2br(htmlspecialchars($review['review_text'])).'</p>
                                    <small class="text-muted">'.date('d.m.Y', strtotime($review['created_at'])).'</small>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<p class="text-center">Пока нет отзывов. Будьте первым!</p>';
                }
                ?>
                
                <!-- Кнопки управления -->
                <?php if (count($reviews) > 1): ?>
                    <button class="carousel-control-prev prev" onclick="prevReview()">&#10094;</button>
                    <button class="carousel-control-next next" onclick="nextReview()">&#10095;</button>
                <?php endif; ?>
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

    <script>
        function prevReview() {
            const cards = document.querySelectorAll('.review-card');
            let activeIndex = 0;
            
            cards.forEach((card, index) => {
                if (card.classList.contains('active')) {
                    activeIndex = index;
                    card.classList.remove('active');
                }
            });
            
            const prevIndex = (activeIndex - 1 + cards.length) % cards.length;
            cards[prevIndex].classList.add('active');
        }
        
        function nextReview() {
            const cards = document.querySelectorAll('.review-card');
            let activeIndex = 0;
            
            cards.forEach((card, index) => {
                if (card.classList.contains('active')) {
                    activeIndex = index;
                    card.classList.remove('active');
                }
            });
            
            const nextIndex = (activeIndex + 1) % cards.length;
            cards[nextIndex].classList.add('active');
        }
    </script>
</body>
</html>