    <header>
        <div class="top-menu">
            <div class="media-icon hidden">
                <a href="https://vk.com/coffeessovoy35"><img src="img/icon/vk.png" alt="Вконтакте"></a>
                <a href="https://wa.me/79115128112"><img src="img/icon/instagram.png" alt="Ватсап"></a>
                <a href="https://www.instagram.coffeessovoy35"><img src="img/icon/whatsapp.png" alt="Инстаграм"></a>
            </div>
            <div class="logo">
                <a href="#"><img src="img/header/logo.jpg" alt="Логотип кофейни 'Кофе с СоВой'"></a>
            </div>
            <div class="contact-menu hidden">
                <div class="contact-tools">
                    <div class="contact-tool">
                        <img src="img/header/location.png" alt="Город, где находится чудестная кофейня">
                        <a>Вытегра</a>
                    </div>
                    <div class="contact-tool">
                        <div class="text-to-copy" onclick="copyText(this)"><img src="img/header/phone.png"
                                alt="Номер телефона для связи">+7 (911) 512-81-12</div>
                    </div>
                </div>
                <div class="btn-order">
                    <div class="button-container">
                        <button class="hover-button" data-bs-target="#LoginModal" data-bs-toggle="modal">Войти</button>
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
                            <a class="nav-link" href="index.html">Главная</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Меню
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="page/menu.html#novinki">Новинки</a>
                                <a class="dropdown-item" href="page/menu.html#drinks">Напитки</a>
                                <a class="dropdown-item" href="page/menu.html#bistro">Бистро/Пекарня</a>
                                <a class="dropdown-item" href="page/menu.html#presents">Подарочные наборы</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="page/delivery.html#deliver">Доставка</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBooking1" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Бронирование
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownBooking1">
                                <a class="dropdown-item" href="page/booking.html">Забронировать столик</a>
                                <a class="dropdown-item" href="page/booking.html#hall_reservation">Заказать зал</a>
                            </div>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="page/news.html">Новости</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="page/galery.html">Галерея</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBooking2" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                О Нас
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownBooking2">
                                <a class="dropdown-item" href="page/about-us.html">Кто мы?</a>
                                <a class="dropdown-item" href="page/about-us.html#contact">Контакты</a>
                                <a class="dropdown-item" href="page/about-us.html#impotant">Создатели</a>
                                <a class="dropdown-item" href="page/about-us.html#retwit">Отзывы</a>
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
                <a href="index.html"><img src="img/header/logo.jpg" alt="Логотип" class="logo-burger"></a>

                <!-- Кнопка "Войти" -->
                <div class="btn-order">
                    <div class="button-container">
                        <button class="hover-button" data-bs-target="#LoginModal" data-bs-toggle="modal">Войти</button>
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
                            <a class="nav-link" href="#">Главная</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="page/menu.html" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Меню
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="page/menu.html#novinki1">Новинки</a>
                                <a class="dropdown-item" href="page/menu.html#drinks1">Напитки</a>
                                <a class="dropdown-item" href="page/menu.html#bistro1">Бистро/Пекарня</a>
                                <a class="dropdown-item" href="page/menu.html#presents1">Подарочные наборы</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="page/delivery.html#deliver">Доставка</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="page/booking.html" id="navbarDropdownBooking"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Бронирование
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownBooking">
                                <a class="dropdown-item" href="page/booking.html">Забронировать столик</a>
                                <a class="dropdown-item" href="page/booking.html#hall_reservation">Заказать зал</a>
                            </div>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="page/news.html">Новости</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="page/galery.html">Галерея</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="page/about-us.html" id="navbarDropdownAbout"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                О Нас
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownAbout">
                                <a class="dropdown-item" href="page/about-us.html">Кто мы?</a>
                                <a class="dropdown-item" href="page/about-us.html#contact">Контакты</a>
                                <a class="dropdown-item" href="page/about-us.html#impotant">Создатели</a>
                                <a class="dropdown-item" href="page/about-us.html#retwit">Отзывы</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>