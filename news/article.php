<?php
require_once __DIR__ . '/../models/News.php';

// Получаем slug из URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('HTTP/1.0 404 Not Found');
    header('Location: /news/');
    exit;
}

// Инициализация
$newsModel = new News();
$categoryModel = new NewsCategory();

// Получаем новость
$news = $newsModel->getNewsBySlug($slug);

if (!$news) {
    header('HTTP/1.0 404 Not Found');
    include('404.php');
    exit;
}

// Получаем похожие новости
$related_news = [];
if ($news['category_id']) {
    $related_news = $newsModel->getRelatedNews($news['category_id'], $news['id'], 4);
}

// Мета-данные для SEO
$page_title = $news['title'] . ' | Реестр Гарант';
$meta_description = $news['meta_description'] ?: 
    (mb_strlen($news['excerpt']) > 0 ? $news['excerpt'] : 
    mb_substr(strip_tags($news['content']), 0, 160));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    
    <!-- Open Graph для соцсетей -->
    <meta property="og:title" content="<?php echo htmlspecialchars($news['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://vnesenie-v-reestr.ru/news/<?php echo htmlspecialchars($news['slug']); ?>">
    <?php if ($news['featured_image']): ?>
    <meta property="og:image" content="https://vnesenie-v-reestr.ru<?php echo htmlspecialchars($news['featured_image']); ?>">
    <?php endif; ?>
    
    <!-- Структурированные данные -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "headline": "<?php echo addslashes($news['title']); ?>",
        "description": "<?php echo addslashes($meta_description); ?>",
        "datePublished": "<?php echo date('c', strtotime($news['published_at'])); ?>",
        "dateModified": "<?php echo date('c', strtotime($news['updated_at'])); ?>",
        "author": {
            "@type": "Organization",
            "name": "Реестр Гарант"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Реестр Гарант",
            "logo": {
                "@type": "ImageObject",
                "url": "https://vnesenie-v-reestr.ru/logo.png"
            }
        }
        <?php if ($news['featured_image']): ?>
        ,"image": "https://vnesenie-v-reestr.ru<?php echo addslashes($news['featured_image']); ?>"
        <?php endif; ?>
    }
    </script>
    
    <!-- Фавиконы -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- Стили -->
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    <link rel="stylesheet" href="/news/news-styles.css">
    <?php $article_css_v = @filemtime(__DIR__ . '/article-styles.css') ?: time(); ?>
    <link rel="stylesheet" href="/news/article-styles.css?v=<?php echo $article_css_v; ?>">
    <link rel="stylesheet" href="/news/registry-benefits.css">
     <link rel="stylesheet" href="/news/article-responsive.css?v=<?php echo $article_css_v; ?>">
</head>
<body>
    <!-- Подключение шапки -->
    <div data-include="../header.html"></div>

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <a href="/">Главная</a>
            <span>→</span>
            <a href="/news/">Новости</a>
            <?php if ($news['category_name']): ?>
                <span>→</span>
                <a href="/news/?category=<?php echo $news['category_id']; ?>">
                    <?php echo htmlspecialchars($news['category_name']); ?>
                </a>
            <?php endif; ?>
            <span>→</span>
            <span><?php echo htmlspecialchars($news['title']); ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="article-content">
        <div class="container">
            <div class="article-layout">
                <!-- Article -->
                <article class="article-main">
                    <header class="article-header">
                        <?php if ($news['category_name']): ?>
                            <div class="article-category">
                                <a href="/news/?category=<?php echo $news['category_id']; ?>">
                                    <?php echo htmlspecialchars($news['category_name']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <h1><?php echo htmlspecialchars($news['h1']); ?></h1>
                        
                        <div class="article-meta">
                            <time datetime="<?php echo date('c', strtotime($news['published_at'])); ?>">
                                <?php echo date('d.m.Y в H:i', strtotime($news['published_at'])); ?>
                            </time>
                            <span class="article-views">
                                👁 <?php echo number_format($news['views_count']); ?>
                            </span>
                        </div>
                        
                        <?php if ($news['excerpt']): ?>
                            <div class="article-excerpt">
                                <?php echo htmlspecialchars($news['excerpt']); ?>
                            </div>
                        <?php endif; ?>
                    </header>

                    <?php if ($news['featured_image']): ?>
                        <div class="article-image">
                            <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="article-body">
                        <?php echo $news['content']; ?>
                    </div>

                    <footer class="article-footer">
                        <div class="article-tags">
                            <?php if ($news['category_name']): ?>
                                <a href="/news/?category=<?php echo $news['category_id']; ?>" class="tag">
                                    <?php echo htmlspecialchars($news['category_name']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="article-share">
                            <span>Поделиться:</span>
                            <a href="https://vk.com/share.php?url=<?php echo urlencode('https://vnesenie-v-reestr.ru/news/' . $news['slug']); ?>" 
                               target="_blank" class="share-btn vk">VK</a>
                            <a href="https://t.me/share/url?url=<?php echo urlencode('https://vnesenie-v-reestr.ru/news/' . $news['slug']); ?>&text=<?php echo urlencode($news['title']); ?>" 
                               target="_blank" class="share-btn telegram">Telegram</a>
                            <a href="https://wa.me/?text=<?php echo urlencode($news['title'] . ' https://vnesenie-v-reestr.ru/news/' . $news['slug']); ?>" 
                               target="_blank" class="share-btn whatsapp">WhatsApp</a>
                        </div>
                    </footer>
                </article>

                <!-- Sidebar -->
                <aside class="article-sidebar">
                    <!-- CTA виджет - скрыт на мобильных -->
                    <div class="sidebar-widget cta-widget desktop-only">
                        <h3>Нужна помощь?</h3>
                        <p>Получите бесплатную консультацию по включению в реестр</p>
                        <a href="tel:+79208981718" class="cta-phone">+7 920-898-17-18</a>
                        <a href="mailto:reestrgarant@mail.ru" class="cta-email">reestrgarant@mail.ru</a>
                        <button class="btn btn-primary" onclick="openModal('consultation')" 
                                style="width: 100%; margin-top: 15px;">
                            Получить консультацию
                        </button>
                    </div>

                    <?php if (!empty($related_news)): ?>
                        <!-- Похожие новости -->
                        <div class="sidebar-widget">
                            <h3>Похожие новости</h3>
                            <div class="related-news">
                                <?php foreach ($related_news as $related): ?>
                                    <div class="related-item">
                                        <?php if ($related['featured_image']): ?>
                                            <div class="related-image">
                                                <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($related['title']); ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="related-content">
                                            <h4>
                                                <a href="/news/<?php echo htmlspecialchars($related['slug']); ?>">
                                                    <?php echo htmlspecialchars($related['title']); ?>
                                                </a>
                                            </h4>
                                            <time><?php echo date('d.m.Y', strtotime($related['published_at'])); ?></time>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>

            <!-- Мобильный CTA блок -->
            <div class="mobile-cta mobile-only">
                <div class="mobile-cta-content">
                    <h3>Нужна консультация?</h3>
                    <div class="mobile-cta-buttons">
                        <a href="tel:+79208981718" class="mobile-btn mobile-btn-phone">
                            📞 Позвонить
                        </a>
                        <button class="mobile-btn mobile-btn-form" onclick="openModal('consultation')">
                            💬 Написать
                        </button>
                    </div>
                </div>
            </div>
        
<section class="services-block" style="padding: 40px 0; background-color: #f9f9f9;">
  <div class="container">
    <h2 style="font-size: 28px; margin-bottom: 24px; text-align: center;">Наши услуги</h2>
    <div class="services-grid" style="
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    ">
      <a href="/industrial" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">🏭</div>
        <strong>Промышленная продукция</strong>
        <span style="font-size: 14px; color: #666;">Реестр Минпромторга</span>
      </a>
      <a href="/radioelectronic" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">📡</div>
        <strong>Радиоэлектронная продукция</strong>
        <span style="font-size: 14px; color: #666;">В реестр Минпромторга</span>
      </a>
      <a href="/software" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">💾</div>
        <strong>Российское ПО</strong>
        <span style="font-size: 14px; color: #666;">В реестр ПО</span>
      </a>
      <a href="/medical-devices" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">🩺</div>
        <strong>Медицинские изделия</strong>
        <span style="font-size: 14px; color: #666;">Минпромторг</span>
      </a>
      <a href="/telecom-equipment" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">📶</div>
        <strong>Телеком оборудование</strong>
        <span style="font-size: 14px; color: #666;">Реестр отечественного</span>
      </a>
      <a href="/oil-gas-equipment" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">⛽</div>
        <strong>Нефтегазовое оборудование</strong>
        <span style="font-size: 14px; color: #666;">Включение в реестр</span>
      </a>
      <a href="/roskomnadzor-registration" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">🛡️</div>
        <strong>Регистрация в РКН</strong>
        <span style="font-size: 14px; color: #666;">Персональные данные</span>
      </a>
      <a href="/roskomnadzor-preparation-expanded" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">📋</div>
        <strong>Подготовка к проверке</strong>
        <span style="font-size: 14px; color: #666;">Роскомнадзор</span>
      </a>
      <a href="/tendernoe-soprovozhdenie" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">📑</div>
        <strong>Тендерное сопровождение</strong>
        <span style="font-size: 14px; color: #666;">Помощь в тендерах</span>
      </a>
      <a href="/vnesenie-v-reestr-turoperatorov" class="service-item" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background: #fff; transition: 0.3s;">
        <div style="font-size: 28px; margin-bottom: 8px;">🏖️</div>
        <strong>Реестр туроператоров</strong>
        <span style="font-size: 14px; color: #666;">Добавление компаний</span>
      </a>
    </div>
  </div>
</section>

    </main>

    <!-- Подключение модального окна -->
    <div data-include="../modal.html"></div>

    <!-- Подключение футера -->
    <div data-include="../footer.html"></div>

    <!-- Подключение JavaScript файлов -->
    <script src="/include.js"></script>
    <script src="/script.js"></script>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ERQ4KXJHET"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-ERQ4KXJHET');
</script>
</body>
</html>