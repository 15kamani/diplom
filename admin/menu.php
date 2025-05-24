<?php
session_start();
require '../components/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// Обработка удаления
if ($action === 'delete' && $id > 0) {
    // Удаляем изображение если есть
    $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if ($item && !empty($item['image']) && file_exists('../' . $item['image'])) {
        unlink('../' . $item['image']);
    }
    
    // Удаляем запись (каскадно удалит связанные варианты)
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = 'Позиция меню успешно удалена!';
    header('Location: menu.php?page=menu');
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $title = trim($_POST['title']);
    $short_desc = trim($_POST['short_desc']);
    $full_desc = trim($_POST['full_desc'] ?? '');
    $standard_price = !empty($_POST['standard_price']) ? (float)$_POST['standard_price'] : null;
    $id = $_POST['id'] ?? 0;
    
    // Обработка загрузки изображения
    $image_path = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/menu/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('menu_') . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Удаляем старое изображение
                if (!empty($image_path) && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                $image_path = 'uploads/menu/' . $file_name;
            }
        }
    }
    
    // Сохранение основной информации
    if (empty($title) || empty($short_desc)) {
        $error = 'Заполните обязательные поля (название и краткое описание)';
    } else {
        if ($id > 0) {
            // Редактирование
            $stmt = $pdo->prepare("UPDATE menu_items SET 
                category = ?, image = ?, title = ?, short_desc = ?, full_desc = ?, standard_price = ? 
                WHERE id = ?");
            $stmt->execute([$category, $image_path, $title, $short_desc, $full_desc, $standard_price, $id]);
            $message = 'Позиция меню обновлена!';
        } else {
            // Добавление
            $stmt = $pdo->prepare("INSERT INTO menu_items 
                (category, image, title, short_desc, full_desc, standard_price) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category, $image_path, $title, $short_desc, $full_desc, $standard_price]);
            $id = $pdo->lastInsertId();
            $message = 'Новая позиция добавлена в меню!';
        }
        
        // Обработка напитков (кофе/чай)
        if ($category === 'drinks') {
            // Удаляем старые варианты напитков
            $pdo->prepare("DELETE FROM menu_drinks WHERE menu_item_id = ?")->execute([$id]);
            
            // Добавляем новые варианты
            if (!empty($_POST['drink_type'])) {
                foreach ($_POST['drink_type'] as $index => $type) {
                    $price = (float)$_POST['drink_price'][$index];
                    
                    if ($type === 'coffee') {
                        $volume = (int)$_POST['coffee_volume'][$index];
                        $pdo->prepare("INSERT INTO menu_drinks 
                            (menu_item_id, type, volume_ml, price) 
                            VALUES (?, ?, ?, ?)")
                            ->execute([$id, $type, $volume, $price]);
                    } else { // tea
                        $variety = trim($_POST['tea_variety'][$index]);
                        $pdo->prepare("INSERT INTO menu_drinks 
                            (menu_item_id, type, tea_variety, price) 
                            VALUES (?, ?, ?, ?)")
                            ->execute([$id, $type, $variety, $price]);
                    }
                }
            }
        }
        
        // Обработка вариантов новинок
        if ($category === 'new') {
            // Удаляем старые варианты
            $pdo->prepare("DELETE FROM menu_new_variants WHERE menu_item_id = ?")->execute([$id]);
            
            // Добавляем новые варианты
            if (!empty($_POST['new_variant_name'])) {
                foreach ($_POST['new_variant_name'] as $index => $name) {
                    $price = (float)$_POST['new_variant_price'][$index];
                    $name = trim($name);
                    
                    if (!empty($name)) {
                        $pdo->prepare("INSERT INTO menu_new_variants 
                            (menu_item_id, variant_name, price) 
                            VALUES (?, ?, ?)")
                            ->execute([$id, $name, $price]);
                    }
                }
            }
        }
        
        $_SESSION['message'] = $message;
        header('Location: menu.php?page=menu');
        exit;
    }
}

// Получение данных для редактирования
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        $_SESSION['error'] = 'Позиция меню не найдена!';
        header('Location: menu.php?page=menu');
        exit;
    }
    
    // Получаем варианты напитков если есть
    if ($item['category'] === 'drinks') {
        $drinks = $pdo->prepare("SELECT * FROM menu_drinks WHERE menu_item_id = ?");
        $drinks->execute([$id]);
        $item['drinks'] = $drinks->fetchAll();
    }
    
    // Получаем варианты новинок если есть
    if ($item['category'] === 'new') {
        $variants = $pdo->prepare("SELECT * FROM menu_new_variants WHERE menu_item_id = ?");
        $variants->execute([$id]);
        $item['variants'] = $variants->fetchAll();
    }
}

// Получение списка позиций для отображения
$items = $pdo->query("SELECT * FROM menu_items ORDER BY category, title")->fetchAll();

// Получаем все варианты напитков и новинок
$allDrinks = $pdo->query("SELECT * FROM menu_drinks")->fetchAll();
$allVariants = $pdo->query("SELECT * FROM menu_new_variants")->fetchAll();

// Группируем варианты по ID позиции меню
$drinkGroups = $variantGroups = [];
foreach ($allDrinks as $drink) {
    $drinkGroups[$drink['menu_item_id']][] = $drink;
}
foreach ($allVariants as $variant) {
    $variantGroups[$variant['menu_item_id']][] = $variant;
}
?>

<div class="admin-menu">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if ($action === 'list' || $action === 'delete'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Управление меню</h2>
            <a href="menu.php?page=menu&action=add" class="btn btn-primary">Добавить позицию</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Категория</th>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Цены</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td>
                                <?= [
                                    'new' => 'Новинки',
                                    'drinks' => 'Напитки',
                                    'bistro' => 'Бистро/Пекарня',
                                    'gifts' => 'Подарочные наборы'
                                ][$item['category']] ?>
                            </td>
                            <td>
                                <?php if (!empty($item['image'])): ?>
                                    <img src="../<?= htmlspecialchars($item['image']) ?>" style="max-width: 50px;">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td>
                                <?php if ($item['category'] === 'drinks' && isset($drinkGroups[$item['id']])): ?>
                                    <?= htmlspecialchars(implode(' / ', array_column($drinkGroups[$item['id']], 'price'))) ?>
                                <?php elseif ($item['category'] === 'new' && isset($variantGroups[$item['id']])): ?>
                                    <?= htmlspecialchars(implode(', ', array_map(
                                        fn($v) => $v['variant_name'].' ('.$v['price'].')',
                                        $variantGroups[$item['id']]
                                    ))) ?>
                                <?php else: ?>
                                    <?= $item['standard_price'] ? htmlspecialchars($item['standard_price']) : '-' ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="menu.php?page=menu&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>
                                <a href="menu.php?page=menu&action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить эту позицию?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    
    <?php else: ?>
        <h2><?= ($action === 'add') ? 'Добавить позицию' : 'Редактировать позицию' ?></h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $item['id'] ?? 0 ?>">
            
            <?php if (isset($item['image']) && !empty($item['image'])): ?>
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($item['image']) ?>">
                <div class="mb-3">
                    <label>Текущее изображение:</label>
                    <img src="../<?= htmlspecialchars($item['image']) ?>" class="img-thumbnail" style="max-width: 200px; display: block;">
                </div>
            <?php endif; ?>
            
            <div class="form-group mb-3">
                <label for="image">Изображение:</label>
                <input type="file" id="image" name="image" class="form-control">
                <small class="form-text text-muted">Оставьте пустым, чтобы оставить текущее изображение</small>
            </div>
            
            <div class="form-group mb-3">
                <label for="category">Категория *</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="new" <?= ($item['category'] ?? '') === 'new' ? 'selected' : '' ?>>Новинки</option>
                    <option value="drinks" <?= ($item['category'] ?? '') === 'drinks' ? 'selected' : '' ?>>Напитки</option>
                    <option value="bistro" <?= ($item['category'] ?? '') === 'bistro' ? 'selected' : '' ?>>Бистро/Пекарня</option>
                    <option value="gifts" <?= ($item['category'] ?? '') === 'gifts' ? 'selected' : '' ?>>Подарочные наборы</option>
                </select>
            </div>
            
            <div class="form-group mb-3">
                <label for="title">Название *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($item['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-group mb-3">
                <label for="short_desc">Краткое описание *</label>
                <textarea id="short_desc" name="short_desc" class="form-control" rows="3" required><?= 
                    htmlspecialchars($item['short_desc'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label for="full_desc">Полное описание</label>
                <textarea id="full_desc" name="full_desc" class="form-control" rows="5"><?= 
                    htmlspecialchars($item['full_desc'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label for="standard_price">Стандартная цена</label>
                <input type="number" step="0.01" id="standard_price" name="standard_price" class="form-control" 
                       value="<?= htmlspecialchars($item['standard_price'] ?? '') ?>">
                <small class="form-text text-muted">Для напитков/новинок можно указать базовую цену или оставить пустым</small>
            </div>
            
            <!-- Блок для напитков (кофе/чай) -->
            <div id="drinks-section" class="mb-4" style="<?= ($item['category'] ?? '') === 'drinks' ? '' : 'display: none;' ?>">
                <h4>Варианты напитков</h4>
                <div id="drinks-container">
                    <?php if (($item['category'] ?? '') === 'drinks' && !empty($item['drinks'])): ?>
                        <?php foreach ($item['drinks'] as $drink): ?>
                            <div class="drink-variant mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Тип напитка</label>
                                        <select name="drink_type[]" class="form-control drink-type">
                                            <option value="coffee" <?= $drink['type'] === 'coffee' ? 'selected' : '' ?>>Кофе</option>
                                            <option value="tea" <?= $drink['type'] === 'tea' ? 'selected' : '' ?>>Чай</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3 coffee-field" style="<?= $drink['type'] === 'coffee' ? '' : 'display: none;' ?>">
                                        <label>Объем (мл)</label>
                                        <input type="number" name="coffee_volume[]" class="form-control" 
                                               value="<?= $drink['type'] === 'coffee' ? $drink['volume_ml'] : '' ?>">
                                    </div>
                                    
                                    <div class="col-md-3 tea-field" style="<?= $drink['type'] === 'tea' ? '' : 'display: none;' ?>">
                                        <label>Вид чая</label>
                                        <input type="text" name="tea_variety[]" class="form-control" 
                                               value="<?= $drink['type'] === 'tea' ? htmlspecialchars($drink['tea_variety']) : '' ?>">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label>Цена</label>
                                        <input type="number" step="0.01" name="drink_price[]" class="form-control" 
                                               value="<?= htmlspecialchars($drink['price']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-drink">×</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif (($item['category'] ?? '') === 'drinks'): ?>
                        <div class="drink-variant mb-3 p-3 border rounded">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Тип напитка</label>
                                    <select name="drink_type[]" class="form-control drink-type">
                                        <option value="coffee">Кофе</option>
                                        <option value="tea">Чай</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 coffee-field">
                                    <label>Объем (мл)</label>
                                    <input type="number" name="coffee_volume[]" class="form-control" value="250">
                                </div>
                                
                                <div class="col-md-3 tea-field" style="display: none;">
                                    <label>Вид чая</label>
                                    <input type="text" name="tea_variety[]" class="form-control">
                                </div>
                                
                                <div class="col-md-2">
                                    <label>Цена</label>
                                    <input type="number" step="0.01" name="drink_price[]" class="form-control" required>
                                </div>
                                
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-drink">×</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="button" id="add-drink" class="btn btn-secondary">Добавить вариант</button>
            </div>
            
            <!-- Блок для вариантов новинок -->
            <div id="new-variants-section" class="mb-4" style="<?= ($item['category'] ?? '') === 'new' ? '' : 'display: none;' ?>">
                <h4>Варианты новинок</h4>
                <div id="new-variants-container">
                    <?php if (($item['category'] ?? '') === 'new' && !empty($item['variants'])): ?>
                        <?php foreach ($item['variants'] as $variant): ?>
                            <div class="new-variant mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label>Название варианта</label>
                                        <input type="text" name="new_variant_name[]" class="form-control" 
                                               value="<?= htmlspecialchars($variant['variant_name']) ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <label>Цена</label>
                                        <input type="number" step="0.01" name="new_variant_price[]" class="form-control" 
                                               value="<?= htmlspecialchars($variant['price']) ?>" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-new-variant">×</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif (($item['category'] ?? '') === 'new'): ?>
                        <div class="new-variant mb-3 p-3 border rounded">
                            <div class="row">
                                <div class="col-md-5">
                                    <label>Название варианта</label>
                                    <input type="text" name="new_variant_name[]" class="form-control">
                                </div>
                                <div class="col-md-5">
                                    <label>Цена</label>
                                    <input type="number" step="0.01" name="new_variant_price[]" class="form-control" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-new-variant">×</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="button" id="add-new-variant" class="btn btn-secondary">Добавить вариант</button>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="menu.php?page=menu" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
        
        <script>
        // Показываем/скрываем блоки при изменении категории
        document.getElementById('category').addEventListener('change', function() {
            document.getElementById('drinks-section').style.display = 
                this.value === 'drinks' ? 'block' : 'none';
            document.getElementById('new-variants-section').style.display = 
                this.value === 'new' ? 'block' : 'none';
        });
        
        // Управление вариантами напитков
        function initDrinkEvents(element) {
            element.querySelector('.remove-drink').addEventListener('click', function() {
                element.remove();
            });
            
            element.querySelector('.drink-type').addEventListener('change', function() {
                const isCoffee = this.value === 'coffee';
                element.querySelector('.coffee-field').style.display = isCoffee ? 'block' : 'none';
                element.querySelector('.tea-field').style.display = isCoffee ? 'none' : 'block';
            });
        }
        
        // Добавление нового варианта напитка
        document.getElementById('add-drink').addEventListener('click', function() {
            const container = document.getElementById('drinks-container');
            const newDrink = document.createElement('div');
            newDrink.className = 'drink-variant mb-3 p-3 border rounded';
            newDrink.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <label>Тип напитка</label>
                        <select name="drink_type[]" class="form-control drink-type">
                            <option value="coffee">Кофе</option>
                            <option value="tea">Чай</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 coffee-field">
                        <label>Объем (мл)</label>
                        <input type="number" name="coffee_volume[]" class="form-control" value="250">
                    </div>
                    
                    <div class="col-md-3 tea-field" style="display: none;">
                        <label>Вид чая</label>
                        <input type="text" name="tea_variety[]" class="form-control">
                    </div>
                    
                    <div class="col-md-2">
                        <label>Цена</label>
                        <input type="number" step="0.01" name="drink_price[]" class="form-control" required>
                    </div>
                    
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-drink">×</button>
                    </div>
                </div>
            `;
            container.appendChild(newDrink);
            initDrinkEvents(newDrink);
        });
        
        // Инициализация существующих вариантов напитков
        document.querySelectorAll('.drink-variant').forEach(initDrinkEvents);
        
        // Управление вариантами новинок
        function initNewVariantEvents(element) {
            element.querySelector('.remove-new-variant').addEventListener('click', function() {
                element.remove();
            });
        }
        
        // Добавление нового варианта новинки
        document.getElementById('add-new-variant').addEventListener('click', function() {
            const container = document.getElementById('new-variants-container');
            const newVariant = document.createElement('div');
            newVariant.className = 'new-variant mb-3 p-3 border rounded';
            newVariant.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <label>Название варианта</label>
                        <input type="text" name="new_variant_name[]" class="form-control">
                    </div>
                    <div class="col-md-5">
                        <label>Цена</label>
                        <input type="number" step="0.01" name="new_variant_price[]" class="form-control" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-new-variant">×</button>
                    </div>
                </div>
            `;
            container.appendChild(newVariant);
            initNewVariantEvents(newVariant);
        });
        
        // Инициализация существующих вариантов новинок
        document.querySelectorAll('.new-variant').forEach(initNewVariantEvents);
        </script>
    <?php endif; ?>
</div>