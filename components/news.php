<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Последние новости</title>

</head>
<body>
    <div class="novinki-nedeli">
        <h1>Новинки недели</h1>
        <div class="cards-novinki">
                    
                    <?php
                    
                    $stmt = $pdo->query("SELECT * FROM news ORDER BY date DESC, id DESC LIMIT 3");
                    $latestNews = $stmt->fetchAll();
                    ?>
                    
                    <?php if (empty($latestNews)): ?>
                        <div class="alert alert-info">Скоро будут новые новости!</div>
                    <?php else: ?>
                        <div class="buttom-cards hidden">
                            <?php foreach ($latestNews as $news): ?>
                                <div class="col-md-4">
                                    <div class="card hidden" style="width: 18rem;">
                                        <?php if (!empty($news['image'])): ?>
                                            <img src="<?= htmlspecialchars($news['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>">
                                        <?php else: ?>
                                            <img src="images/default-news.jpg" class="card-img-top" alt="Новость">
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
                                            <p class="card-text garmond-0"><?= htmlspecialchars($news['short_desc']) ?></p>
                                            <div class="mt-auto">
                                                <p class="card-text">
                                                    <small class="text-body-secondary">
                                                        <?= date('d.m.Y', strtotime($news['date'])) ?>
                                                    </small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>