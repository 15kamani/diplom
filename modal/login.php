<?php
// Первая строка - session_start(), никаких пробелов!

// Подключение БД (убедитесь, что в db_connect.php нет вывода)
require_once __DIR__ . '/../components/db_connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['loginUsername']);
    $password = trim($_POST['loginPassword']);
    $remember = isset($_POST['rememberMe']);

    if (empty($username) || empty($password)) {
        $errors[] = 'Все поля обязательны для заполнения';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 60*60*24*30, '/');
                    $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([$token, $user['id']]);
                }

echo '<script>window.location.href = "' . ($user['username'] === 'admin' ? 'admin.php' : 'profile.php') . '";</script>';
exit();
                exit(); // Обязательно завершаем скрипт
            } else {
                $errors[] = 'Неверный логин или пароль';
            }
        } catch (PDOException $e) {
            $errors[] = 'Ошибка при авторизации: ' . $e->getMessage();
        }
    }
}
?>
<!-- HTML-код модального окна -->

<div class="modal fade" id="LoginModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><h2 class="garmond-1">Вход</h2></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form id="loginForm" method="POST" action="">
                    <input type="hidden" name="login" value="1">
                    <div class="div-forms">
                        <div class="div-form">
                            <label for="loginUsername">Логин:</label>
                            <input type="text" id="loginUsername" name="loginUsername" class="form-control" required>
                        </div>
                        <div class="div-form">
                            <label for="loginPassword">Пароль:</label>
                            <input type="password" id="loginPassword" name="loginPassword" class="form-control" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
                            <label class="form-check-label" for="rememberMe">Запомнить меня</label>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Войти</button>
                    <p class="text-custom-1" style="margin-top: 3%;">Нет аккаунта? <a href="#" data-bs-target="#registerModal" data-bs-toggle="modal">Зарегистрироваться</a></p>
                </form>
            </div>
        </div>
    </div>
</div>