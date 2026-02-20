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
        /* ----- Обёртка и сетка ----- */
        .mera-page { background: linear-gradient(180deg, #f8fafc 0%, #fff 120px); min-height: 60vh; }
        .mera-bread { padding: 16px 20px; font-size: 0.875rem; color: #64748b; max-width: 1200px; margin: 0 auto; }
        .mera-bread a { color: #1e3c72; text-decoration: none; transition: color 0.2s; }
        .mera-bread a:hover { color: #2563eb; }
        .mera-bread span { color: #334155; font-weight: 500; }
        .mera-layout { display: grid; grid-template-columns: 1fr 360px; gap: 48px; max-width: 1200px; margin: 0 auto; padding: 32px 20px 64px; }
        @media (max-width: 900px) { .mera-layout { grid-template-columns: 1fr; gap: 32px; padding: 24px 16px 48px; } }

        /* ----- Hero: заголовок и бейджи ----- */
        .mera-hero { margin-bottom: 32px; }
        .mera-badges { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .mera-badge { display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 999px; font-size: 0.8125rem; font-weight: 600; letter-spacing: 0.02em; }
        .mera-badge-ty { background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25); }
        .mera-badge-sec { background: #fff; color: #475569; border: 1.5px solid #e2e8f0; }
        .mera-main h1 { font-size: clamp(1.5rem, 4vw, 2rem); margin: 0 0 20px; color: #0f172a; line-height: 1.3; font-weight: 700; letter-spacing: -0.02em; max-width: 42ch; }
        .mera-intro { font-size: 1.0625rem; color: #475569; line-height: 1.75; margin: 0 0 32px; max-width: 65ch; }

        /* ----- Блок НПА / Администратор ----- */
        .mera-meta { background: #fff; border-radius: 16px; padding: 24px 28px; margin-bottom: 32px; font-size: 0.9375rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border-left: 4px solid #1e3c72; }
        .mera-meta p { margin: 0 0 12px; line-height: 1.6; color: #334155; }
        .mera-meta p:last-child { margin-bottom: 0; }
        .mera-meta strong { color: #1e293b; }

        /* ----- Таблица ключевых параметров ----- */
        .mera-section-title { font-size: 1.25rem; margin: 0 0 16px; color: #1e3c72; font-weight: 700; letter-spacing: -0.01em; }
        .mera-table-wrap { border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 32px; }
        .mera-table { width: 100%; border-collapse: collapse; font-size: 0.9375rem; }
        .mera-table th, .mera-table td { padding: 16px 20px; text-align: left; }
        .mera-table th { background: #1e3c72; color: #fff; font-weight: 600; width: 200px; }
        .mera-table tr:nth-child(even) td { background: #f8fafc; }
        .mera-table tr:nth-child(odd) td { background: #fff; }
        .mera-table td { color: #334155; border-bottom: 1px solid #e2e8f0; line-height: 1.5; }
        .mera-table tr:last-child td { border-bottom: 0; }

        /* ----- Секции описания (полное описание) ----- */
        .mera-main h2 { font-size: 1.25rem; margin: 40px 0 16px; color: #1e3c72; font-weight: 700; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; }
        .mera-main h3 { font-size: 1.0625rem; margin: 24px 0 10px; color: #334155; font-weight: 600; }
        .mera-desc-block { background: #f8fafc; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .mera-main p, .mera-main .contacts-pre { color: #475569; line-height: 1.75; margin-bottom: 14px; font-size: 0.9375rem; }
        .mera-main p:last-of-type { margin-bottom: 0; }

        /* ----- Пошаговая инструкция ----- */
        .mera-steps { list-style: none; padding: 0; margin: 20px 0 24px; counter-reset: step; }
        .mera-steps li { position: relative; padding: 18px 0 18px 56px; border-bottom: 1px solid #e2e8f0; counter-increment: step; font-size: 0.9375rem; color: #334155; line-height: 1.6; }
        .mera-steps li:last-child { border-bottom: 0; }
        .mera-steps li::before { content: counter(step); position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 700; }
        .mera-cta-wrap { margin: 28px 0 32px; }
        .mera-cta-btn { display: inline-flex; align-items: center; padding: 14px 28px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff !important; border: 0; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 14px rgba(30, 60, 114, 0.35); transition: transform 0.15s, box-shadow 0.2s; }
        .mera-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(30, 60, 114, 0.4); }

        /* ----- Контакты ----- */
        .mera-contacts-block { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 16px; padding: 24px 28px; margin: 24px 0 32px; }
        .mera-contacts-block .contacts-pre { white-space: pre-line; font-size: 0.9375rem; color: #334155; line-height: 1.7; margin: 0 0 16px; }
        .mera-contacts-block a { color: #1e3c72; font-weight: 500; }

        /* ----- Сайдбар: два раздельных блока ----- */
        .mera-aside { position: sticky; top: 24px; height: fit-content; display: flex; flex-direction: column; gap: 24px; }
        /* Блок 1: консультация и ключевые цифры */
        .mera-aside-cta-box { background: linear-gradient(165deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%); border: 1px solid #6ee7b7; border-radius: 20px; padding: 28px 26px; box-shadow: 0 4px 24px rgba(5, 150, 105, 0.12), 0 1px 3px rgba(0,0,0,0.04); }
        .mera-aside-cta-title { margin: 0 0 12px; font-size: 1.125rem; color: #065f46; font-weight: 700; letter-spacing: -0.01em; }
        .mera-aside-cta-desc { margin: 0 0 20px; font-size: 0.9rem; color: #047857; line-height: 1.45; }
        .mera-aside-cta-meta { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; margin-bottom: 10px; padding: 10px 14px; background: rgba(255,255,255,0.7); border-radius: 10px; }
        .mera-aside-cta-label { font-size: 0.8125rem; color: #047857; }
        .mera-aside-cta-value { font-size: 1rem; font-weight: 700; color: #064e3b; }
        .mera-aside-cta-time { margin: 0 0 20px; font-size: 0.8125rem; color: #059669; }
        .mera-aside-cta { width: 100%; padding: 16px 20px; margin: 0; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; border: 0; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35); transition: opacity 0.2s, transform 0.05s; }
        .mera-aside-cta:hover { opacity: 0.95; transform: translateY(-1px); }
        /* Блок 2: форма заявки и контакты */
        .mera-aside-form-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 26px 26px 28px; box-shadow: 0 4px 24px rgba(30, 60, 114, 0.06), 0 1px 3px rgba(0,0,0,0.04); }
        .mera-aside-form-title { margin: 0 0 20px; font-size: 1.0625rem; color: #1e3c72; font-weight: 700; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0; }
        .mera-aside-form-box input, .mera-aside-form-box textarea { width: 100%; padding: 12px 14px; margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 10px; box-sizing: border-box; font-size: 0.9375rem; transition: border-color 0.2s, box-shadow 0.2s; }
        .mera-aside-form-box input:focus, .mera-aside-form-box textarea:focus { outline: none; border-color: #1e3c72; box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.12); }
        .mera-aside-form-box button[type="submit"] { width: 100%; padding: 16px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; border: 0; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; margin-top: 4px; transition: opacity 0.2s; }
        .mera-aside-form-box button[type="submit"]:hover { opacity: 0.95; }
        .mera-aside-contacts { margin-top: 22px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .mera-aside-contact-item { margin: 0 0 10px; font-size: 0.9375rem; color: #334155; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .mera-aside-contact-icon { font-size: 1rem; }
        .mera-aside-contact-list { margin: 14px 0 0; padding: 0 0 0 1.2em; font-size: 0.875rem; color: #64748b; line-height: 1.7; }
        .mera-aside-contact-list li { margin-bottom: 4px; }
        .mera-why { background: #fff; border-radius: 16px; padding: 24px; margin-top: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .mera-why h3 { margin: 0 0 16px; font-size: 1rem; color: #1e3c72; font-weight: 700; }
        .mera-why ul { margin: 0; padding: 0; list-style: none; }
        .mera-why li { position: relative; padding: 10px 0 10px 28px; font-size: 0.9rem; color: #475569; line-height: 1.55; }
        .mera-why li::before { content: ''; position: absolute; left: 0; top: 16px; width: 8px; height: 8px; background: #059669; border-radius: 50%; }

        /* ----- Другие меры (карточки) ----- */
        .mera-other { margin-top: 48px; padding-top: 32px; border-top: 2px solid #e2e8f0; }
        .mera-other .mera-section-title { margin-bottom: 20px; border: 0; padding: 0; }
        .mera-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .mera-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 24px; transition: box-shadow 0.2s, border-color 0.2s; }
        .mera-card:hover { box-shadow: 0 8px 24px rgba(30, 60, 114, 0.1); border-color: #93c5fd; }
        .mera-card a { color: #1e3c72; text-decoration: none; font-weight: 600; font-size: 0.9375rem; line-height: 1.4; display: block; }
        .mera-card a:hover { text-decoration: underline; }
        .mera-card .amount { font-size: 0.8125rem; color: #64748b; margin-top: 10px; font-weight: 500; }
    </style>
</head>
<body>
    <div data-include="/header.html"></div>

    <div class="mera-page">
    <div class="mera-bread">
        <a href="/">Главная</a> → <a href="/navigator-mer-podderzhki-gisp">Навигатор мер поддержки ГИСП</a> → <?php echo htmlspecialchars($measure['section'] ?? ''); ?> → <span><?php echo htmlspecialchars(mb_substr($measure['title_normal'] ?? '', 0, 50)); ?>…</span>
    </div>

    <div class="mera-layout">
        <main class="mera-main">
            <div class="mera-hero">
                <div class="mera-badges">
                    <span class="mera-badge mera-badge-ty"><?php echo htmlspecialchars($measure['support_type'] ?? ''); ?></span>
                    <span class="mera-badge mera-badge-sec"><?php echo htmlspecialchars($measure['section'] ?? ''); ?></span>
                </div>
                <h1><?php echo htmlspecialchars($measure['title_normal'] ?? ''); ?></h1>
                <p class="mera-intro"><?php echo htmlspecialchars($intro); ?></p>
            </div>

            <div class="mera-meta">
                <?php if (!empty($measure['npa'])): ?><p><strong>НПА:</strong> <?php echo htmlspecialchars($measure['npa']); ?></p><?php endif; ?>
                <p><strong>Администратор:</strong> <?php echo htmlspecialchars($measure['administrator'] ?? '—'); ?></p>
                <?php if (!empty($measure['operator'])): ?><p><strong>Оператор:</strong> <?php echo htmlspecialchars($measure['operator']); ?></p><?php endif; ?>
                <?php if (!empty($measure['source_url'])): ?><p><strong>Источник:</strong> <a href="<?php echo htmlspecialchars($measure['source_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($measure['source_url']); ?></a></p><?php endif; ?>
            </div>

            <h2 class="mera-section-title">Ключевые параметры</h2>
            <div class="mera-table-wrap">
            <table class="mera-table">
                <tr><th>Размер</th><td><?php echo htmlspecialchars($measure['amount_formatted'] ?? '—'); ?></td></tr>
                <tr><th>Участники</th><td><?php echo htmlspecialchars($measure['participants'] ?? '—'); ?></td></tr>
                <tr><th>Цель</th><td><?php echo htmlspecialchars($measure['goal_tags'] ?? '—'); ?></td></tr>
                <tr><th>Срок</th><td><?php echo htmlspecialchars($measure['implementation_period'] ?? '—'); ?></td></tr>
                <tr><th>Тип</th><td><?php echo htmlspecialchars($measure['support_type'] ?? '—'); ?></td></tr>
            </table>
            </div>

            <h2 class="mera-section-title">Полное описание</h2>
            <?php if (!empty($measure['participants'])): ?><div class="mera-desc-block"><h3>Кто может получить</h3><p><?php echo nl2br(htmlspecialchars($measure['participants'])); ?></p></div><?php endif; ?>
            <?php if (!empty($measure['financing_direction']) || !empty($measure['financing_object'])): ?>
            <div class="mera-desc-block"><h3>На что предоставляется</h3>
            <p><?php echo nl2br(htmlspecialchars(trim($measure['financing_direction'] . ' ' . $measure['financing_object']))); ?></p></div>
            <?php endif; ?>
            <?php if (!empty($measure['support_size']) || !empty($measure['support_conditions'])): ?>
            <div class="mera-desc-block"><h3>Размер и условия</h3>
            <p><?php echo nl2br(htmlspecialchars(trim($measure['support_size'] . ' ' . $measure['support_conditions']))); ?></p></div>
            <?php endif; ?>
            <?php if (!empty($measure['criteria'])): ?><div class="mera-desc-block"><h3>Требования к заявителю</h3><p><?php echo nl2br(htmlspecialchars($measure['criteria'])); ?></p></div><?php endif; ?>

            <h2 class="mera-section-title">Как получить <?php echo htmlspecialchars(mb_substr($measure['title_normal'] ?? '', 0, 40)); ?> — пошаговая инструкция</h2>
            <ul class="mera-steps">
                <?php foreach ($steps as $i => $step): ?>
                <li><?php echo htmlspecialchars($step); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Подсказка:</strong> Подготовка заявки требует знания специфики требований администратора программы. Специалисты РеестрГарант помогут пройти весь путь — от проверки соответствия до подписания соглашения.</p>
            <div class="mera-cta-wrap">
                <button type="button" class="mera-cta-btn" onclick="typeof openModal === 'function' && openModal('consultation')">Заказать подготовку заявки под ключ →</button>
            </div>

            <h2 class="mera-section-title">Контакты для подачи заявки</h2>
            <div class="mera-contacts-block">
            <div class="contacts-pre"><?php echo nl2br(htmlspecialchars($measure['contacts'] ?? '—')); ?></div>
            <?php if (!empty($measure['source_url'])): ?><p><a href="<?php echo htmlspecialchars($measure['source_url']); ?>" target="_blank" rel="noopener noreferrer">Перейти на страницу программы →</a></p><?php endif; ?>
            <?php if (!empty($measure['gisp_link'])): ?><p><a href="https://gisp.gov.ru" target="_blank" rel="noopener noreferrer">Открыть в ГИСП →</a></p><?php endif; ?>
            </div>

            <?php if (count($same_section) > 0): ?>
            <section class="mera-other">
                <h2 class="mera-section-title">Другие меры поддержки в разделе «<?php echo htmlspecialchars($measure['section'] ?? ''); ?>»</h2>
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
            <!-- Блок 1: Консультация и ключевые цифры -->
            <div class="mera-aside-cta-box">
                <h3 class="mera-aside-cta-title">Получить помощь в получении</h3>
                <p class="mera-aside-cta-desc"><?php echo htmlspecialchars(mb_substr($measure['title_normal'] ?? '', 0, 45)); ?>…</p>
                <div class="mera-aside-cta-meta">
                    <span class="mera-aside-cta-label">Размер поддержки</span>
                    <span class="mera-aside-cta-value"><?php echo htmlspecialchars($measure['amount_formatted'] ?? '—'); ?></span>
                </div>
                <div class="mera-aside-cta-meta">
                    <span class="mera-aside-cta-label">Консалтинг по рынку</span>
                    <span class="mera-aside-cta-value"><?php echo htmlspecialchars($price_label); ?></span>
                </div>
                <p class="mera-aside-cta-time">Среднее время подготовки: 14–21 день</p>
                <button type="button" class="mera-aside-cta" onclick="typeof openModal === 'function' && openModal('consultation')">Получить консультацию</button>
            </div>
            <!-- Блок 2: Форма заявки и контакты -->
            <div class="mera-aside-form-box" id="form">
                <h3 class="mera-aside-form-title">Оставить заявку</h3>
                <form action="/send-email.php" method="post" id="meraLeadForm">
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
                <div class="mera-aside-contacts">
                    <p class="mera-aside-contact-item"><span class="mera-aside-contact-icon">📞</span> +7 920-898-17-18</p>
                    <p class="mera-aside-contact-item"><span class="mera-aside-contact-icon">✉️</span> reestrgarant@mail.ru</p>
                    <ul class="mera-aside-contact-list">
                        <li>Бесплатная первичная консультация</li>
                        <li>Оценка шансов на получение</li>
                        <li>Подготовка документов под ключ</li>
                    </ul>
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
    </div>

    <div data-include="/footer.html"></div>
    <script src="/js/modal.js"></script>
    <script>(function(){ var orig = window.openModal; if (orig) { window.openModal = function(type) { orig(type); setTimeout(function(){ var s = document.getElementById('lead_service'); if (s) s.value = <?php echo json_encode($measure['title_normal'] ?? 'Консультация'); ?>; }, 200); }; } })();</script>
    <script src="/include.js"></script>
    <script src="/js/lead-form.js"></script>
</body>
</html>
