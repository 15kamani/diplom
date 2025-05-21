<?php
session_start();

// Удаляем все данные сессии
$_SESSION = array();

// Удаляем сессионные куки
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Удаляем куки "запомнить меня"
setcookie('remember_token', '', time() - 3600, '/');

// Перенаправляем на главную
header("Location: index.php");
exit();
?>