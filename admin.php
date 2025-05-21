<?php
session_start();

// Проверяем, вошёл ли пользователь и является ли он 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    // Если не админ, перенаправляем на главную или страницу входа
    header("Location: index.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Админ-панель</h1>
        <p>Добро пожаловать, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
        <p>Это страница только для администратора.</p>
        <a href="logout.php" class="btn btn-danger">Выйти</a>
    </div>
</body>
</html>