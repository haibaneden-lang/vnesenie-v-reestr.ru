<?php
/**
 * Навигатор мер поддержки ГИСП — одна страница с текстом и каталогом 171 меры
 * URL: /navigator-mer-podderzhki-gisp
 */
$data_file = __DIR__ . '/data/all_measures_combined.json';
if (!is_readable($data_file)) {
    $all = [];
    $by_source = ['minpromtorg' => [], 'frp_federal' => [], 'frp_regional' => []];
    $sections = [];
    $support_types = [];
    $filtered = [];
    $q = '';
    $filter_source = '';
    $filter_section = '';
    $filter_type = '';
} else {
    $all = json_decode(file_get_contents($data_file), true);
    if (!is_array($all)) $all = [];
    $by_source = ['minpromtorg' => [], 'frp_federal' => [], 'frp_regional' => []];
    foreach ($all as $m) {
        $src = isset($m['source']) ? $m['source'] : '';
        $sec = isset($m['section']) ? $m['section'] : '';
        if (stripos($src, 'региональный') !== false || stripos($sec, 'Региональные программы ФРП') !== false) {
            $by_source['frp_regional'][] = $m;
        } elseif ($src === 'frprf.ru' || stripos($sec, 'Программы ФРП') !== false) {
            $by_source['frp_federal'][] = $m;
        } else {
            $by_source['minpromtorg'][] = $m;
        }
    }
    $sections = [];
    $support_types = [];
    foreach ($all as $m) {
        if (!empty($m['section'])) $sections[$m['section']] = ($sections[$m['section']] ?? 0) + 1;
        if (!empty($m['support_type'])) $support_types[$m['support_type']] = ($support_types[$m['support_type']] ?? 0) + 1;
    }
    ksort($sections);
    ksort($support_types);
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $filter_source = isset($_GET['source']) ? $_GET['source'] : '';
    $filter_section = isset($_GET['section']) ? trim($_GET['section']) : '';
    $filter_type = isset($_GET['type']) ? trim($_GET['type']) : '';
    $filtered = $all;
    if ($q !== '') {
        $q_lower = mb_strtolower($q);
        $filtered = array_filter($filtered, function ($m) use ($q_lower) {
            return (isset($m['title_normal']) && mb_stripos($m['title_normal'], $q_lower) !== false)
                || (isset($m['section']) && mb_stripos($m['section'], $q_lower) !== false)
                || (isset($m['participants']) && mb_stripos($m['participants'], $q_lower) !== false);
        });
    }
    if ($filter_source === 'minpromtorg') { $ids = array_column($by_source['minpromtorg'], 'id'); $filtered = array_values(array_filter($filtered, function ($m) use ($ids) { return in_array($m['id'] ?? 0, $ids); })); }
    elseif ($filter_source === 'frp_federal') { $ids = array_column($by_source['frp_federal'], 'id'); $filtered = array_values(array_filter($filtered, function ($m) use ($ids) { return in_array($m['id'] ?? 0, $ids); })); }
    elseif ($filter_source === 'frp_regional') { $ids = array_column($by_source['frp_regional'], 'id'); $filtered = array_values(array_filter($filtered, function ($m) use ($ids) { return in_array($m['id'] ?? 0, $ids); })); }
    if ($filter_section !== '') $filtered = array_values(array_filter($filtered, function ($m) use ($filter_section) { return isset($m['section']) && $m['section'] === $filter_section; }));
    if ($filter_type !== '') $filtered = array_values(array_filter($filtered, function ($m) use ($filter_type) { return isset($m['support_type']) && $m['support_type'] === $filter_type; }));
}
$catalog_action = '/navigator-mer-podderzhki-gisp';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Навигатор мер поддержки ГИСП — как пользоваться | Реестр Гарант</title>
    <meta name="description" content="Навигатор мер поддержки ГИСП Минпромторга: что это, как найти подходящие меры поддержки для производителей. 171 мера: таблица с фильтрами, ссылка на gisp.gov.ru.">
    <meta property="og:title" content="Навигатор мер поддержки ГИСП — как пользоваться | Реестр Гарант">
    <meta property="og:description" content="Навигатор мер поддержки ГИСП: 171 мера, таблица с фильтрами. Официальный портал gisp.gov.ru и помощь во включении в реестры.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vnesenie-v-reestr.ru/navigator-mer-podderzhki-gisp">
    <meta property="og:image" content="https://vnesenie-v-reestr.ru/og-image.jpg">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://vnesenie-v-reestr.ru/navigator-mer-podderzhki-gisp">
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    <link rel="stylesheet" href="/cert.css">
    <style>
    .dp-hero { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 80px 0 70px; text-align: center; }
    .dp-hero h1 { font-size: 2.5rem; margin-bottom: 18px; }
    .dp-hero p { max-width: 760px; margin: 0 auto 28px; font-size: 1.1rem; opacity: 0.95; }
    .dp-hero-meta { display: flex; justify-content: center; flex-wrap: wrap; gap: 14px; font-size: 0.95rem; opacity: 0.9; }
    .dp-hero-meta span { padding: 6px 12px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.35); background: rgba(15,23,42,0.18); }
    .dp-main { padding: 56px 0 40px; background: #fff; }
    .dp-layout { display: grid; grid-template-columns: minmax(0, 2.2fr) minmax(0, 1.2fr); gap: 40px; }
    .dp-article h2 { font-size: 1.7rem; margin: 40px 0 16px; padding: 0 0 0 18px; border-left: 4px solid #1e3c72; color: #1e3c72; }
    .dp-article h2:first-child { margin-top: 0; }
    .dp-article p, .dp-article ul { margin: 0 0 14px; line-height: 1.7; color: #374151; font-size: 0.98rem; }
    .dp-article ul { padding-left: 1.4em; }
    .dp-callout { background: #f0f7ff; border-left: 4px solid #1e3c72; padding: 14px 18px; margin: 18px 0; border-radius: 0 8px 8px 0; font-size: 0.95rem; color: #1e293b; }
    .dp-list li { margin-bottom: 6px; }
    .dp-aside-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .dp-aside-card h3 { font-size: 1.05rem; margin: 0 0 10px; color: #1e3c72; display: flex; align-items: center; gap: 8px; }
    .dp-aside-card p, .dp-aside-card ul { margin: 0; font-size: 0.9rem; line-height: 1.6; color: #475569; }
    .dp-aside-card ul { padding-left: 1.2em; }
    .dp-aside-icon { display: inline-flex; }
    .dp-aside-icon svg { width: 20px; height: 20px; }
    .gisp-official { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 20px; margin: 24px 0; }
    .gisp-official a { color: #047857; font-weight: 600; }
    @media (max-width: 900px) { .dp-layout { grid-template-columns: 1fr; } }
    /* Каталог мер на этой же странице */
    .mp-catalog { padding: 48px 0 56px; background: #f8fafc; }
    .mp-catalog h2 { text-align: center; font-size: 1.8rem; margin-bottom: 8px; color: #1e3c72; }
    .mp-catalog .sub { text-align: center; color: #64748b; margin-bottom: 24px; }
    .mp-layout { display: grid; grid-template-columns: 260px 1fr; gap: 32px; max-width: 1200px; margin: 0 auto; padding: 0 20px; align-items: start; }
    @media (max-width: 900px) { .mp-layout { grid-template-columns: 1fr; gap: 24px; } }
    /* Блок фильтров — современный вид в стилистике сайта */
    .mp-filters { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; height: fit-content; position: sticky; top: 24px; box-shadow: 0 4px 20px rgba(30, 60, 114, 0.06); }
    .mp-filters form { display: flex; flex-direction: column; gap: 0; }
    .mp-filters h3 { font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #1e3c72; margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }
    .mp-filters h3:not(:first-child) { margin-top: 20px; }
    .mp-filters input[type="text"] { width: 100%; padding: 12px 14px; margin-bottom: 4px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.9375rem; color: #1e293b; background: #fff; transition: border-color 0.2s, box-shadow 0.2s; }
    .mp-filters input[type="text"]::placeholder { color: #94a3b8; }
    .mp-filters input[type="text"]:focus { outline: none; border-color: #1e3c72; box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.15); }
    .mp-filters select { width: 100%; padding: 12px 36px 12px 14px; margin-bottom: 4px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.9375rem; color: #1e293b; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23475569' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 12px center; appearance: none; cursor: pointer; transition: border-color 0.2s, box-shadow 0.2s; }
    .mp-filters select:focus { outline: none; border-color: #1e3c72; box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.15); }
    .mp-filters .filter-radios { display: flex; flex-direction: column; gap: 8px; margin-bottom: 4px; }
    .mp-filters label { display: flex; align-items: center; gap: 10px; margin-bottom: 0; font-size: 0.9375rem; color: #475569; cursor: pointer; padding: 8px 10px; border-radius: 8px; transition: background 0.15s, color 0.15s; }
    .mp-filters label:hover { background: #f1f5f9; color: #1e293b; }
    .mp-filters label input[type="radio"] { width: 18px; height: 18px; margin: 0; accent-color: #1e3c72; cursor: pointer; flex-shrink: 0; }
    .mp-filters button[type="submit"] { width: 100%; margin-top: 20px; padding: 14px 20px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; border: 0; border-radius: 10px; font-size: 0.9375rem; font-weight: 600; cursor: pointer; transition: opacity 0.2s, transform 0.1s; }
    .mp-filters button[type="submit"]:hover { opacity: 0.95; }
    .mp-filters button[type="submit"]:active { transform: scale(0.99); }
    .mp-filters button[type="submit"]:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.4); }
    .mp-filters .reset { margin-top: 14px; text-align: center; }
    .mp-filters .reset a { color: #64748b; font-size: 0.875rem; text-decoration: none; padding: 6px 12px; border-radius: 8px; transition: color 0.15s, background 0.15s; }
    .mp-filters .reset a:hover { color: #1e3c72; background: #f1f5f9; }
    @media (max-width: 900px) { .mp-filters { position: static; } }
    .mp-table-wrap { overflow-x: auto; }
    .mp-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .mp-table th, .mp-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    .mp-table th { background: #1e3c72; color: #fff; font-weight: 600; }
    .mp-table tr:hover td { background: #f1f5f9; }
    .mp-table a { color: #1e3c72; text-decoration: none; font-weight: 500; }
    .mp-table a:hover { text-decoration: underline; }
    .mp-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-right: 4px; }
    .mp-badge-subs { background: #059669; color: #fff; }
    .mp-badge-grant { background: #2563eb; color: #fff; }
    .mp-badge-frp { background: #ea580c; color: #fff; }
    .mp-badge-credit { background: #6d28d9; color: #fff; }
    .mp-badge-other { background: #64748b; color: #fff; }
    .mp-count { color: #64748b; font-size: 0.95rem; margin-bottom: 16px; }
    .dp-section { padding: 48px 0 56px; background: #f0f4f8; }
    .dp-section h2 { text-align: center; font-size: 1.8rem; margin-bottom: 12px; color: #1e3c72; }
    .dp-section .section-subtitle { text-align: center; max-width: 640px; margin: 0 auto 32px; color: #64748b; }
    .dp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .dp-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; transition: box-shadow 0.2s, border-color 0.2s; }
    .dp-card:hover { border-color: #1e3c72; box-shadow: 0 4px 12px rgba(30,60,114,0.12); }
    .dp-card-header { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
    .dp-icon-circle { width: 44px; height: 44px; min-width: 44px; border-radius: 50%; background: #e0e7ff; color: #1e3c72; display: flex; align-items: center; justify-content: center; }
    .dp-icon-circle svg { width: 22px; height: 22px; }
    .dp-card-title { font-size: 1.05rem; margin: 0; color: #1e293b; }
    .dp-card p { margin: 0; font-size: 0.92rem; line-height: 1.6; color: #64748b; }
    html { scroll-behavior: smooth; }
    @media (max-width: 768px) { .container { padding: 0 16px; } .dp-hero { padding: 52px 0 48px; } .dp-hero h1 { font-size: 1.75rem; } }
    @media (max-width: 480px) { .container { padding: 0 12px; } .cta-buttons { flex-direction: column; } .cta-buttons .btn { width: 100%; } }
    </style>
</head>
<body>
    <div data-include="header.html"></div>

    <section class="dp-hero">
        <div class="container">
            <h1>Навигатор мер поддержки ГИСП</h1>
            <p>Удобный способ найти подходящие меры государственной поддержки для российских производителей через единый портал ГИСП Минпромторга — субсидии, льготы, реестры и программы. Ниже — каталог из 171 актуальной меры с фильтрами.</p>
            <div class="cta-buttons" style="margin-top: 24px; display: flex; flex-wrap: wrap; justify-content: center; gap: 12px;">
                <button type="button" class="btn btn-primary" onclick="typeof openModal === 'function' && openModal('consultation')">Получить консультацию</button>
                <a href="#katalog-mer" class="btn btn-secondary">Таблица мер поддержки (171)</a>
                <a href="#katalog-mer" class="btn btn-secondary">Подробнее</a>
            </div>
            <div class="dp-hero-meta" style="margin-top: 28px;">
                <span>Официальный портал Минпромторга</span>
                <span>171 мера: Минпромторг + ФРП</span>
                <span>Помощь во включении в реестры с 2015 года</span>
            </div>
        </div>
    </section>

    <section class="dp-main">
        <div class="container">
            <div class="dp-layout">
                <article class="dp-article">
                    <h2>Что такое навигатор мер поддержки ГИСП</h2>
                    <p>Государственная информационная система промышленности (ГИСП) — единая цифровая платформа Минпромторга России для ведения реестров российской промышленной продукции и предоставления мер поддержки. <strong>Навигатор мер поддержки</strong> в ГИСП помогает производителям и организациям подобрать подходящие программы: субсидии, льготное кредитование, включение в реестры, участие в госзакупках и отраслевых программах.</p>
                    <div class="gisp-official">
                        <p style="margin: 0 0 8px;"><strong>Официальный портал ГИСП:</strong></p>
                        <p style="margin: 0;"><a href="https://gisp.gov.ru" target="_blank" rel="noopener noreferrer">https://gisp.gov.ru</a> — здесь размещены реестры, меры поддержки, сервисы подачи заявлений и актуальная информация Минпромторга.</p>
                    </div>

    <section class="mp-catalog" id="katalog-mer">
        <h2>Каталог мер поддержки промышленности — 171 программа</h2>
        <p class="sub">Минпромторг, ФРП федеральные и региональные. Клик по названию — отдельная страница с описанием и заявкой.</p>
        <div class="mp-layout">
            <aside class="mp-filters">
                <form method="get" action="<?php echo htmlspecialchars($catalog_action); ?>">
                    <h3>Поиск</h3>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Название или отрасль...">
                    <h3>Источник</h3>
                    <div class="filter-radios">
                    <label><input type="radio" name="source" value="" <?php echo $filter_source === '' ? 'checked' : ''; ?>> Все</label>
                    <label><input type="radio" name="source" value="minpromtorg" <?php echo $filter_source === 'minpromtorg' ? 'checked' : ''; ?>> Минпромторг (<?php echo count($by_source['minpromtorg']); ?>)</label>
                    <label><input type="radio" name="source" value="frp_federal" <?php echo $filter_source === 'frp_federal' ? 'checked' : ''; ?>> ФРП федеральные (<?php echo count($by_source['frp_federal']); ?>)</label>
                    <label><input type="radio" name="source" value="frp_regional" <?php echo $filter_source === 'frp_regional' ? 'checked' : ''; ?>> ФРП региональные (<?php echo count($by_source['frp_regional']); ?>)</label>
                    </div>
                    <h3>Отрасль</h3>
                    <select name="section">
                        <option value="">Все отрасли</option>
                        <?php foreach ($sections as $sec => $cnt): ?>
                        <option value="<?php echo htmlspecialchars($sec); ?>" <?php echo $filter_section === $sec ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec); ?> (<?php echo $cnt; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <h3>Тип поддержки</h3>
                    <select name="type">
                        <option value="">Все типы</option>
                        <?php foreach ($support_types as $st => $cnt): ?>
                        <option value="<?php echo htmlspecialchars($st); ?>" <?php echo $filter_type === $st ? 'selected' : ''; ?>><?php echo htmlspecialchars($st); ?> (<?php echo $cnt; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="mp-filters-submit">Показать</button>
                    <div class="reset"><a href="/navigator-mer-podderzhki-gisp">Сбросить фильтры</a></div>
                </form>
            </aside>
            <main>
                <p class="mp-count">Найдено мер: <strong><?php echo count($filtered); ?></strong></p>
                <div class="mp-table-wrap">
                    <table class="mp-table">
                        <thead>
                            <tr>
                                <th>Мера поддержки</th>
                                <th>Тип</th>
                                <th>Отрасль</th>
                                <th>Размер</th>
                                <th>Консалтинг</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($filtered as $m):
                                $slug = $m['slug'] ?? '';
                                $url = $slug ? '/navigator-mer-podderzhki-gisp/' . htmlspecialchars($slug) : '#';
                                $st = $m['support_type'] ?? '';
                                $badge_class = 'mp-badge-other';
                                if ($st === 'Субсидия') $badge_class = 'mp-badge-subs';
                                elseif ($st === 'Грант') $badge_class = 'mp-badge-grant';
                                elseif (stripos($st, 'ФРП') !== false) $badge_class = 'mp-badge-frp';
                                elseif (stripos($st, 'кредит') !== false || stripos($st, 'заём') !== false) $badge_class = 'mp-badge-credit';
                                $title_short = isset($m['title_normal']) ? mb_substr($m['title_normal'], 0, 120) . (mb_strlen($m['title_normal']) > 120 ? '…' : '') : '';
                                $price_label = isset($m['consulting_price']['label']) ? $m['consulting_price']['label'] : '—';
                            ?>
                            <tr>
                                <td><a href="<?php echo $url; ?>"><?php echo htmlspecialchars($title_short); ?></a></td>
                                <td><span class="mp-badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($st); ?></span></td>
                                <td><?php echo htmlspecialchars($m['section'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($m['amount_formatted'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($price_label); ?></td>
                                <td><a href="<?php echo $url; ?>">Подробнее →</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </section>

                    <h2>Для кого полезен навигатор мер поддержки</h2>
                    <p>Инструмент ориентирован на:</p>
                    <ul class="dp-list">
                        <li>российских производителей промышленной продукции;</li>
                        <li>компании, планирующие включение в реестры Минпромторга (промышленная продукция, ПО, радиоэлектроника, медицинские изделия, телекоммуникационное и нефтегазовое оборудование);</li>
                        <li>организации, претендующие на субсидии и льготы;</li>
                        <li>участников госзакупок и программ импортозамещения.</li>
                    </ul>
                    <h2>Какие меры поддержки можно найти в ГИСП</h2>
                    <p>Через портал ГИСП доступны сведения о реестрах российских производителей, мерах финансовой поддержки и субсидиях, льготном кредитовании и программах развития промышленности, требованиях к документации и нормативной базе.</p>
                    <div class="dp-callout"><strong>Важно:</strong> сначала необходимо определить, в какой реестр или программу подходит ваша продукция, и подготовить комплект документов. Компания «Реестр Гарант» сопровождает включение в реестры Минпромторга под ключ и помогает корректно пройти процедуры в ГИСП.</div>
                    <h2>Как пользоваться навигатором мер поддержки</h2>
                    <p>На портале <a href="https://gisp.gov.ru" target="_blank" rel="noopener noreferrer">gisp.gov.ru</a> можно ознакомиться с перечнем реестров и мер поддержки, подобрать программы по отрасли или типу продукции, изучить условия и при необходимости подать заявление в электронной форме.</p>
                    <h2>Помощь во включении в реестры Минпромторга</h2>
                    <p>«Реестр Гарант» с 2015 года сопровождает компании при включении в реестры Минпромторга и работе с ГИСП. Если вы определили подходящую меру поддержки через каталог ниже — по клику откроется отдельная страница с описанием и формой заявки.</p>
                </article>
                <aside class="dp-aside">
                    <div class="dp-aside-card">
                        <h3><span class="dp-aside-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></span>Где смотреть навигатор</h3>
                        <p>Раздел с мерами поддержки размещён на <a href="https://gisp.gov.ru" target="_blank" rel="noopener noreferrer">gisp.gov.ru</a>. Ниже на этой странице — наш каталог из 171 меры с фильтрами и ссылками на отдельную страницу по каждой мере.</p>
                    </div>
                    <div class="dp-aside-card">
                        <h3><span class="dp-aside-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>Кому пригодится</h3>
                        <ul><li>производителям промышленной продукции и ПО;</li><li>компаниям, планирующим субсидии и льготы;</li><li>участникам госзакупок и импортозамещения.</li></ul>
                    </div>
                    <div class="dp-aside-card">
                        <h3><span class="dp-aside-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></span>Что подготовить</h3>
                        <ul><li>сведения о продукции и производителе;</li><li>документы по нормативам выбранного реестра или меры;</li><li>при необходимости — техдокументация, сертификаты.</li></ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="dp-section">
        <div class="container">
            <h2>Полезные инструменты</h2>
            <p class="section-subtitle">Официальный портал ГИСП, включение в реестр Минпромторга, калькуляторы и реестры.</p>
            <div class="dp-grid">
                <a href="https://gisp.gov.ru" target="_blank" rel="noopener noreferrer" class="dp-card" style="text-decoration: none; color: inherit;">
                    <div class="dp-card-header"><span class="dp-icon-circle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></span><h3 class="dp-card-title">Официальный портал ГИСП</h3></div>
                    <p>Единая платформа Минпромторга: реестры, навигатор мер поддержки, сервисы и нормативная база.</p>
                </a>
                <a href="/gisp-minpromtorg" class="dp-card" style="text-decoration: none; color: inherit;">
                    <div class="dp-card-header"><span class="dp-icon-circle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2C20 17.5 12 22 12 22z"/></svg></span><h3 class="dp-card-title">Включение в реестр Минпромторга</h3></div>
                    <p>Подготовка документов и сопровождение включения в реестры ГИСП под ключ.</p>
                </a>
                <a href="/calculators/" class="dp-card" style="text-decoration: none; color: inherit;">
                    <div class="dp-card-header"><span class="dp-icon-circle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 10h8M8 14h4"/></svg></span><h3 class="dp-card-title">Калькуляторы Минпромторга</h3></div>
                    <p>Калькуляторы для расчёта критериев и проверки соответствия реестрам.</p>
                </a>
                <a href="/reestry-i-bazy/" class="dp-card" style="text-decoration: none; color: inherit;">
                    <div class="dp-card-header"><span class="dp-icon-circle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8M8 11h8"/></svg></span><h3 class="dp-card-title">Реестры и базы</h3></div>
                    <p>Справочники, реестры и базы данных для работы с господдержкой и закупками.</p>
                </a>
            </div>
        </div>
    </section>

    <div data-include="footer.html"></div>
    <script src="/js/modal.js"></script>
    <script src="/include.js"></script>
    <script src="/js/lead-form.js"></script>
    <script src="/script.js"></script>
</body>
</html>
