<?php
// Подключение к базе данных (убедитесь, что этот файл существует и содержит корректные данные)
require_once 'components/db_connect.php';

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Получение данных из формы
    $full_name = $_POST['registerFullName'];
    $username = $_POST['registerUsername'];
    $phone = $_POST['registerPhone'];
    $email = $_POST['registerEmail'];
    $password = $_POST['registerPassword'];
    $confirm_password = $_POST['registerConfirmPassword'];
    $agree_to_terms = isset($_POST['agreeToTerms']) ? 1 : 0;
    
    // Валидация данных
    $errors = [];
    
    if (empty($full_name)) $errors[] = "ФИО обязательно для заполнения";
    if (empty($username)) $errors[] = "Логин обязателен для заполнения";
    if (empty($phone)) $errors[] = "Номер телефона обязателен для заполнения";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Некорректный email";
    if (empty($password)) $errors[] = "Пароль обязателен для заполнения";
    if ($password !== $confirm_password) $errors[] = "Пароли не совпадают";
    if (!$agree_to_terms) $errors[] = "Необходимо согласие на обработку персональных данных";
    
    // Если ошибок нет, регистрируем пользователя
    if (empty($errors)) {
        // Хеширование пароля
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Подготовка SQL запроса
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, phone, email, password, agree_to_terms, created_at) 
                              VALUES (:full_name, :username, :phone, :email, :password, :agree_to_terms, NOW())");
        
        // Выполнение запроса
        try {
            $stmt->execute([
                ':full_name' => $full_name,
                ':username' => $username,
                ':phone' => $phone,
                ':email' => $email,
                ':password' => $hashed_password,
                ':agree_to_terms' => $agree_to_terms
            ]);
            
            // Регистрация успешна
            $success_message = "Регистрация прошла успешно! Теперь вы можете войти.";
        } catch (PDOException $e) {
            // Ошибка при регистрации
            if ($e->getCode() == 23000) { // Ошибка дублирования уникального поля
                $errors[] = "Пользователь с таким логином или email уже существует";
            } else {
                $errors[] = "Ошибка при регистрации: " . $e->getMessage();
            }
        }
    }
}
?>

<!-- Модальное окно регистрации -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><h2 class="garmond-1">Регистрация</h2></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php else: ?>
                    <form id="registerForm" method="POST" action="">
                        <input type="hidden" name="register" value="1">
                        <div class="div-forms">
                            <div class="div-form">
                                <label for="registerFullName">ФИО<span class="red">*</span>:</label>
                                <input type="text" id="registerFullName" name="registerFullName" required 
                                       value="<?php echo isset($_POST['registerFullName']) ? htmlspecialchars($_POST['registerFullName']) : ''; ?>">
                            </div>
                            <div class="div-form">
                                <label for="registerUsername">Логин<span class="red">*</span>:</label>
                                <input type="text" id="registerUsername" name="registerUsername" required 
                                       value="<?php echo isset($_POST['registerUsername']) ? htmlspecialchars($_POST['registerUsername']) : ''; ?>">
                            </div>
                            <div class="div-form">
                                <label for="registerPhone">Номер телефона<span class="red">*</span>:</label>
                                <input type="tel" id="registerPhone" name="registerPhone" required 
                                       value="<?php echo isset($_POST['registerPhone']) ? htmlspecialchars($_POST['registerPhone']) : ''; ?>">
                            </div>
                            <div class="div-form">
                                <label for="registerEmail">Почта:</label>
                                <input type="email" id="registerEmail" name="registerEmail" required 
                                       value="<?php echo isset($_POST['registerEmail']) ? htmlspecialchars($_POST['registerEmail']) : ''; ?>">
                            </div>
                            <div class="div-form">
                                <label for="registerPassword">Пароль<span class="red">*</span>:</label>
                                <input type="password" id="registerPassword" name="registerPassword" required>
                            </div>
                            <div class="div-form">
                                <label for="registerConfirmPassword">Подтверждение пароля<span class="red">*</span>:</label>
                                <input type="password" id="registerConfirmPassword" name="registerConfirmPassword" required>
                            </div>
                            <label>
                                <input type="checkbox" id="agreeToTerms" name="agreeToTerms" required 
                                    <?php echo (isset($_POST['agreeToTerms']) && $_POST['agreeToTerms']) ? 'checked' : ''; ?>> 
                                Согласен на обработку персональных данных<span class="red">*</span>
                            </label>
                        </div>
                        <button type="submit" class="btn-submit">Зарегистрироваться</button>
                        <p class="text-custom-1" style="margin-top: 3%;">Есть аккаунт? <a href="#" data-bs-target="#LoginModal" data-bs-toggle="modal">Войти</a></p>
                        <span><span class="red">*</span> - поля, обязательные для заполнения</span>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>