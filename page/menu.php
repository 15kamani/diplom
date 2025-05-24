<?php
session_start();
require_once '../components/db_connect.php';


// Проверка подключения к БД
if (!$pdo) {
    die("Database connection failed");
}

// Получаем 4 новинки из базы данных
$novinki = [];
try {
    $stmt = $pdo->prepare("
        SELECT m.*, 
               GROUP_CONCAT(nv.variant_name, ' (', nv.price, ' руб.)' SEPARATOR ', ') AS variants
        FROM menu_items m
        LEFT JOIN menu_new_variants nv ON nv.menu_item_id = m.id
        WHERE m.category = 'new'
        GROUP BY m.id
        ORDER BY m.created_at DESC
        LIMIT 4
    ");
    $stmt->execute();
    $novinki = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching novinki: " . $e->getMessage());
}

// Получаем напитки из базы данных
$coffeeItems = $teaItems = [];
try {
    $coffeeItems = $pdo->query("
        SELECT m.*, 
               GROUP_CONCAT(CONCAT(d.volume_ml, 'мл - ', d.price, ' руб.') SEPARATOR ', ') AS sizes
        FROM menu_items m
        JOIN menu_drinks d ON d.menu_item_id = m.id
        WHERE m.category = 'drinks' AND d.type = 'coffee'
        GROUP BY m.id
        ORDER BY m.title
    ")->fetchAll();

    $teaItems = $pdo->query("
        SELECT m.*, 
               GROUP_CONCAT(CONCAT(d.tea_variety, ' - ', d.price, ' руб.') SEPARATOR ', ') AS varieties
        FROM menu_items m
        JOIN menu_drinks d ON d.menu_item_id = m.id
        WHERE m.category = 'drinks' AND d.type = 'tea'
        GROUP BY m.id
        ORDER BY m.title
    ")->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching drinks: " . $e->getMessage());
}

// Получаем товары категории "Бистро/Пекарня"
$bistroItems = [];
try {
    $bistroItems = $pdo->query("
        SELECT * FROM menu_items 
        WHERE category = 'bistro'
        ORDER BY title
    ")->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching bistro items: " . $e->getMessage());
}

// Получаем подарочные наборы из базы данных
$giftItems = [];
try {
    $stmt = $pdo->prepare("
        SELECT id, image, title, short_desc, standard_price 
        FROM menu_items 
        WHERE category = 'gifts'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $giftItems = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching gift items: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню - Кофе с СоВой</title>
    
    <!-- Подключение Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Подключение стилей проекта -->
    <link rel="stylesheet" href="../css/style-menu.css">
    <link rel="stylesheet" href="../css/media-.css">
    
    <!-- Шрифты -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond&family=EB+Garamond&display=swap" rel="stylesheet">
    
    <!-- Иконка -->
    <link rel="icon" href="../img/favicon.png" type="image/x-icon">
</head>
<body>
    <?php include "../components-page/header.php"; ?>
    <?php include "../modal/login.php"; ?>
    <?php include "../modal/register-for-page.php"; ?>

    <main id="novinki">
        <div id="tooltip" class="tooltip">Номер скопирован!</div>
        
        <div class="kroshka gallery">
            <p><a href="../index.php">Главная</a> > <a href="#">Меню</a></p>
        </div>
        
        <div class="menu-contaner">
            <!-- Блок новинок -->
            <h1>Новинки</h1>
            
            <!-- Десктопная версия (4 карточки в ряд) -->
            <div class="container my-5 keep_secret">
                <div id="cardCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="row">
                                <?php foreach ($novinki as $item): ?>
                                    <div class="col-md-3">
                                        <?php include '../components/menu_card.php'; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Мобильная версия (1 карточка) -->
            <div class="menu-contaner secret">
                <div class="container my-5">
                    <div id="novinkiCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($novinki as $index => $item): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <div class="row justify-content-center">
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <?php include '../components/menu_card.php'; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#novinkiCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Предыдущий</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#novinkiCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Следующий</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Модальные окна для новинок -->
            <?php foreach ($novinki as $item): ?>
                <div class="modal fade" id="exampleModal-novinki-<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="../<?= htmlspecialchars($item['image']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($item['title']) ?>">
                                <?php endif; ?>
                                
                                <p><?= nl2br(htmlspecialchars($item['full_desc'] ?: $item['short_desc'])) ?></p>
                                
                                <?php if (!empty($item['variants'])): ?>
                                    <div class="mb-3">
                                        <h6>Варианты:</h6>
                                        <?php 
                                        $variants = explode(', ', $item['variants']);
                                        foreach ($variants as $variant): 
                                            $variantParts = explode(' (', $variant);
                                            $name = $variantParts[0];
                                            $price = str_replace(' руб.)', '', $variantParts[1] ?? '');
                                        ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span><?= htmlspecialchars($variant) ?></span>
                                                <?php if (isset($_SESSION['user_id'])): ?>
                                                    <button class="btn btn-sm btn-custom add-to-cart" 
                                                            data-item-id="<?= $item['id'] ?>"
                                                            data-variant="<?= htmlspecialchars($name) ?>"
                                                            data-price="<?= htmlspecialchars($price) ?>">
                                                        В корзину
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-custom" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#loginModal"
                                                            data-bs-dismiss="modal">
                                                        В корзину
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['standard_price'])): ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><strong>Цена:</strong> <?= htmlspecialchars($item['standard_price']) ?> руб.</span>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <button class="btn btn-sm btn-custom add-to-cart" 
                                                    data-item-id="<?= $item['id'] ?>"
                                                    data-price="<?= htmlspecialchars($item['standard_price']) ?>">
                                                В корзину
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-custom" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#LoginModal"
                                                    data-bs-dismiss="modal">
                                                В корзину
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Блок напитков -->
            <div class="menu-contaner keep_secret" id="drinks">
                <h1>Напитки</h1>
                
                <!-- Кофе -->
                <h2 class="menu-contaner">Кофе</h2>
                <div class="container my-5">
                    <div id="coffeeCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php 
                            $coffeeChunks = array_chunk($coffeeItems, 4);
                            foreach ($coffeeChunks as $chunkIndex => $chunk): ?>
                                <div class="carousel-item <?= $chunkIndex === 0 ? 'active' : '' ?>">
                                    <div class="row">
                                        <?php foreach ($chunk as $item): ?>
                                            <div class="col-md-3">
                                                <?php include '../components/drink_card.php'; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#coffeeCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Предыдущий</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#coffeeCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Следующий</span>
                        </button>
                    </div>
                </div>
                
                <!-- Чай -->
                <h2 class="menu-contaner">Чай</h2>
                <div class="container my-5">
                    <div id="teaCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php 
                            $teaChunks = array_chunk($teaItems, 4);
                            foreach ($teaChunks as $chunkIndex => $chunk): ?>
                                <div class="carousel-item <?= $chunkIndex === 0 ? 'active' : '' ?>">
                                    <div class="row">
                                        <?php foreach ($chunk as $item): ?>
                                            <div class="col-md-3">
                                                <?php include '../components/drink_card.php'; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#teaCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Предыдущий</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#teaCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Следующий</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Модальные окна для напитков -->
            <?php 
                $allDrinks = array_merge($coffeeItems, $teaItems);
                foreach ($allDrinks as $item): 
                    $drinkDetails = $pdo->query("
                        SELECT * FROM menu_drinks 
                        WHERE menu_item_id = {$item['id']}
                        ORDER BY price
                    ")->fetchAll();
            ?>
                <div class="modal fade" id="drinkModal-<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="../<?= htmlspecialchars($item['image']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($item['title']) ?>">
                                <?php endif; ?>
                                
                                <p><?= nl2br(htmlspecialchars($item['full_desc'] ?: $item['short_desc'])) ?></p>
                                
                                <div class="mb-3">
                                    <h6>Доступные варианты:</h6>
                                    <?php foreach ($drinkDetails as $variant): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>
                                                <?php if ($variant['type'] === 'coffee'): ?>
                                                    <?= htmlspecialchars($variant['volume_ml']) ?> мл
                                                <?php else: ?>
                                                    <?= htmlspecialchars($variant['tea_variety']) ?>
                                                <?php endif; ?>
                                                - <?= htmlspecialchars($variant['price']) ?> руб.
                                            </span>
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <button class="btn btn-sm btn-custom add-to-cart" 
                                                        data-item-id="<?= $item['id'] ?>"
                                                        data-variant="<?= $variant['type'] === 'coffee' ? $variant['volume_ml'].'мл' : htmlspecialchars($variant['tea_variety']) ?>"
                                                        data-price="<?= htmlspecialchars($variant['price']) ?>">
                                                    В корзину
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-custom" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#loginModal"
                                                        data-bs-dismiss="modal">
                                                    В корзину
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Блок бистро/пекарни -->
            <div class="menu-contaner keep_secret" id="bistro">
                <h1>Бистро/Пекарня</h1>
                <div class="container my-5">
                    <div id="bistroCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php 
                            $bistroChunks = array_chunk($bistroItems, 4);
                            foreach ($bistroChunks as $chunkIndex => $chunk): ?>
                                <div class="carousel-item <?= $chunkIndex === 0 ? 'active' : '' ?>">
                                    <div class="row">
                                        <?php foreach ($chunk as $item): ?>
                                            <div class="col-md-3">
                                                <?php include '../components/bistro_card.php'; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#bistroCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Предыдущий</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#bistroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Следующий</span>
                        </button>
                    </div>
                </div>
            </div>
            
<!-- Блок подарочных наборов -->
<div class="menu-contaner" id="gifts">
    <h1>Подарочные наборы</h1>
    
    <!-- Десктопная версия (4 карточки в ряд) -->
    <div class="container my-5 keep_secret">
        <div id="giftsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                    <div class="row">
                        <?php foreach ($giftItems as $item): ?>
                            <div class="col-md-3">
                                <div class="card">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="../<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($item['short_desc']) ?></p>
                                        <p class="price"><?= htmlspecialchars($item['standard_price']) ?> руб.</p>
                                                                                <button class="btn btn-custom" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#giftModal-<?= $item['id'] ?>">
                                            Подробнее
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
            </div>
        </div>
    </div>
    
    <!-- Мобильная версия (1 карточка) -->
    <div class="menu-contaner secret">
        <div class="container my-5">
            <div id="giftsMobileCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($giftItems as $index => $item): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="row justify-content-center">
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card h-100">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="../<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                            <p class="card-text"><?= htmlspecialchars($item['short_desc']) ?></p>
                                            <p class="price"><?= htmlspecialchars($item['standard_price']) ?> руб.</p>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <button class="btn btn-custom w-100" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#giftModal-<?= $item['id'] ?>">
                                                Подробнее
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button class="carousel-control-prev" type="button" data-bs-target="#giftsMobileCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Предыдущий</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#giftsMobileCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Следующий</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна для подарочных наборов -->
<?php foreach ($giftItems as $item): ?>
    <div class="modal fade" id="giftModal-<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= htmlspecialchars($item['title']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($item['image'])): ?>
                        <img src="../<?= htmlspecialchars($item['image']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($item['title']) ?>">
                    <?php endif; ?>
                    
                    <p><?= nl2br(htmlspecialchars($item['full_desc'] ?? $item['short_desc'])) ?></p>
                    <p class="price"><?= htmlspecialchars($item['standard_price']) ?> руб.</p>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-custom add-to-cart" 
                                data-item-id="<?= $item['id'] ?>"
                                data-price="<?= htmlspecialchars($item['standard_price']) ?>">
                            Добавить в корзину
                        </button>
                    <?php else: ?>
                        <button class="btn btn-custom" 
                                data-bs-toggle="modal" 
                                data-bs-target="#loginModal"
                                data-bs-dismiss="modal">
                            Войдите, чтобы добавить в корзину
                        </button>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
        </div>
    </main>

    <?php include "../components-page/footer.php"; ?>

    <!-- Подключение JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="../js/script.js"></script>
    <script src="../js/script-modal.js"></script>
    
</body>
</html>