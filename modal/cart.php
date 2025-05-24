<?php


$cartItems = $_SESSION['cart'] ?? [];
$totalSum = 0;

// Получаем полную информацию о товарах
if (!empty($cartItems)) {
    $itemIds = array_column($cartItems, 'item_id');
    $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
    
    $stmt = $pdo->prepare("SELECT id, title, image FROM menu_items WHERE id IN ($placeholders)");
    $stmt->execute($itemIds);
    $itemsInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Преобразуем в ассоциативный массив с id в качестве ключа
    $itemsInfo = array_reduce($itemsInfo, function($carry, $item) {
        $carry[$item['id']] = [
            'title' => $item['title'],
            'image' => $item['image']
        ];
        return $carry;
    }, []);
    
    // Добавляем информацию о товарах в корзину
    foreach ($cartItems as &$item) {
        if (isset($itemsInfo[$item['item_id']])) {
            $item['title'] = $itemsInfo[$item['item_id']]['title'];
            $item['image'] = $itemsInfo[$item['item_id']]['image'];
        } else {
            $item['title'] = 'Неизвестный товар';
            $item['image'] = 'img/default.png';
        }
        $totalSum += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Корзина</title>
    <!-- Подключение стилей -->
</head>
<body>
    
    <main class="container my-5">
        <h1>Ваша корзина</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">Ваша корзина пуста</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Вариант</th>
                            <th>Цена</th>
                            <th>Количество</th>
                            <th>Сумма</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $index => $item): ?>
                            <tr>
                                <td>
                                    <img src="../<?= htmlspecialchars($item['image']) ?>" width="50" alt="<?= htmlspecialchars($item['title']) ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                </td>
                                <td><?= htmlspecialchars($item['variant'] ?? 'Стандарт') ?></td>
                                <td><?= htmlspecialchars($item['price']) ?> руб.</td>
                                <td>
                                    <input type="number" class="form-control quantity-input" 
                                           value="<?= $item['quantity'] ?>" min="1" 
                                           data-index="<?= $index ?>" style="width: 70px;">
                                </td>
                                <td><?= $item['price'] * $item['quantity'] ?> руб.</td>
                                <td>
                                    <button class="btn btn-danger btn-sm remove-from-cart" 
                                            data-index="<?= $index ?>">
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Итого:</th>
                            <th><?= $totalSum ?> руб.</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="text-end">
                <a href="/checkout.php" class="btn btn-primary">Оформить заказ</a>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
    // JavaScript для управления корзиной
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const index = this.dataset.index;
            const quantity = parseInt(this.value);
            
            fetch('/api/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    index: index,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            });
        });
    });
    
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Удалить товар из корзины?')) {
                const index = this.dataset.index;
                
                fetch('/api/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        index: index
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>