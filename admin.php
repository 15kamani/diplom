<?php
session_start();
require 'components/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    echo "<script>window.location.href = 'index.php';</script>";
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
    <link rel="icon" href="img/favicon.png" type="image/x-icon">
    <style>
        :root {
            --dark: #24211C;
            --accent: #c0875c;
            --light: #f7eabd;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 3rem;
        }
        
        .admin-header {
            border-bottom: 2px solid var(--accent);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-around;
        }
        
        .admin-title {
            color: var(--dark);
            font-weight: 700;
        }
        
        .admin-welcome {
            color: var(--accent);
            font-size: 1.2rem;
        }
        
        .btn-admin {
            background-color: var(--accent);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-admin:hover {
            background-color: #a57352;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-logout {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-logout:hover {
            background-color: #bb2d3b;
        }
        
        .admin-section {
            margin-bottom: 3rem;
            padding: 1.5rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .section-title {
            color: var(--accent);
            border-left: 4px solid var(--accent);
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .buttom-menu {
            display: none;
        }
        
        .admin-veber{
            display: flex;
            justify-content: space-evenly;
        }

        .btn-custom {
    background-color: #c0875c;
    /* Основной цвет */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    color: #ffffff;
    /* Цвет текста (темный для контраста) */
    font-size: 18px;
    padding: 1%;
    border-radius: 10px;
    text-decoration: none;
}

.btn-custom:hover {
    background-color: #702f27;
    /* Цвет при наведении (немного темнее) */
    border-color: #c0875c;
    /* Цвет границы при наведении */
    color: #e0d4a8;
    /* Цвет текста при наведении */
}

        /* Адаптивность */
        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    
    <div class="container admin-container">
        <div class="admin-header">
            <div class="info-header">
                <h1 class="admin-title">Административная панель</h1>
                <p class="admin-welcome">Добро пожаловать, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
                <p class="text-muted">Управление контентом и настройками сайта</p>
                <a href="logout.php" class="btn btn-logout">Выйти из системы</a>
            </div>
            <div class="logo-header">
                <img src="img/header/logo.jpg" alt="" style="border-radius: 40%;">
            </div>
        </div>
        <div class="admin-veber">
            <a href="admin/event.php" class="btn-custom">ИВЕНТ</a>
            <a href="admin/news.php" class="btn-custom">БЛОГ</a>
            <a href="admin/menu.php" class="btn-custom">МЕНЮ</a>
            <a href="admin/galery.php" class="btn-custom">ГАЛЕРЕЯ</a>
            <a href="admin/review.php" class="btn-custom">ОТЗЫВЫ</a>
        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>