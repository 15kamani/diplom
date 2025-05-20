let lastScrollTop = 0;
const buttomMenu = document.querySelector('.buttom-menu');
const topMenuHeight = document.querySelector('.top-menu').offsetHeight;

window.addEventListener('scroll', function() {
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > lastScrollTop) {
        buttomMenu.classList.add('hidden');
    } else {
        buttomMenu.classList.remove('hidden');
    }

    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});

// Получаем кнопку
const scrollToTopButton = document.getElementById('scrollToTop');

// Показываем кнопку при прокрутке вниз
window.onscroll = function () {
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        scrollToTopButton.style.display = "block";
    } else {
        scrollToTopButton.style.display = "none";
    }
};

// Плавная прокрутка вверх
scrollToTopButton.onclick = function () {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};




// Нахождение необходимых элементов
const button = document.querySelector('.hover-button');
const owlTooltip = document.querySelector('.owl-tooltip');

// Добавляем обработчик клика на кнопку
button.addEventListener('click', () => {
    // Проверяем текущий текст кота и меняем его
    if (owlTooltip.textContent === '🦉') {
        owlTooltip.textContent = '🦅'; // Заменяем на другое эмодзи (например, орел)
    } else {
        owlTooltip.textContent = '🦉'; // Возвращаем обратно совушку
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const elements = document.querySelectorAll(".hidden");

    elements.forEach((element, index) => {
        setTimeout(() => {
            element.classList.add("visible");
        }, index * 600); // Задержка 700 мс между элементами
    });
});

const burgerMenu = document.getElementById('burger-menu');
const mobileMenu = document.getElementById('mobile-menu');

burgerMenu.addEventListener('click', () => {
    mobileMenu.classList.toggle('active');
});

// JavaScript для меню
document.addEventListener("DOMContentLoaded", function () {
    const navbarToggler = document.querySelector(".navbar-toggler");
    const navbarCollapse = document.querySelector(".navbar-collapse");

    navbarToggler.addEventListener("click", function () {
        navbarCollapse.classList.toggle("show");
    });
});

const swiper = new Swiper('#cardCarousel', '#cardCarousel-1', '#cardCarousel-2','#cardCarousel-3', '#cardCarousel-4',{
    slidesPerView: 4,
    spaceBetween: 10,
    navigation: {
        nextEl: '.carousel-control-next',
        prevEl: '.carousel-control-prev',
    },
    breakpoints: {
        320: {
            slidesPerView: 1,
        },
        768: {
            slidesPerView: 2,
        },
        1024: {
            slidesPerView: 4,
        },
    },
});

// копирование текста и вылезающее оповещение
function copyText(element) {
    // Создаем временный элемент textarea для копирования текста
    const tempTextArea = document.createElement('textarea');
    tempTextArea.value = element.textContent;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand('copy');
    document.body.removeChild(tempTextArea);

    // Показываем подсказку
    const tooltip = document.getElementById('tooltip');
    tooltip.classList.add('show');

    // Скрываем подсказку через 2 секунды
    setTimeout(() => {
        tooltip.classList.remove('show');
    }, 2000);
}

// Для бронирвоания столиков 

    document.addEventListener("DOMContentLoaded", function () {
    const tables = document.querySelectorAll(".table");
    const tableNumberSelect = document.getElementById("tableNumber");
    const guestsInput = document.getElementById("guests");

    // Обработка выбора столика
    tables.forEach(table => {
        table.addEventListener("click", function () {
            const tableId = this.getAttribute("data-table");
            tableNumberSelect.value = tableId;

            // Установка максимального количества гостей
            if (tableId === "3") {
                guestsInput.max = 4;
            } else {
                guestsInput.max = 6;
            }

            // Подсветка выбранного столика
            tables.forEach(t => t.classList.remove("selected"));
            this.classList.add("selected");
        });
    });

    // Обработка отправки формы
    const bookingForm = document.getElementById("bookingForm");
    bookingForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const tableNumber = tableNumberSelect.value;
        const guests = guestsInput.value;
        const date = document.getElementById("date").value;
        const time = document.getElementById("time").value;

        // Заглушка для отправки данных
        console.log("Данные бронирования:", {
            tableNumber,
            guests,
            date,
            time,
        });

        alert("Столик успешно забронирован!  С уважение, Кофе с СоВой <3");
        bookingForm.reset();
        tables.forEach(t => t.classList.remove("selected"));
    });
});

// Для бронирования зала
document.addEventListener("DOMContentLoaded", function () {
    const bookingHallForm = document.getElementById("bookingHallForm");

    // Обработка отправки формы
    bookingHallForm.addEventListener("submit", function (e) {
        e.preventDefault();

        // Сбор данных из формы
        const formData = {
            fullName: document.getElementById("fullName").value,
            phone: document.getElementById("phone").value,
            email: document.getElementById("email").value,
            eventType: document.getElementById("eventType").value,
            guests: document.getElementById("guests").value,
            date: document.getElementById("date").value,
            time: document.getElementById("time").value,
            comments: document.getElementById("comments").value,
        };

        // Заглушка для отправки данных
        console.log("Данные бронирования:", formData);

        // Оповещение об успешной отправке
        alert("Зал успешно забронирован! Мы свяжемся с вами для подтверждения.");
        bookingHallForm.reset(); // Очистка формы
    });
});


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

// О НАС
let currentReview = 0;
const reviews = document.querySelectorAll(".review-card");

// Функция для показа текущего отзыва
function showReview(index) {
    reviews.forEach((review, i) => {
        if (i === index) {
            review.classList.add("active");
        } else {
            review.classList.remove("active");
        }
    });
}

// Функция для перехода к следующему отзыву
function nextReview() {
    currentReview = (currentReview + 1) % reviews.length;
    showReview(currentReview);
}

// Функция для перехода к предыдущему отзыву
function prevReview() {
    currentReview = (currentReview - 1 + reviews.length) % reviews.length;
    showReview(currentReview);
}

// Показываем первый отзыв при загрузке страницы
showReview(currentReview);


        // Получаем элементы
        const zoomableImage = document.querySelector('.zoomable-image');
        const overlay = document.getElementById('overlay');
        const fullscreenImage = document.getElementById('fullscreen-image');
        const closeBtn = document.getElementById('close-btn');
        
        // Открытие картинки в полноэкранном режиме
        zoomableImage.addEventListener('click', () => {
            fullscreenImage.src = zoomableImage.src; // Устанавливаем источник картинки
            overlay.style.display = 'flex'; // Показываем оверлей
        });
        
        // Закрытие полноэкранного режима
        closeBtn.addEventListener('click', () => {
            overlay.style.display = 'none'; // Скрываем оверлей
        });
        
        // Закрытие при клике вне картинки
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                overlay.style.display = 'none';
            }
        });

