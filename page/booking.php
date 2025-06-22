<?php
session_start();
require_once '../components/db_connect.php';

// Упрощенная проверка аутентификации
$userLoggedIn = isset($_SESSION['user_id']);

// Обработка бронирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_type'])) {
    $errors = validateBookingData($_POST, $userLoggedIn);
    
    if (empty($errors)) {
        $result = saveBooking($_POST, $userLoggedIn);
        if ($result['success']) {
            echo json_encode($result);
            exit;
        }
        $errors[] = $result['message'];
    }
    
    echo json_encode([
        'success' => false,
        'message' => implode('<br>', $errors)
    ]);
    exit;
}

// Проверка доступности
if (isset($_GET['date'])) {
    checkAvailability($_GET);
    exit;
}

// Основные функции
function validateBookingData($data, $isAuthenticated) {
    global $pdo;
    $errors = [];
    $requiredFields = ['date', 'time', 'guests'];
    
    // Проверка обязательных полей
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) $errors[] = "Поле {$field} обязательно для заполнения";
    }
    
    // Валидация типа бронирования
    if ($data['reservation_type'] === 'table') {
        if (!isset($data['tableNumber'])) $errors[] = 'Не выбран столик';
        if ($data['tableNumber'] == 4 && $data['guests'] > 4) {
            $errors[] = 'Столик 4 вмещает только 4 гостя';
        } elseif ($data['tableNumber'] != 4 && $data['guests'] > 6) {
            $errors[] = 'Столики 1-3 вмещают только 6 гостей';
        }

        // Проверка на бронь зала на эту дату
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
            WHERE date = ? AND reservation_type = 'hall'");
        $stmt->execute([$data['date']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'На выбранную дату забронирован зал, бронь столиков невозможна';
        }

        // Проверка на занятость столика
        if (!isset($errors['tableNumber'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                WHERE date = ? 
                AND reservation_type = 'table' 
                AND table_number = ?
                AND ABS(TIMEDIFF(time, ?)) < '02:00:00'");
            $stmt->execute([$data['date'], $data['tableNumber'], $data['time']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Этот столик уже забронирован на выбранное время';
            }
        }
        
    } elseif ($data['reservation_type'] === 'hall') {
        if ($data['guests'] > 80) {
            $errors[] = 'Зал вмещает максимум 80 гостей';
        }

        // Проверка на брони столиков на эту дату
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
            WHERE date = ? AND reservation_type = 'table'");
        $stmt->execute([$data['date']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'На выбранную дату есть брони столиков, бронь зала невозможна';
        }

        // Проверка на бронь зала
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
            WHERE date = ? AND reservation_type = 'hall'");
        $stmt->execute([$data['date']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Зал уже забронирован на выбранную дату';
        }
    }
    
    // Проверка данных для неавторизованных пользователей
    if (!$isAuthenticated) {
        if (empty($data['name'])) $errors[] = 'Имя обязательно';
        if (empty($data['phone'])) $errors[] = 'Телефон обязателен';
        if ($data['reservation_type'] === 'hall' && (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))) {
            $errors[] = 'Введите корректный email';
        }
    }
    
    return $errors;
}

function saveBooking($data, $isAuthenticated) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO reservations 
            (user_id, reservation_type, table_number, name, phone, email, event_type, guests, date, time, comments) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $isAuthenticated ? $_SESSION['user_id'] : null,
            $data['reservation_type'],
            $data['tableNumber'] ?? null,
            $data['name'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['eventType'] ?? null,
            $data['guests'],
            $data['date'],
            $data['time'],
            $data['comments'] ?? null
        ]);
        
        return [
            'success' => true,
            'message' => $data['reservation_type'] === 'table' ? 'Столик забронирован!' : 'Зал забронирован!',
            'reservationId' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при бронировании: ' . $e->getMessage()
        ];
    }
}

function checkAvailability($params) {
    global $pdo;
    
    try {
        if (isset($params['time']) && isset($params['table_number'])) {
            // Проверка конкретного столика на дату и время
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                WHERE date = :date 
                AND (
                    (reservation_type = 'table' 
                    AND table_number = :table_number
                    AND ABS(TIMEDIFF(time, :time)) < '02:00:00')
                    OR
                    (reservation_type = 'hall')
                )");
            $stmt->execute([
                'date' => $params['date'],
                'time' => $params['time'],
                'table_number' => $params['table_number']
            ]);
            $isBooked = $stmt->fetchColumn() > 0;
            echo json_encode(['isBooked' => $isBooked]);
            
        } elseif (isset($params['time'])) {
            // Проверка всех столиков на дату и время
            $stmt = $pdo->prepare("SELECT table_number FROM reservations 
                WHERE reservation_type = 'table' 
                AND date = :date 
                AND ABS(TIMEDIFF(time, :time)) < '02:00:00'");
            $stmt->execute([
                'date' => $params['date'],
                'time' => $params['time']
            ]);
            $bookedTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Проверка, не забронирован ли зал на эту дату
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                WHERE reservation_type = 'hall' 
                AND date = :date");
            $stmt->execute(['date' => $params['date']]);
            $isHallBooked = $stmt->fetchColumn() > 0;
            
            echo json_encode([
                'bookedTables' => $bookedTables,
                'isHallBooked' => $isHallBooked
            ]);
            
        } else {
            // Проверка только зала на дату
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                WHERE date = :date 
                AND reservation_type = 'hall'");
            $stmt->execute(['date' => $params['date']]);
            $isHallBooked = $stmt->fetchColumn() > 0;
            
            // Проверка, есть ли брони столиков на эту дату
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                WHERE date = :date 
                AND reservation_type = 'table'");
            $stmt->execute(['date' => $params['date']]);
            $hasTableBookings = $stmt->fetchColumn() > 0;
            
            echo json_encode([
                'isHallBooked' => $isHallBooked,
                'hasTableBookings' => $hasTableBookings
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
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
    <link rel="stylesheet" href="../css/proverka.css">
    <link rel="stylesheet" href="../css/media.css">
    <!-- Шрифты -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=EB+Garamond:ital,wght@0,400..800;1,400..800&display=swap"
        rel="stylesheet">
    <!-- Иконка -->
    <link rel="icon" href="../img/favicon.png" type="image/x-icon">
    <title>Бронирование - Кофе с СоВой</title>
</head>
<body>
    <div id="tooltip" class="tooltip">Номер скопирован!</div>
    <?php
        include "../components-page/header.php";
        include "../modal/login.php";
        include "../modal/register-for-page.php";
    ?>

    <div class="offer-booking">
        <div class="offer-text hidden h-100">
            <div class="kroshka-0">
                <p><a href="../index.php">Главная</a> > <a href="#">Меню</a></p>
            </div>
            <h2>Забронируй столик заранее для:</h2>
            <div class="icons-for-offer">
                <div class="icon-offer hidden">
                    <img src="../img/booking/friends.png" alt="">
                    <p>встречи с другом</p>
                </div>
                <div class="icon-offer hidden">
                    <img src="../img/booking/romantic-date.png" alt="">
                    <p>романтического свидания</p>
                </div>
                <div class="icon-offer hidden">
                    <img src="../img/booking/coworking.png" alt="">
                    <p>важных дел</p>
                </div>
                <div class="icon-offer hidden">
                    <img src="../img/booking/chill.png" alt="">
                    <p>чилла с самим собой</p>
                </div>
            </div>
            <div class="offer-btn hidden" id="hall_reservation">
                <button class="btn btn-custom" type="button" data-bs-toggle="modal"
                    data-bs-target="#bookingModal">Забронировать столик</button>
            </div>
            <h2>Вы так же можете забронировать целый зал!</h2>
            <h4>Проводите вместе с нами:</h4>
            <div class="icons-for-offer">
                <div class="icon-offer hidden">
                    <img src="../img/booking/birthday.png" alt="">
                    <p>дни рождения</p>
                </div>
                <div class="icon-offer hidden">
                    <img src="../img/booking/party.png" alt="">
                    <p>мероприятия для компаний</p>
                </div>
                <div class="icon-offer hidden">
                    <img src="../img/booking/game.png" alt="">
                    <p>игры</p>
                </div>
            </div>
            <div class="offer-btn hidden">
                <button class="btn btn-custom" type="button" data-bs-toggle="modal"
                    data-bs-target="#bookingHallModal">Заказать зал</button>
            </div>
        </div>
    </div>

<!-- Модальное окно для столиков -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-custom-1" id="bookingModalLabel">Бронирование столика</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="room-layout">
                    <div class="table table-1" data-table="1">
                        <img src="../img/booking/chear.png" alt="" class="chear-1">
                        <span>Столик 1</span>
                        <img src="../img/booking/chear.png" alt="" class="chear-2">
                    </div>
                    <div class="table table-2" data-table="2">
                        <img src="../img/booking/chear.png" alt="" class="chear-1">
                        <span>Столик 2</span>
                        <img src="../img/booking/chear.png" alt="" class="chear-2">
                    </div>
                    <div class="table table-3" data-table="3">
                        <img src="../img/booking/chear.png" alt="" class="chear-1">
                        <span>Столик 3</span>
                        <img src="../img/booking/chear.png" alt="" class="chear-2">
                    </div>
                    <div class="table table-entrance" data-table="4">
                        <img src="../img/booking/chear.png" alt="" class="chear-3 c3-1">
                        <img src="../img/booking/chear.png" alt="" class="chear-3 c3-2">
                        <img src="../img/booking/chear.png" alt="" class="chear-3 c3-3">
                        <img src="../img/booking/chear.png" alt="" class="chear-3 c3-4">
                        <span>Столик 4</span>
                    </div>
                </div>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="mb-3">
                        <h4>При бронировании столика без входа в аккаунт, ожидайте звонка от менеджера.</h4>
                        <p>Для сохранения истории бронирования <a href="#" data-bs-target="#registerModal" data-bs-toggle="modal">создайте</a> или <a href="#" data-bs-target="#LoginModal" data-bs-toggle="modal">войдите</a> в аккаунт.</p>
                    </div>
                <?php endif; ?>
                
                <form id="bookingForm" method="POST">
                    <input type="hidden" name="reservation_type" value="table">
                    
                    <div class="mb-3">
                        <label for="tableNumber" class="form-label garmond-1">Выберите столик:</label>
                        <select class="form-select garmond-0" name="tableNumber" id="tableNumber" required>
                            <option value="1">Столик 1</option>
                            <option value="2">Столик 2</option>
                            <option value="3">Столик 3</option>
                            <option value="4">Столик 4</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="guests" class="form-label garmond-1">Количество гостей:</label>
                        <input type="number" class="form-control" name="guests" id="guests" min="1" max="6" required>
                        <p class="garmond-0">Столики 1-3 можно забронировать на компанию 6 человек. Столик 4 только для 4 человек.</p>
                    </div>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="mb-3">
                        <label for="name" class="form-label garmond-1">Ваше имя:</label>
                        <input type="text" class="form-control" name="name" id="name" required placeholder="Ваше полное имя">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label garmond-1">Телефон:</label>
                        <input type="tel" class="form-control" name="phone" id="phone" required placeholder="+7 (999) 999-99-99">
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label garmond-1">Дата:</label>
                        <input type="date" class="form-control" name="date" id="date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="time" class="form-label garmond-1">Время:</label>
                        <input type="time" class="form-control" name="time" id="time" min="10:00" max="22:00" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom">
                            <?= isset($_SESSION['user_id']) ? 'Забронировать' : 'Отправить заявку' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для зала -->
<div class="modal fade" id="bookingHallModal" tabindex="-1" aria-labelledby="bookingHallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-custom-1" id="bookingHallModalLabel">Бронирование зала</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="mb-3">
                        <h4>При бронировании зала без входа в аккаунт, ожидайте звонка от менеджера.</h4>
                        <p>Для сохранения истории бронирования <a href="#" data-bs-target="#registerModal" data-bs-toggle="modal">создайте</a> или <a href="#" data-bs-target="#LoginModal" data-bs-toggle="modal">войдите</a> в аккаунт.</p>
                    </div>
                <?php endif; ?>
                
                <form id="bookingHallForm" method="POST">
                    <input type="hidden" name="reservation_type" value="hall">
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="mb-3">
                        <label for="fullName" class="form-label garmond-1">ФИО:</label>
                        <input type="text" class="form-control" name="name" id="fullName" placeholder="Иванов Иван Иванович" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label garmond-1">Телефон:</label>
                        <input type="tel" class="form-control" name="phone" id="phone" placeholder="+7 (999) 999-99-99" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label garmond-1">Email:</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="example@mail.com" required>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="eventType" class="form-label garmond-1">Тип мероприятия:</label>
                        <select class="form-select garmond-0" name="eventType" id="eventType" required>
                            <option value="" selected disabled>Выберите тип мероприятия</option>
                            <option value="birthday">День рождения</option>
                            <option value="meeting">Деловая встреча</option>
                            <option value="party">Вечеринка</option>
                            <option value="other">Другое</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="guests" class="form-label garmond-1">Количество гостей:</label>
                        <input type="number" class="form-control" name="guests" id="hallGuests" min="1" max="30" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label garmond-1">Дата:</label>
                        <input type="date" class="form-control" name="date" id="hallDate" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="time" class="form-label garmond-1">Время:</label>
                        <input type="time" class="form-control" name="time" id="hallTime" min="10:00" max="22:00" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comments" class="form-label garmond-1">Дополнительные пожелания:</label>
                        <textarea class="form-control" name="comments" id="comments" rows="3" placeholder="Напишите ваши пожелания"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom">
                            <?= isset($_SESSION['user_id']) ? 'Забронировать зал' : 'Отправить заявку' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
    document.addEventListener("DOMContentLoaded", function() {
        // Основные элементы
        const elements = {
            tables: document.querySelectorAll(".table"),
            tableSelect: document.getElementById("tableNumber"),
            guestsInput: document.getElementById("guests"),
            dateInput: document.getElementById("date"),
            timeInput: document.getElementById("time"),
            bookingForm: document.getElementById('bookingForm'),
            hallForm: document.getElementById('bookingHallForm')
        };

        // Инициализация
        initTableSelection();
        initForms();
        if (elements.dateInput && elements.timeInput) initAvailabilityChecks();

        function initTableSelection() {
            if (!elements.tables.length) return;
            
            elements.tables.forEach(table => {
                table.addEventListener("click", () => {
                    const tableId = table.dataset.table;
                    elements.tableSelect.value = tableId;
                    elements.guestsInput.max = tableId === "4" ? 4 : 6;
                    
                    elements.tables.forEach(t => t.classList.remove("selected"));
                    table.classList.add("selected");
                });
            });
        }

        function initForms() {
            if (elements.bookingForm) {
                elements.bookingForm.addEventListener('submit', handleSubmit);
            }
            if (elements.hallForm) {
                elements.hallForm.addEventListener('submit', handleSubmit);
            }
        }

        async function handleSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            try {
                submitBtn.disabled = true;
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    bootstrap.Modal.getInstance(form.closest('.modal'))?.hide();
                    form.reset();
                    if (form.id === 'bookingForm') {
                        elements.tables.forEach(t => t.classList.remove("selected"));
                    }
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка при отправке формы');
            } finally {
                submitBtn.disabled = false;
            }
        }

        function initAvailabilityChecks() {
            elements.dateInput.addEventListener('change', updateAvailability);
            elements.timeInput.addEventListener('change', updateAvailability);
            if (elements.dateInput.value) updateAvailability();
        }

        async function updateAvailability() {
            if (!elements.dateInput.value) return;
            
            try {
                // Проверка зала
                const hallResponse = await fetch(`?date=${elements.dateInput.value}`);
                const hallData = await hallResponse.json();
                updateHallStatus(hallData.isHallBooked);
                
                // Проверка столиков
                if (elements.timeInput.value) {
                    const tablesResponse = await fetch(`?date=${elements.dateInput.value}&time=${elements.timeInput.value}`);
                    const tablesData = await tablesResponse.json();
                    updateTablesStatus(tablesData.bookedTables || []);
                }
            } catch (error) {
                console.error('Ошибка проверки:', error);
            }
        }

        function updateTablesStatus(bookedTables) {
            elements.tables.forEach(table => {
                const isBooked = bookedTables.includes(parseInt(table.dataset.table));
                table.classList.toggle('booked', isBooked);
                table.title = isBooked ? "Столик занят" : "";
            });
        }

        function updateHallStatus(isBooked) {
            const hallTitle = document.querySelector('#bookingHallModal .modal-title');
            if (hallTitle) {
                hallTitle.innerHTML = isBooked 
                    ? 'Бронирование зала <span class="text-danger">(Зал занят)</span>'
                    : 'Бронирование зала';
            }
        }
    });

    function updateTablesStatus(bookedTables, isHallBooked) {
    elements.tables.forEach(table => {
        const tableNumber = table.dataset.table;
        const isBooked = bookedTables.includes(parseInt(tableNumber)) || isHallBooked;
        table.classList.toggle('booked', isBooked);
        table.title = isBooked ? 
            (isHallBooked ? "Зал забронирован на эту дату" : "Столик занят") : "";
    });
}

function updateHallStatus(isHallBooked, hasTableBookings) {
    const hallTitle = document.querySelector('#bookingHallModal .modal-title');
    if (hallTitle) {
        hallTitle.innerHTML = isHallBooked ? 
            'Бронирование зала <span class="text-danger">(Зал занят)</span>' :
            hasTableBookings ? 
            'Бронирование зала <span class="text-danger">(Есть брони столиков)</span>' :
            'Бронирование зала';
    }
}
</script>
</body>
</html>