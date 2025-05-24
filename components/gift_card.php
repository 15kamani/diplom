<!-- для подарков -->
<div class="menu-contaner keep_secret" id="presents">
    <h2>Подарочные наборы</h2>
    <div class="container my-5">
        <div id="cardCarousel-3" class="carousel slide" data-bs-ride="carousel">
            <!-- Слайды -->
            <div class="carousel-inner">
                <!-- Первый слайд (4 карточки) -->
                <div class="carousel-item active">
                    <div class="row">
                        <!-- Жестко закодированная карточка -->
                        <div class="col-md-3">
                            <div class="card h-100">
                                <img src="../img/menu/prisents/card-4.png" class="card-img-top" alt="Уникальный набор">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">УНИКАЛЬНЫЙ НАБОР</h5>
                                    <p class="card-text flex-grow-1">Оставь заявку в форме ниже, чтобы мы собрали подарочный набор по вашим желаниям</p>
                                    <p class="card-text"><strong>Цена: зависит от деталей сборки</strong></p>
                                    <button type="button" class="btn btn-custom mt-auto align-self-start" data-bs-toggle="modal" data-bs-target="#uniqueGiftModal">
                                        Заказать
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Динамические карточки из БД -->
                        <?php
                        // Получаем подарочные наборы из БД, исключая первый (если нужно)
                        $giftItems = $pdo->query("
                            SELECT * FROM gifts 
                            WHERE id > 1  // Исключаем первый набор, если он жестко закодирован
                            ORDER BY id
                            LIMIT 3      // Ограничиваем 3 карточками для первого слайда
                        ")->fetchAll();
                        
                        foreach ($giftItems as $item) {
                            $item['is_custom'] = false; // Указываем, что это не кастомный набор
                            include '../components/gift_card.php';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Дополнительные слайды (если товаров больше 4) -->
                <?php
                $remainingGifts = $pdo->query("
                    SELECT * FROM gifts 
                    WHERE id > 4  // Продолжаем с 5-го элемента
                    ORDER BY id
                ")->fetchAll();
                
                if (!empty($remainingGifts)) {
                    $chunks = array_chunk($remainingGifts, 4);
                    foreach ($chunks as $chunk) {
                        echo '<div class="carousel-item"><div class="row">';
                        foreach ($chunk as $item) {
                            $item['is_custom'] = false;
                            include '../components/gift_card.php';
                        }
                        echo '</div></div>';
                    }
                }
                ?>
            </div>
            
            <!-- Стрелки для переключения -->
            <button class="carousel-control-prev" type="button" data-bs-target="#cardCarousel-3" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Предыдущий</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#cardCarousel-3" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Следующий</span>
            </button>
        </div>
    </div>
</div>

<!-- Мобильная версия -->
<div class="menu-contaner secret" id="presents1">
    <h2>Подарочные наборы</h2>
    <div class="container my-5">
        <div id="presentsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- Жестко закодированная карточка -->
                <div class="carousel-item active">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100">
                                <img src="../img/menu/prisents/card-4.png" class="card-img-top" alt="Уникальный набор">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">УНИКАЛЬНЫЙ НАБОР</h5>
                                    <p class="card-text flex-grow-1">Оставь заявку в форме ниже, чтобы мы собрали подарочный набор по вашим желаниям.</p>
                                    <p class="card-text"><strong>Цена: зависит от деталей сборки</strong></p>
                                    <button type="button" class="btn btn-custom mt-auto align-self-start" data-bs-toggle="modal" data-bs-target="#uniqueGiftModal">
                                        Заказать
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Динамические карточки из БД -->
                <?php
                $allGifts = $pdo->query("
                    SELECT * FROM gifts 
                    WHERE id > 1  // Исключаем первый набор
                    ORDER BY id
                ")->fetchAll();
                
                foreach ($allGifts as $index => $item) {
                    echo '<div class="carousel-item' . ($index === 0 ? ' active' : '') . '">';
                    echo '<div class="row justify-content-center">';
                    echo '<div class="col-12 col-md-6 col-lg-4">';
                    
                    $item['is_custom'] = false;
                    include '../components/gift_card.php';
                    
                    echo '</div></div></div>';
                }
                ?>
            </div>
            
            <!-- Стрелки для переключения -->
            <button class="carousel-control-prev" type="button" data-bs-target="#presentsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Предыдущий</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#presentsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Следующий</span>
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно для уникального набора -->
<div class="modal fade" id="uniqueGiftModal" tabindex="-1" aria-hidden="true">
    <!-- Содержимое модального окна -->
</div>