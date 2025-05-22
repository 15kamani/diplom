
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Создаем контейнер для сообщений, если его нет
            let alertContainer = document.getElementById('formAlertContainer');
            if (!alertContainer) {
                alertContainer = document.createElement('div');
                alertContainer.id = 'formAlertContainer';
                registerForm.prepend(alertContainer);
            }
            alertContainer.innerHTML = '';
            
            // Показываем загрузку
            const submitBtn = registerForm.querySelector('.btn-submit');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Отправка...';
            
            // Собираем данные формы
            const formData = new FormData(registerForm);
            
            // Отправляем AJAX-запрос
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const errorAlert = doc.querySelector('.alert-danger');
                const successAlert = doc.querySelector('.alert-success');
                
                if (successAlert) {
                    // Успешная регистрация
                    alertContainer.innerHTML = successAlert.outerHTML;
                    registerForm.reset();
                    
                    // Закрываем модальное окно через 3 секунды
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
                    }, 3000);
                    
                    // Удаляем уведомление через 10 секунд
                    setTimeout(() => {
                        alertContainer.innerHTML = '';
                    }, 10000);
                    
                } else if (errorAlert) {
                    // Ошибки валидации
                    alertContainer.innerHTML = errorAlert.outerHTML;
                    
                    // Удаляем уведомление через 10 секунд
                    setTimeout(() => {
                        alertContainer.innerHTML = '';
                    }, 10000);
                    
                } else {
                    // Неизвестная ошибка
                    alertContainer.innerHTML = '<div class="alert alert-danger">Произошла неизвестная ошибка</div>';
                    
                    // Удаляем уведомление через 10 секунд
                    setTimeout(() => {
                        alertContainer.innerHTML = '';
                    }, 10000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertContainer.innerHTML = '<div class="alert alert-danger">Ошибка при отправке формы</div>';
                
                // Удаляем уведомление через 10 секунд
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 10000);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                
                // Прокручиваем к сообщению
                if (alertContainer.innerHTML) {
                    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });
    }

    // Функция для автоматического скрытия всех alert'ов через 10 секунд
    function setupAutoDismissAlerts() {
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                
                // Полное удаление после завершения анимации
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 10000);
        });
    }

    // Вызываем функцию при появлении новых alert'ов
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                setupAutoDismissAlerts();
            }
        });
    });

    // Начинаем наблюдение за изменениями в document.body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Элементы управления
    const btnToggleUpload = document.querySelector('.btn-toggle-upload');
    const uploadContainer = document.querySelector('.avatar-upload-container');
    const cancelBtn = document.querySelector('.cancel-upload');
    const avatarForm = document.getElementById('avatarForm');
    const avatarInput = document.getElementById('avatarInput');
    const avatarImage = document.querySelector('.avatar-image');
    
    // Показ/скрытие формы загрузки
    if (btnToggleUpload && uploadContainer) {
        btnToggleUpload.addEventListener('click', function() {
            uploadContainer.style.display = uploadContainer.style.display === 'block' ? 'none' : 'block';
        });
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                uploadContainer.style.display = 'none';
                avatarForm.reset();
            });
        }
    }
    
    // Обработка отправки формы через AJAX
    if (avatarForm) {
        avatarForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(avatarForm);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload(); // Перезагружаем страницу после успешной загрузки
                } else {
                    alert('Ошибка при загрузке аватара');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка');
            });
        });
    }
    
    // Превью аватара перед загрузкой
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    avatarImage.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});