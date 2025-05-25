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
    <title>Кофе с СоВой</title>
</head>
<body>
        <?php
            include "../components-page/header.php";
            include "../modal/login.php";
            include "../modal/register-for-page.php";
        ?>
    <div id="tooltip" class="tooltip">Номер скопирован!</div>
    <div class="offer">
        <div class="offer-text hidden">
            <div class="kroshka-0">
                <p><a href="../index.php">Главная</a> > <a href="#">Доставка</a></p>
            </div>
            <h2>Где бы ты ни был, чем бы ни занимался:</h2>
            <div class="icons-for-offer">
                <div class="icon-offer hidden">
                    <img src="../img/offer/work.png" alt="">
                    <p>на работе</p>
                </div>
                <div class="icon-offer hidden">
                    <img src="../img/offer/walking.png" alt="">
                    <p>на прогулке</p>
                </div>
                <div class="icon-offer hidden">
                    <img src="../img/offer/romantic-date.png" alt="">
                    <p>романтическом свидании</p>
                </div>
            </div>
            <h2>Кофе с СоВой всегда рядом с тобой!</h2>
            
            <h3>Также у нас вы можете:</h3>
            <h3>- приобрести молотый кофе и чай</h3>
            <h3>- заказать кофейно-чайные букеты</h3>
            <div class="offer-btn hidden" id="deliver">
                <a href="menu.php"><button class="btn btn-custom" id="delivery">Подробности ></button></a>
            </div>                
        </div>
  
    </div>
    <main>
        <div class="about-delivery">
            <h1>Доставка</h1>
            <div class="info-devivery">
                <img src="../img/delivery/delivery.gif" alt="">
                <div class="text-delivery">
                    <p>Доставка по городу - <span class="text-custom-1">200 </span>рублей</p>
                    <p>Доставка по городу <span class="text-custom-1">БЕСПЛАТНО</span> при заказе от<span class="text-custom-1"> 1500 </span>рублей</p>
                    <p>В <span class="text-custom-1">шаговой доступности</span> от нашего кафе (Вянгинская 29) доставка <span class="text-custom-1">БЕСПЛАТНАЯ</span></p>
                    <h4>Подробная информация:</h4>
                    <div class="phone-delivery">
                        <img src="../img/delivery/phone-deliver.png" alt="" onclick="copyText(this)">
                        <div class="text-to-copy" onclick="copyText(this)"><p>8911 509 00 35</p></div>
                    </div>
                </div>
            </div>
            <div id="tooltip" class="tooltip">Номер скопирован!</div>
        </div>
        <div class="predzakaz">
            <h2>Принимаем предзаказы:</h2>
            <div class="pre-slots">
                <div class="pre-slot">
                    <h4 class="garmond-1">КУХНЯ/пекарня:</h4>
                    <p>- в ларёчке по адресу Вянгинская 29</p>
                    <p>- по телефону: 8 (981) 509 00 35</p>
                    <p>- на WhatsApp Messenger: 8 (981) 509 00 35</p>
                    <div class="pre-slot-img">
                        <img src="../img/delivery/bikary.png" alt="">
                    </div>
                </div>
                <div class="pre-slot">
                    <h4 class="garmond-1">КОФЕЙНЯ:</h4>
                    <p>- по адресу Вянгинская 29 второй этаж</p>
                    <p>- по телефону: 8 (911) 509 00 35</p>
                    <p>- на WhatsApp Messenger: 8 (981) 509 00 35</p>
                    <div class="pre-slot-img">
                        <img src="../img/delivery/coffee.png" alt="">
                    </div>
                </div>
            </div>
            <div class="predzakaz-span">
                <span>Время ожидания заказа 5-10 мин при отсутствии очереди.</span>
            </div>            
        </div>
    </main>
    <footer>
        <ul class="menu-footer">
            <li class="step-menu">
                <a href="../index.html">Главная</a>
            </li>
            <li class="step-menu">
                <a href="../page/menu.html">Меню</a>
                <a href="../page/menu.html#novinki" id="a-side">Новинки</a>
                <a href="../page/menu.html#drinks" id="a-side">Напитки</a>
                <a href="../page/menu.html#bistro" id="a-side">Бистро/Пекарня</a>
                <a href="../page/menu.html#presents" id="a-side">Подарочные наборы</a>
            </li>
            <li class="step-menu">
                <a href="../page/delivery.html#deliver">Доставка</a>
            </li>
            <li class="step-menu">
                <a href="../page/booking.html">Бронирование</a>
                <a href="../page/booking.html" id="a-side">Забронировать столик</a>
                <a href="../page/booking.html#hall_reservation" id="a-side">Заказать зал</a>
            </li>
            <li class="step-menu">
                <a href="../page/news.html">Новости</a>
            </li>
            <li class="step-menu">
                <a href="../page/galery.html">Галерея</a>
            </li>
            <li class="step-menu">
                <a href="../page/about-us.html">О Нас</a>
                <a href="../page/about-us.html" id="a-side">Кто мы?</a>
                <a href="../page/about-us.html#contact" id="a-side">Контакты</a>
                <a href="../page/about-us.html#retwit" id="a-side">Отзывы</a>
            </li>
        </ul>
        <div class="image-links">
            <a href="https://vk.com/coffeessovoy35" target="_blank">
                <img src="../img/icon/vk.png" alt="Вконтакте">
            </a>
            <a href="https://www.instagram.coffeessovoy35" target="_blank">
                <img src="../img/icon/instagram.png" alt="Инстаграм">
            </a>
            <a href="https://wa.me/79115128112" target="_blank">
                <img src="../img/icon/whatsapp.png" alt="Ватсап">
            </a>
        </div>
        <div class="logotip-footer">
            <a href="#"><img src="../img/header/logo.jpg" alt=""></a>
        </div>
        <div class="kapcha">
            <span>Все права пренадлежать "Кофе с СоВой" | Каманина Алина ИСП-421р | @sun_rise3001</span>
            <span id="years-develop">2024-2025</span>
        </div>
    </footer>
      <button id="scrollToTop" aria-label="Наверх">
        &#8593; <!-- Стрелочка вверх -->
      </button>

    <!-- Подключение JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="../js/script.js"></script>
    <script src="../js/script-modal.js"></script>
</body>

</html>