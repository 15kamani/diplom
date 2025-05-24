<?php
// Компонент карточки товара для меню
?>
<div class="card">
    <?php if (!empty($item['image'])): ?>
        <img src="../<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
    <?php else: ?>
        <img src="../img/menu/novinki/default.png" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
    <?php endif; ?>
    
    <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
        <p class="card-text"><?= htmlspecialchars($item['short_desc']) ?></p>
        
        <?php if (!empty($item['standard_price'])): ?>
            <p class="card-text"><strong>Цена: <?= htmlspecialchars($item['standard_price']) ?> руб.</strong></p>
        <?php endif; ?>
        
        <button type="button" class="btn btn-custom" data-bs-toggle="modal"
                data-bs-target="#exampleModal-novinki-<?= $item['id'] ?>">
            Подробнее
        </button>
    </div>
</div>