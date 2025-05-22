<?php
// Получаем последнее добавленное событие из базы данных
$event = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if ($event):
?>
    <!-- Текстовый блок события -->
    <div class="special-event">
        <div class="container">
            <div class="event-text hidden">
                <h2><?= htmlspecialchars($event['title']) ?></h2>
                <p class="garmond-1"><?= nl2br(htmlspecialchars($event['short_description'])) ?></p>
                
                <?php 
                // Разбиваем подробное описание на пункты
                $lines = explode("\n", $event['detailed_description']);
                foreach ($lines as $line):
                    if (!empty(trim($line))):
                ?>
                    <p>- <?= htmlspecialchars(trim($line)) ?></p>
                <?php 
                    endif;
                endforeach; 
                ?>
                
                <a href="<?= htmlspecialchars($event['event_url']) ?>"><button class="btn btn-custom">Подробности ></button></a>
            </div>

            <!-- Карусель с изображениями -->
            <div id="carouselExample" class="carousel slide hidden" data-bs-ride="carousel">
                <!-- Индикаторы -->
                <ol class="carousel-indicators">
                    <li data-bs-target="#carouselExample" data-bs-slide-to="0" class="active"></li>
                    <li data-bs-target="#carouselExample" data-bs-slide-to="1"></li>
                    <li data-bs-target="#carouselExample" data-bs-slide-to="2"></li>
                    <li data-bs-target="#carouselExample" data-bs-slide-to="3"></li>
                </ol>
                <div class="carousel-inner">
                    <?php
                    $first = true;
                    for ($i = 1; $i <= 4; $i++):
                        if (!empty($event["image{$i}"])):
                    ?>
                        <div class="carousel-item <?= $first ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($event["image{$i}"]) ?>" class="d-block w-100" alt="Image <?= $i ?>">
                        </div>
                    <?php
                            $first = false;
                        endif;
                    endfor;
                    ?>
                </div>

                <!-- Стрелочки (кнопки навигации) -->
                <a class="carousel-control-prev" href="#carouselExample" role="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExample" role="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </a>
            </div>
        </div>
    </div>    
<?php
endif;
?>