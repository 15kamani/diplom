      // Регистрация и авторизация

      document.getElementById('order-present').addEventListener('submit', function (event){
        alert('Аккаунт создан!');
    });

        // Получаем элементы модальных окон и кнопок
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const openLoginModalBtn = document.getElementById('openLoginModal');
        const openRegisterModalLink = document.getElementById('openRegisterModalLink');
        const closeButtons = document.querySelectorAll('.close');
        
        // Открытие модального окна входа
        openLoginModalBtn.addEventListener('click', () => {
            loginModal.style.display = 'block';
        });
        
        // Открытие модального окна регистрации
        openRegisterModalLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.style.display = 'none';
            registerModal.style.display = 'block';
        });
        
        
        // Закрытие модальных окон при клике вне их области
        window.addEventListener('click', (event) => {
            if (event.target === loginModal) {
                loginModal.style.display = 'none';
            }
            if (event.target === registerModal) {
                registerModal.style.display = 'none';
            }
        });
        
        // Обработка формы входа
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Вход выполнен!');
            loginModal.style.display = 'none';
        });
        
        // Обработка формы регистрации
        document.getElementById('registerForm').addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Регистрация успешна!');
            registerModal.style.display = 'none';
        });

        document.addEventListener('DOMContentLoaded', function() {
    // Находим кнопку закрытия
    const closeButton = document.querySelector('.btn-close');
    
    // Находим модальное окно
    const modal = document.getElementById('loginModal', 'registerModal');

    // Добавляем обработчик события на клик
    closeButton.addEventListener('click', function() {
        // Скрываем модальное окно
        modal.style.display = 'none';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Находим кнопку закрытия
    const closeButton = document.querySelector('#registerModal .btn-close');
    
    // Находим модальное окно регистрации
    const registerModal = document.getElementById('registerModal');

    // Добавляем обработчик события на клик
    closeButton.addEventListener('click', function() {
        // Скрываем модальное окно
        registerModal.style.display = 'none';
    });
});