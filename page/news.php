<?php
session_start();
require_once '../components/db_connect.php';

// Получаем текущий год и месяц
$currentYear = date('Y');
$currentMonth = date('m');

// Функция для получения русскоязычного названия месяца
function getRussianMonthName($month) {
    $months = [
        '01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель',
        '05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август',
        '09' => 'Сентябрь', '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'
    ];
    return $months[$month] ?? '';
}

// Получаем список месяцев, за которые есть новости
$monthsWithNews = $pdo->query("
    SELECT DISTINCT DATE_FORMAT(date, '%m') as month 
    FROM news 
    WHERE YEAR(date) = $currentYear 
    ORDER BY month DESC
")->fetchAll(PDO::FETCH_COLUMN);

// Получаем данные для всех вкладок
$tabs = [
    'fresh-news' => [
        'title' => 'Свежие новости',
        'query' => "SELECT * FROM news ORDER BY date DESC LIMIT 6"
    ],
    'current-month' => [
        'title' => getRussianMonthName($currentMonth),
        'query' => "SELECT * FROM news WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth ORDER BY date DESC"
    ],
    'previous-month-1' => [
        'title' => getRussianMonthName(str_pad($currentMonth-1, 2, '0', STR_PAD_LEFT)),
        'query' => "SELECT * FROM news WHERE YEAR(date) = $currentYear AND MONTH(date) = ".($currentMonth-1)." ORDER BY date DESC"
    ],
    'previous-month-2' => [
        'title' => getRussianMonthName(str_pad($currentMonth-2, 2, '0', STR_PAD_LEFT)),
        'query' => "SELECT * FROM news WHERE YEAR(date) = $currentYear AND MONTH(date) = ".($currentMonth-2)." ORDER BY date DESC"
    ],
    'year-news' => [
        'title' => 'За год',
        'query' => "SELECT * FROM news WHERE YEAR(date) = $currentYear AND MONTH(date) NOT IN ($currentMonth, ".($currentMonth-1).", ".($currentMonth-2).") ORDER BY date DESC"
    ],
    'archive-news' => [
        'title' => 'Архивные новости',
        'query' => "SELECT * FROM news WHERE YEAR(date) < $currentYear ORDER BY date DESC"
    ]
];

// Загружаем данные для каждой вкладки
foreach ($tabs as $key => &$tab) {
    $tab['news'] = $pdo->query($tab['query'])->fetchAll();
}
unset($tab);
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
    <link rel="stylesheet" href="../css/style-menu.css">
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

        <main id="novinki-1">

            <div class="kroshka">
                <p><a href="../index.php">Главная</a> > <a href="#">Новости</a></p>
            </div>
            <div id="tooltip" class="tooltip">Номер скопирован!</div>

<div class="container my-5">
    <h1>Новости</h1>
    
    <!-- Переключатели вкладок -->
    <div class="d-flex justify-content-start mb-4 flex-wrap">
        <?php foreach ($tabs as $id => $tab): ?>
            <?php if (!empty($tab['news'])): ?>
                <button class="btn btn-custom me-2 mb-2 <?= $id === 'fresh-news' ? 'active' : '' ?>" 
                        data-tab="<?= $id ?>">
                    <?= $tab['title'] ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Вкладки с новостями -->
    <?php foreach ($tabs as $id => $tab): ?>
        <?php if (!empty($tab['news'])): ?>
            <div id="<?= $id ?>" class="news-tab <?= $id === 'fresh-news' ? 'active' : '' ?>">
                <div class="row">
                    <?php foreach ($tab['news'] as $news): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <?php if (!empty($news['image'])): ?>
                                    <img src="../<?= htmlspecialchars($news['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>">
                                <?php else: ?>
                                    <img src="../img/news/default-news.png" class="card-img-top" alt="Новость">
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-custom-0"><?= htmlspecialchars($news['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($news['short_desc']) ?></p>
                                    <button class="btn btn-custom mt-auto" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#newsModal<?= $news['id'] ?>">
                                        Подробнее
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- Модальные окна для новостей -->
<?php foreach ($tabs as $tab): ?>
    <?php foreach ($tab['news'] as $news): ?>
        <div class="modal fade" id="newsModal<?= $news['id'] ?>" tabindex="-1" 
             aria-labelledby="newsModal<?= $news['id'] ?>Label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-custom-1" id="newsModal<?= $news['id'] ?>Label">
                            <?= htmlspecialchars($news['title']) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body content-news">
                        <?php if (!empty($news['image'])): ?>
                            <img src="../<?= htmlspecialchars($news['image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="img-modal-card-news">
                        <?php endif; ?>
                        
                        <div class="text-content-news">
                            <?= nl2br(htmlspecialchars($news['full_desc'] ?: $news['short_desc'])) ?>
                        </div>
                        <div class="date-span">
                            <span><?= date('d.m.Y', strtotime($news['date'])) ?></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>

        <!-- Модальные окна для новостей -->
        <!-- Модальное окно 1 -->
        <div class="modal fade" id="newsModal1" tabindex="-1" aria-labelledby="newsModal1Label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-custom-1" id="newsModal1Label">#COFFEESSOVOY35</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body content-news">
                        <img src="../img/news/card-1.png" alt="">
                        <div class="text-content-news">
                            <p>Вручение приза победителю за хештеги #COFFEESSOVOY35 в ВК/ инстаграме</p>
                            <p class="text-custom-0">Поздравляем 💚💚💚</p>
                        </div>
                        <div class="date-span">
                            <span>08.02.2025</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
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
        // НОВОСТНОЙ ПОТОК
        document.addEventListener("DOMContentLoaded", function () {
            const tabButtons = document.querySelectorAll("[data-tab]");
            const tabs = document.querySelectorAll(".news-tab");

            // Обработка клика по кнопкам вкладок
            tabButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const targetTab = this.getAttribute("data-tab");

                    // Убираем активный класс у всех кнопок и вкладок
                    tabButtons.forEach(btn => btn.classList.remove("active"));
                    tabs.forEach(tab => tab.classList.remove("active"));

                    // Добавляем активный класс выбранной кнопке и вкладке
                    this.classList.add("active");
                    document.getElementById(targetTab).classList.add("active");
                });
            });
        });
    </script>
    <script>
// Скрипт для переключения вкладок
document.querySelectorAll('[data-tab]').forEach(btn => {
    btn.addEventListener('click', function() {
        // Удаляем активный класс у всех кнопок и вкладок
        document.querySelectorAll('.btn-custom').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.news-tab').forEach(t => t.classList.remove('active'));
        
        // Добавляем активный класс к текущей кнопке и вкладке
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});
</script>

</body>
</html>