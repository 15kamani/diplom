<header>
    <div class="top-menu">
        <div class="media-icon hidden">
            <a href="https://vk.com/coffeessovoy35"><img src="../img/icon/vk.png" alt="Вконтакте"></a>
            <a href="https://wa.me/79115128112"><img src="../img/icon/instagram.png" alt="Ватсап"></a>
            <a href="https://www.instagram.coffeessovoy35"><img src="../img/icon/whatsapp.png" alt="Инстаграм"></a>
        </div>
        <div class="logo">
            <a href="#"><img src="../img/header/logo.jpg" alt="Логотип кофейни 'Кофе с СоВой'"></a>
        </div>
        <div class="contact-menu hidden">
            <div class="contact-tools">
                <div class="contact-tool">
                    <img src="../img/header/location.png" alt="Город, где находится чудестная кофейня">
                    <a>Вытегра</a>
                </div>
                <div class="contact-tool">
                    <div class="text-to-copy" onclick="copyText(this)"><img src="../img/header/phone.png"
                            alt="Номер телефона для связи">+7 (911) 512-81-12</div>
                </div>
            </div>
            <div class="btn-order">
                <div class="button-container">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button class="hover-button" onclick="window.location.href='logout.php'">Выйти</button>
                    <?php else: ?>
                        <button class="hover-button" data-bs-target="#LoginModal" data-bs-toggle="modal">Войти</button>
                    <?php endif; ?>
                    <div class="owl-tooltip">🦉</div>
                </div>
            </div>
        </div>
    </div>

    <div class="buttom-menu">
        <nav class="navbar navbar-expand-lg bg-f7eabd">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="../index.php">Главная</a>
                    </li>

                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Профиль
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="../profile.php">Профиль</a>
                            <a class="dropdown-item" href="../profile.php">Корзина</a>
                        </div>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Меню
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="../page/menu.php">Новинки</a>
                            <a class="dropdown-item" href="../page/menu.php#drinks">Напитки</a>
                            <a class="dropdown-item" href="../page/menu.php#bistro">Бистро/Пекарня</a>
                            <a class="dropdown-item" href="../page/menu.php#presents">Подарочные наборы</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="delivery.php#deliver">Доставка</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBooking1" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Бронирование
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownBooking1">
                            <a class="dropdown-item" href="../page/booking.php">Забронировать столик</a>
                            <a class="dropdown-item" href="../page/booking.php#hall_reservation">Заказать зал</a>
                        </div>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="page/news.php">Новости</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../page/galery.php">Галерея</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBooking2" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            О Нас
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownBooking2">
                            <a class="dropdown-item" href="../page/about-us.php">Кто мы?</a>
                            <a class="dropdown-item" href="../page/about-us.php#contact">Контакты</a>
                            <a class="dropdown-item" href="../page/about-us.php#impotant">Создатели</a>
                            <a class="dropdown-item" href="../page/about-us.php#retwit">Отзывы</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
    <!-- Навигационное меню с бургером -->
    <nav class="navbar navbar-expand-lg navbar-light bg-f7eabd" id="none-display">
        <div class="container-fluid">
            <!-- Логотип -->
            <a href="index.php"><img src="img/header/logo.jpg" alt="Логотип" class="logo-burger"></a>

            <!-- Кнопка "Войти" или "Выйти" -->
            <div class="btn-order">
                <div class="button-container">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button class="hover-button" onclick="window.location.href='logout.php'">Выйти</button>
                    <?php else: ?>
                        <button class="hover-button" data-bs-target="#LoginModal" data-bs-toggle="modal">Войти</button>
                    <?php endif; ?>
                    <div class="owl-tooltip">🦉</div>
                </div>
            </div>

            <!-- Кнопка бургера -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Меню -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="../index.php">Главная</a>
                    </li>
                
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Профиль
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="../profile.php">Профиль</a>
                            <a class="dropdown-item" href="../profile.php">Корзина</a>
                        </div>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="page/menu.php" id="navbarDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Меню
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="../page/menu.php#novinki1">Новинки</a>
                            <a class="dropdown-item" href="../page/menu.php#drinks1">Напитки</a>
                            <a class="dropdown-item" href="../page/menu.php#bistro1">Бистро/Пекарня</a>
                            <a class="dropdown-item" href="../page/menu.php#presents1">Подарочные наборы</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="delivery.php#deliver">Доставка</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="../page/booking.php" id="navbarDropdownBooking"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Бронирование
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownBooking">
                            <a class="dropdown-item" href="../page/booking.php">Забронировать столик</a>
                            <a class="dropdown-item" href="../page/booking.php#hall_reservation">Заказать зал</a>
                        </div>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="page/news.php">Новости</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../page/galery.php">Галерея</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="../page/about-us.php" id="navbarDropdownAbout"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            О Нас
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAbout">
                            <a class="dropdown-item" href="../page/about-us.php">Кто мы?</a>
                            <a class="dropdown-item" href="../page/about-us.php#contact">Контакты</a>
                            <a class="dropdown-item" href="../page/about-us.php#impotant">Создатели</a>
                            <a class="dropdown-item" href="../page/about-us.php#retwit">Отзывы</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>