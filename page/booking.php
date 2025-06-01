<?php
session_start();
require_once '../components/db_connect.php';

$isAuthenticated = isset($_SESSION['user_id']);
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
                    <div class="table table-1" data-table="0">
                        <img src="../img/booking/chear.png" alt="" class="chear-1">
                        <span>Столик 1</span>
                        <img src="../img/booking/chear.png" alt="" class="chear-2">
                    </div>
                    <div class="table table-2" data-table="1">
                        <img src="../img/booking/chear.png" alt="" class="chear-1">
                        <span>Столик 2</span>
                        <img src="../img/booking/chear.png" alt="" class="chear-2">
                    </div>
                    <div class="table table-3" data-table="2">
                        <img src="../img/booking/chear.png" alt="" class="chear-1">
                        <span>Столик 3</span>
                        <img src="../img/booking/chear.png" alt="" class="chear-2">
                    </div>
                    <div class="table table-entrance" data-table="3">
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
                    
                <form id="bookingForm" action="../components/booking_handler.php" method="POST">
                    <input type="hidden" name="reservation_type" value="table">
                    
                    <div class="mb-3">
                        <label for="tableNumber" class="form-label garmond-1">Выберите столик:</label>
                        <select class="form-select garmond-0" name="tableNumber" id="tableNumber" required>
                            <option value="0">Столик 1</option>
                            <option value="1">Столик 2</option>
                            <option value="2">Столик 3</option>
                            <option value="3">Столик 4</option>
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
                <!-- Форма бронирования зала -->
                <form id="bookingHallForm" action="../components/booking_handler.php" method="POST">
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
                        <input type="number" class="form-control" name="guests" id="hallGuests" min="1" max="80" required>
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
        // Общие элементы
        const tables = document.querySelectorAll(".table");
        const tableNumberSelect = document.getElementById("tableNumber");
        const guestsInput = document.getElementById("guests");
        const dateInput = document.getElementById("date");
        const timeInput = document.getElementById("time");

        // Инициализация при загрузке
        initTableSelection();
        initFormSubmissions();
        if (dateInput && timeInput) initAvailabilityChecks();

        // Функция инициализации выбора столиков
        function initTableSelection() {
            if (!tables.length || !tableNumberSelect || !guestsInput) return;
            
            tables.forEach(table => {
                table.addEventListener("click", function() {
                    const tableId = this.getAttribute("data-table");
                    tableNumberSelect.value = tableId;
                    guestsInput.max = tableId === "3" ? 4 : 6;
                    
                    tables.forEach(t => t.classList.remove("selected"));
                    this.classList.add("selected");
                });
            });
        }

        // Функция инициализации отправки форм
        function initFormSubmissions() {
            const bookingForm = document.getElementById('bookingForm');
            const bookingHallForm = document.getElementById('bookingHallForm');
            
            if (bookingForm) {
                bookingForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    await handleFormSubmit(this, 'Столик успешно забронирован!');
                });
            }

            if (bookingHallForm) {
                bookingHallForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    await handleFormSubmit(this, 'Зал успешно забронирован!');
                });
            }
        }

        // Функция инициализации проверки доступности
        function initAvailabilityChecks() {
            dateInput.addEventListener('change', function() {
                updateTableAvailability();
                checkHallAvailability(this.value);
            });

            timeInput.addEventListener('change', updateTableAvailability);
            
            if (dateInput.value) {
                updateTableAvailability();
                checkHallAvailability(dateInput.value);
            }
        }

        // Общая функция обработки отправки формы
        async function handleFormSubmit(form, successMessage) {
            try {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON response:", text);
                    throw new Error("Ошибка обработки ответа сервера");
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Ошибка сервера');
                }

                showSuccess(data.message || successMessage, form);
            } catch (error) {
                showError(error);
            } finally {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        // Остальные функции остаются без изменений...
        async function updateTableAvailability() {
            try {
                if (!dateInput.value || !timeInput.value) return;
                
                const response = await fetch(`../components/availability.php?date=${dateInput.value}&time=${timeInput.value}`);
                
                if (!response.ok) {
                    throw new Error('Ошибка при проверке доступности');
                }

                const data = await response.json();
                updateTableStatuses(data.bookedTables || []);
                updateHallStatus(data.isHallBooked || false, dateInput.value);
                
            } catch (error) {
                console.error('Ошибка при обновлении доступности:', error);
            }
        }

            // Добавьте в функцию initFormSubmissions()
            if (bookingHallForm) {
                bookingHallForm.addEventListener('submit', async function(e) {
                    // Простая валидация перед отправкой
                    if (!this.eventType.value) {
                        e.preventDefault();
                        alert('Пожалуйста, выберите тип мероприятия');
                        return;
                    }
                    
                    e.preventDefault();
                    await handleFormSubmit(this, 'Зал успешно забронирован!');
                });
            }

        async function checkHallAvailability(date) {
            try {
                if (!date) return;
                
                const response = await fetch(`../components/availability.php?date=${date}`);
                
                if (!response.ok) {
                    throw new Error('Ошибка при проверке зала');
                }

                const data = await response.json();
                if (data.isHallBooked) {
                    alert("На выбранную дату зал уже забронирован. Бронирование столиков невозможно.");
                }
            } catch (error) {
                console.error('Ошибка при проверке зала:', error);
            }
        }

        function updateTableStatuses(bookedTables) {
            tables.forEach(table => {
                const tableNumber = table.dataset.table;
                if (bookedTables.includes(parseInt(tableNumber))) {
                    table.classList.add('booked');
                    table.title = "Этот столик занят или временной интервал менее 2 часов";
                } else {
                    table.classList.remove('booked');
                    table.title = "";
                }
            });
        }

        function updateHallStatus(isBooked, date) {
            const hallTitle = document.querySelector('#bookingHallModal .modal-title');
            if (!hallTitle) return;

            hallTitle.innerHTML = isBooked 
                ? 'Бронирование зала <span class="text-danger">(Зал занят на эту дату)</span>'
                : 'Бронирование зала';

            const formElements = document.querySelectorAll('#bookingForm input, #bookingForm select, #bookingForm button');
            formElements.forEach(el => {
                if (el.id === 'date' && el.value === date) {
                    el.disabled = isBooked;
                }
            });
        }

        function showSuccess(message, form) {
            alert(message);
            const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
            if (modal) modal.hide();
            form.reset();
            
            if (form.id === 'bookingForm') {
                tables.forEach(t => t.classList.remove("selected"));
            }
        }

        function showError(error) {
            console.error('Error:', error);
            alert(error.message || 'Произошла ошибка при отправке формы');
        }
    });
</script>
</body>
</html>