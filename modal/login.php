<!-- Модальное окно входа -->
<div class="modal fade" id="LoginModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><h2 class="garmond-1">Вход</h2></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="div-forms">
                        <div class="div-form">
                            <label for="loginUsername">Логин:</label>
                            <input type="text" id="loginUsername" name="loginUsername" required>
                        </div>
                        <div class="div-form">
                            <label for="loginPassword">Пароль:</label>
                            <input type="password" id="loginPassword" name="loginPassword" required>
                        </div>
                        <label>
                            <input type="checkbox" id="rememberMe"> Запомнить меня
                        </label>
                    </div>
                    <button type="submit" class="btn-submit">Войти</button>
                    <p class="text-custom-1" style="margin-top: 3%;">Нет аккаунта? <a href="#" data-bs-target="#registerModal" data-bs-toggle="modal">Зарегистрироваться</a></p>
                </form>
            </div>
        </div>
    </div>
</div>