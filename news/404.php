<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новость не найдена | Реестр Гарант</title>
    <meta name="description" content="Запрашиваемая новость не найдена. Перейдите к списку всех новостей.">
    
    <!-- Фавиконы -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- Стили -->
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    <link rel="stylesheet" href="news-styles.css">
</head>
<body>
    <!-- Подключение шапки -->
    <div data-include="/header.html"></div>

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <a href="/">Главная</a>
            <span>→</span>
            <a href="/news/">Новости</a>
            <span>→</span>
            <span>404</span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="news-content">
        <div class="container">
            <div class="error-404">
                <div class="error-icon">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="#667eea" stroke-width="2"/>
                        <path d="M15 9l-6 6" stroke="#667eea" stroke-width="2" stroke-linecap="round"/>
                        <path d="M9 9l6 6" stroke="#667eea" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <h1>Новость не найдена</h1>
                <p>К сожалению, запрашиваемая новость не существует или была удалена.</p>
                
                <div class="error-actions">
                    <a href="/news/" class="btn btn-primary">Все новости</a>
                    <a href="/" class="btn btn-secondary">На главную</a>
                </div>
                
                <!-- Поиск -->
                <div class="error-search">
                    <h3>Попробуйте найти интересную новость:</h3>
                    <form method="GET" action="/news/" class="search-form">
                        <input type="text" name="search" placeholder="Введите ключевые слова...">
                        <button type="submit">🔍</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Подключение футера -->
    <div data-include="/footer.php"></div>

    <!-- Подключение JavaScript файлов -->
    <script src="/include.js"></script>
    <script src="/script.js"></script>

    <style>
    .error-404 {
        text-align: center;
        padding: 80px 20px;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .error-icon {
        margin-bottom: 30px;
        opacity: 0.6;
    }
    
    .error-404 h1 {
        font-size: 2.5rem;
        color: #2c3e50;
        margin-bottom: 20px;
    }
    
    .error-404 p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 40px;
        line-height: 1.6;
    }
    
    .error-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-bottom: 60px;
        flex-wrap: wrap;
    }
    
    .error-search {
        background: #f8f9fa;
        padding: 40px;
        border-radius: 15px;
        border: 1px solid #e9ecef;
    }
    
    .error-search h3 {
        color: #2c3e50;
        margin-bottom: 25px;
        font-size: 1.3rem;
    }
    
    .error-search .search-form {
        max-width: 400px;
        margin: 0 auto;
    }
    
    @media (max-width: 768px) {
        .error-404 h1 {
            font-size: 2rem;
        }
        
        .error-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .error-search {
            padding: 30px 20px;
        }
    }
    </style>
</body>
</html>