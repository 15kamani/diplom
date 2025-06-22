<?php
// Компонент карточки напитка
?>
<div class="card">
    <?php if (!empty($item['image'])): ?>
        <img src="../<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
    <?php else: ?>
        <?php if (isset($item['type']) && $item['type'] === 'tea'): ?>
            <img src="../img/menu/drinks/default-tea.png" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
        <?php else: ?>
            <img src="../img/menu/drinks/default.png" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
        <p class="card-text"><?= htmlspecialchars($item['short_desc']) ?></p>
        
        <!-- <?php if (!empty($item['sizes'])): ?>
            <p class="card-text"><strong>Цена: <?= htmlspecialchars($item['sizes']) ?></strong></p>
        <?php elseif (!empty($item['varieties'])): ?>
            <p class="card-text"><strong>Цена: <?= htmlspecialchars($item['varieties']) ?></strong></p>
        <?php endif; ?> -->
        
        <button type="button" class="btn btn-custom" data-bs-toggle="modal"
                data-bs-target="#drinkModal-<?= $item['id'] ?>">
            Подробнее
        </button>
    </div>
</div>