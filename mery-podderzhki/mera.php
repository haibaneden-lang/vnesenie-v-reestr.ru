<?php
/**
 * Детальная SEO-страница одной меры поддержки
 * URL: /navigator-mer-podderzhki-gisp/{slug}
 */
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    header('HTTP/1.0 404 Not Found');
    include(__DIR__ . '/../news/404.php');
    exit;
}

$data_file = dirname(__DIR__) . '/data/all_measures_combined.json';
if (!is_readable($data_file)) {
    header('HTTP/1.0 503 Service Unavailable');
    echo 'Данные временно недоступны.';
    exit;
}
$all = json_decode(file_get_contents($data_file), true);
if (!is_array($all)) {
    header('HTTP/1.0 503 Service Unavailable');
    echo 'Ошибка загрузки данных.';
    exit;
}

$measure = null;
foreach ($all as $m) {
    if (isset($m['slug']) && $m['slug'] === $slug) {
        $measure = $m;
        break;
    }
}
if (!$measure) {
    header('HTTP/1.0 404 Not Found');
    include(__DIR__ . '/../news/404.php');
    exit;
}

// Похожие меры из той же секции (до 3)
$same_section = array_values(array_filter($all, function ($m) use ($measure) {
    return isset($m['section'], $measure['section']) && $m['section'] === $measure['section'] && ($m['slug'] ?? '') !== ($measure['slug'] ?? '');
}));
$same_section = array_slice($same_section, 0, 3);

$title = $measure['seo_title'] ?? ($measure['title_normal'] ?? '') . ' | РеестрГарант';
$description = $measure['seo_description'] ?? '';
$canonical = 'https://vnesenie-v-reestr.ru/navigator-mer-podderzhki-gisp/' . $slug;
$price_label = $measure['consulting_price']['label'] ?? 'по запросу';
$steps = $measure['how_to_get_steps'] ?? [];
$why_us = $measure['why_us_points'] ?? [];
$intro = mb_substr(strip_tags(str_replace(["\n", '**'], [' ', ''], $measure['full_description'] ?? '')), 0, 300) . '…';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical); ?>">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    <style>
        .mera-bread { padding: 12px 0; font-size: 0.9rem; color: #64748b; }
        .mera-bread a { color: #1e3c72; text-decoration: none; }
        .mera-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; max-width: 1200px; margin: 0 auto; padding: 24px 20px 48px; }
        @media (max-width: 900px) { .mera-layout { grid-template-columns: 1fr; } }
        .mera-main h1 { font-size: 1.75rem; margin: 0 0 16px; color: #1e293b; line-height: 1.35; }
        .mera-badges { margin-bottom: 16px; }
        .mera-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; margin-right: 8px; margin-bottom: 6px; }
        .mera-badge-ty { background: #059669; color: #fff; }
        .mera-badge-sec { background: #e2e8f0; color: #475569; }
        .mera-intro { color: #475569; line-height: 1.7; margin-bottom: 24px; }
        .mera-meta { background: #f8fafc; border-radius: 10px; padding: 16px; margin-bottom: 24px; font-size: 0.9rem; }
        .mera-meta p { margin: 0 0 8px; }
        .mera-meta p:last-child { margin-bottom: 0; }
        .mera-table { width: 100%; border-collapse: collapse; margin: 24px 0; font-size: 0.9rem; }
        .mera-table th, .mera-table td { padding: 10px 12px; text-align: left; border: 1px solid #e2e8f0; }
        .mera-table th { background: #f1f5f9; width: 180px; }
        .mera-main h2 { font-size: 1.35rem; margin: 28px 0 12px; color: #1e3c72; }
        .mera-main h3 { font-size: 1.1rem; margin: 20px 0 8px; color: #334155; }
        .mera-main p, .mera-main li { color: #475569; line-height: 1.7; margin-bottom: 10px; }
        .mera-steps { list-style: none; padding: 0; margin: 16px 0; }
        .mera-steps li { padding: 10px 0 10px 36px; position: relative; border-bottom: 1px solid #f1f5f9; }
        .mera-steps li::before { content: '✓'; position: absolute; left: 0; color: #059669; font-weight: bold; }
        .mera-aside { position: sticky; top: 20px; height: fit-content; }
        .mera-form-box { background: #f0f7ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .mera-form-box h3 { margin: 0 0 12px; font-size: 1.1rem; color: #1e3c72; }
        .mera-form-box .price { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin: 8px 0; }
        .mera-form-box input, .mera-form-box textarea { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .mera-form-box button { width: 100%; padding: 14px; background: #1e3c72; color: #fff; border: 0; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .mera-form-box .contacts { margin-top: 16px; font-size: 0.9rem; color: #475569; }
        .mera-why { background: #f8fafc; border-radius: 12px; padding: 20px; margin-top: 24px; }
        .mera-why h3 { margin: 0 0 12px; font-size: 1rem; color: #1e3c72; }
        .mera-why ul { margin: 0; padding-left: 1.2em; color: #475569; font-size: 0.9rem; line-height: 1.6; }
        .mera-other { margin-top: 40px; padding-top: 24px; border-top: 1px solid #e2e8f0; }
        .mera-other h2 { margin-bottom: 16px; }
        .mera-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
        .mera-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
        .mera-card a { color: #1e3c72; text-decoration: none; font-weight: 500; }
        .mera-card a:hover { text-decoration: underline; }
        .mera-card .amount { font-size: 0.85rem; color: #64748b; margin-top: 6px; }
        .contacts-pre { white-space: pre-line; font-size: 0.9rem; color: #475569; }
    </style>
</head>
<body>
    <div data-include="/header.html"></div>

    <div class="container mera-bread">
        <a href="/">Главная</a> → <a href="/navigator-mer-podderzhki-gisp">Навигатор мер поддержки ГИСП</a> → <?php echo htmlspecialchars($measure['section'] ?? ''); ?> → <span><?php echo htmlspecialchars(mb_substr($measure['title_normal'] ?? '', 0, 50)); ?>…</span>
    </div>

    <div class="mera-layout">
        <main class="mera-main">
            <div class="mera-badges">
                <span class="mera-badge mera-badge-ty"><?php echo htmlspecialchars($measure['support_type'] ?? ''); ?></span>
                <span class="mera-badge mera-badge-sec"><?php echo htmlspecialchars($measure['section'] ?? ''); ?></span>
            </div>
            <h1><?php echo htmlspecialchars($measure['title_normal'] ?? ''); ?></h1>
            <p class="mera-intro"><?php echo htmlspecialchars($intro); ?></p>

            <div class="mera-meta">
                <?php if (!empty($measure['npa'])): ?><p><strong>НПА:</strong> <?php echo htmlspecialchars($measure['npa']); ?></p><?php endif; ?>
                <p><strong>Администратор:</strong> <?php echo htmlspecialchars($measure['administrator'] ?? '—'); ?></p>
                <?php if (!empty($measure['operator'])): ?><p><strong>Оператор:</strong> <?php echo htmlspecialchars($measure['operator']); ?></p><?php endif; ?>
                <?php if (!empty($measure['source_url'])): ?><p><strong>Источник:</strong> <a href="<?php echo htmlspecialchars($measure['source_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($measure['source_url']); ?></a></p><?php endif; ?>
            </div>

            <h2>Ключевые параметры</h2>
            <table class="mera-table">
                <tr><th>Размер</th><td><?php echo htmlspecialchars($measure['amount_formatted'] ?? '—'); ?></td></tr>
                <tr><th>Участники</th><td><?php echo htmlspecialchars($measure['participants'] ?? '—'); ?></td></tr>
                <tr><th>Цель</th><td><?php echo htmlspecialchars($measure['goal_tags'] ?? '—'); ?></td></tr>
                <tr><th>Срок</th><td><?php echo htmlspecialchars($measure['implementation_period'] ?? '—'); ?></td></tr>
                <tr><th>Тип</th><td><?php echo htmlspecialchars($measure['support_type'] ?? '—'); ?></td></tr>
            </table>

            <h2>Полное описание</h2>
            <?php if (!empty($measure['participants'])): ?><h3>Кто может получить</h3><p><?php echo nl2br(htmlspecialchars($measure['participants'])); ?></p><?php endif; ?>
            <?php if (!empty($measure['financing_direction']) || !empty($measure['financing_object'])): ?>
            <h3>На что предоставляется</h3>
            <p><?php echo nl2br(htmlspecialchars(trim($measure['financing_direction'] . ' ' . $measure['financing_object']))); ?></p>
            <?php endif; ?>
            <?php if (!empty($measure['support_size']) || !empty($measure['support_conditions'])): ?>
            <h3>Размер и условия</h3>
            <p><?php echo nl2br(htmlspecialchars(trim($measure['support_size'] . ' ' . $measure['support_conditions']))); ?></p>
            <?php endif; ?>
            <?php if (!empty($measure['criteria'])): ?><h3>Требования к заявителю</h3><p><?php echo nl2br(htmlspecialchars($measure['criteria'])); ?></p><?php endif; ?>

            <h2>Как получить <?php echo htmlspecialchars(mb_substr($measure['title_normal'] ?? '', 0, 40)); ?> — пошаговая инструкция</h2>
            <ul class="mera-steps">
                <?php foreach ($steps as $i => $step): ?>
                <li><?php echo htmlspecialchars($step); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Подсказка:</strong> Подготовка заявки требует знания специфики требований администратора программы. Специалисты РеестрГарант помогут пройти весь путь — от проверки соответствия до подписания соглашения.</p>
            <p><button type="button" onclick="typeof openModal === 'function' && openModal('consultation')" style="display:inline-block; padding:12px 24px; background:#1e3c72; color:#fff; border:0; border-radius:8px; font-weight:500; cursor:pointer;">Заказать подготовку заявки под ключ →</button></p>

            <h2>Контакты для подачи заявки</h2>
            <div class="contacts-pre"><?php echo nl2br(htmlspecialchars($measure['contacts'] ?? '—')); ?></div>
            <?php if (!empty($measure['source_url'])): ?><p><a href="<?php echo htmlspecialchars($measure['source_url']); ?>" target="_blank" rel="noopener noreferrer">Перейти на страницу программы →</a></p><?php endif; ?>
            <?php if (!empty($measure['gisp_link'])): ?><p><a href="https://gisp.gov.ru" target="_blank" rel="noopener noreferrer">Открыть в ГИСП →</a></p><?php endif; ?>

            <?php if (count($same_section) > 0): ?>
            <section class="mera-other">
                <h2>Другие меры поддержки в разделе «<?php echo htmlspecialchars($measure['section'] ?? ''); ?>»</h2>
                <div class="mera-cards">
                    <?php foreach ($same_section as $other): ?>
                    <div class="mera-card">
                        <a href="/navigator-mer-podderzhki-gisp/<?php echo htmlspecialchars($other['slug'] ?? ''); ?>"><?php echo htmlspecialchars(mb_substr($other['title_normal'] ?? '', 0, 80)); ?>…</a>
                        <div class="amount"><?php echo htmlspecialchars($other['amount_formatted'] ?? '—'); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </main>

        <aside class="mera-aside">
            <div class="mera-form-box" id="form">
                <h3>Получить помощь в получении</h3>
                <p><?php echo htmlspecialchars(mb_substr($measure['title_normal'] ?? '', 0, 45)); ?>…</p>
                <p class="price">Размер поддержки: <?php echo htmlspecialchars($measure['amount_formatted'] ?? '—'); ?></p>
                <p style="font-size:0.9rem; color:#475569;">Стоимость консалтинга по рынку: <strong><?php echo htmlspecialchars($price_label); ?></strong></p>
                <p style="font-size:0.85rem; color:#64748b;">Среднее время подготовки: 14–21 день</p>
                <button type="button" style="width:100%; padding:12px; margin-bottom:12px; background:#059669; color:#fff; border:0; border-radius:8px; font-weight:600; cursor:pointer;" onclick="typeof openModal === 'function' && openModal('consultation')">Получить консультацию</button>
                <form action="/send-email.php" method="post" id="meraLeadForm" style="margin-top:16px;">
                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($measure['title_normal'] ?? 'Консультация'); ?>">
                    <input type="hidden" name="page_url" value="<?php echo htmlspecialchars($canonical); ?>">
                    <input type="hidden" name="source" value="navigator-mera">
                    <input type="text" name="name" placeholder="Имя *" required>
                    <input type="tel" name="phone" placeholder="Телефон *" required>
                    <input type="email" name="email" placeholder="Email">
                    <input type="text" name="company" placeholder="Компания">
                    <textarea name="message" rows="3" placeholder="Комментарий"></textarea>
                    <button type="submit">Оставить заявку</button>
                </form>
                <div class="contacts">
                    <p>📞 +7 920-898-17-18</p>
                    <p>✉️ reestrgarant@mail.ru</p>
                    <p>Бесплатная первичная консультация</p>
                    <p>Оценка шансов на получение</p>
                    <p>Подготовка документов под ключ</p>
                </div>
            </div>
            <div class="mera-why">
                <h3>Почему РеестрГарант?</h3>
                <ul>
                    <?php foreach ($why_us as $point): ?>
                    <li><?php echo htmlspecialchars($point); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
    </div>

    <div data-include="/footer.html"></div>
    <script src="/js/modal.js"></script>
    <script>(function(){ var orig = window.openModal; if (orig) { window.openModal = function(type) { orig(type); setTimeout(function(){ var s = document.getElementById('lead_service'); if (s) s.value = <?php echo json_encode($measure['title_normal'] ?? 'Консультация'); ?>; }, 200); }; } })();</script>
    <script src="/include.js"></script>
    <script src="/js/lead-form.js"></script>
</body>
</html>
