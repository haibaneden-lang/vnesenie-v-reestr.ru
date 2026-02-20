<?php
/**
 * Каталог мер поддержки промышленности — 171 мера (Минпромторг + ФРП)
 * SEO: Меры поддержки промышленности 2025 — 171 субсидия, грант и заём ФРП | РеестрГарант
 */
$data_file = dirname(__DIR__) . '/data/all_measures_combined.json';
if (!is_readable($data_file)) {
    header('HTTP/1.0 503 Service Unavailable');
    echo 'Данные мер поддержки временно недоступны.';
    exit;
}
$all = json_decode(file_get_contents($data_file), true);
if (!is_array($all)) {
    header('HTTP/1.0 503 Service Unavailable');
    echo 'Ошибка загрузки данных.';
    exit;
}

// Разбивка по источнику для фильтра
$by_source = [
    'minpromtorg' => [],
    'frp_federal' => [],
    'frp_regional' => [],
];
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

// Уникальные section и support_type для фильтров
$sections = [];
$support_types = [];
foreach ($all as $m) {
    if (!empty($m['section'])) $sections[$m['section']] = ($sections[$m['section']] ?? 0) + 1;
    if (!empty($m['support_type'])) $support_types[$m['support_type']] = ($support_types[$m['support_type']] ?? 0) + 1;
}
ksort($sections);
ksort($support_types);

// Фильтры из GET
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_source = isset($_GET['source']) ? $_GET['source'] : '';
$filter_section = isset($_GET['section']) ? trim($_GET['section']) : '';
$filter_type = isset($_GET['type']) ? trim($_GET['type']) : '';

$filtered = $all;
if ($q !== '') {
    $q_lower = mb_strtolower($q);
    $filtered = array_filter($filtered, function ($m) use ($q_lower) {
        return (
            (isset($m['title_normal']) && mb_stripos($m['title_normal'], $q_lower) !== false) ||
            (isset($m['section']) && mb_stripos($m['section'], $q_lower) !== false) ||
            (isset($m['participants']) && mb_stripos($m['participants'], $q_lower) !== false)
        );
    });
}
if ($filter_source === 'minpromtorg') { $ids = array_column($by_source['minpromtorg'], 'id'); $filtered = array_values(array_filter($filtered, function ($m) use ($ids) { return in_array($m['id'] ?? 0, $ids); })); }
elseif ($filter_source === 'frp_federal') { $ids = array_column($by_source['frp_federal'], 'id'); $filtered = array_values(array_filter($filtered, function ($m) use ($ids) { return in_array($m['id'] ?? 0, $ids); })); }
elseif ($filter_source === 'frp_regional') { $ids = array_column($by_source['frp_regional'], 'id'); $filtered = array_values(array_filter($filtered, function ($m) use ($ids) { return in_array($m['id'] ?? 0, $ids); })); }
if ($filter_section !== '') $filtered = array_values(array_filter($filtered, function ($m) use ($filter_section) { return isset($m['section']) && $m['section'] === $filter_section; }));
if ($filter_type !== '') $filtered = array_values(array_filter($filtered, function ($m) use ($filter_type) { return isset($m['support_type']) && $m['support_type'] === $filter_type; }));

$title = 'Меры поддержки промышленности 2025 — 171 субсидия, грант и заём ФРП | РеестрГарант';
$description = 'Полный каталог мер государственной поддержки промышленных предприятий России. 79 программ Минпромторга + 92 программы ФРП. Субсидии, гранты, льготные займы. Помощь в получении. Тел: +7 920-898-17-18';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <link rel="canonical" href="https://vnesenie-v-reestr.ru/mery-podderzhki/">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    <style>
        .mp-hero { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 56px 0 48px; text-align: center; }
        .mp-hero h1 { font-size: 2rem; margin-bottom: 12px; }
        .mp-hero .sub { font-size: 1.05rem; opacity: 0.95; margin-bottom: 20px; }
        .mp-hero .btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .mp-hero .btns a { color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; }
        .mp-hero .btns .btn-primary { background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.5); }
        .mp-hero .btns .btn-tel { background: #0f172a; }
        .mp-layout { display: grid; grid-template-columns: 220px 1fr; gap: 32px; max-width: 1200px; margin: 0 auto; padding: 32px 20px; }
        @media (max-width: 900px) { .mp-layout { grid-template-columns: 1fr; } }
        .mp-filters { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; height: fit-content; position: sticky; top: 20px; }
        .mp-filters h3 { font-size: 1rem; margin: 0 0 12px; color: #1e3c72; }
        .mp-filters input, .mp-filters select { width: 100%; padding: 8px 10px; margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .mp-filters label { display: block; margin-bottom: 6px; font-size: 0.9rem; color: #475569; cursor: pointer; }
        .mp-filters .reset { margin-top: 12px; }
        .mp-filters .reset a { color: #64748b; font-size: 0.9rem; }
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
    </style>
</head>
<body>
    <div data-include="/header.html"></div>

    <section class="mp-hero">
        <div class="container">
            <h1>Меры господдержки промышленности 2025</h1>
            <p class="sub">171 актуальная программа — Минпромторг, ФРП и региональные фонды</p>
            <div class="btns">
                <a href="#form" class="btn-primary">Бесплатный подбор мер</a>
                <a href="tel:+79208981718" class="btn-tel">+7 920-898-17-18</a>
            </div>
        </div>
    </section>

    <div class="mp-layout">
        <aside class="mp-filters">
            <form method="get" action="">
                <h3>Поиск</h3>
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Название или отрасль...">
                <h3>Источник</h3>
                <label><input type="radio" name="source" value="" <?php echo $filter_source === '' ? 'checked' : ''; ?>> Все</label>
                <label><input type="radio" name="source" value="minpromtorg" <?php echo $filter_source === 'minpromtorg' ? 'checked' : ''; ?>> Минпромторг (<?php echo count($by_source['minpromtorg']); ?>)</label>
                <label><input type="radio" name="source" value="frp_federal" <?php echo $filter_source === 'frp_federal' ? 'checked' : ''; ?>> ФРП федеральные (<?php echo count($by_source['frp_federal']); ?>)</label>
                <label><input type="radio" name="source" value="frp_regional" <?php echo $filter_source === 'frp_regional' ? 'checked' : ''; ?>> ФРП региональные (<?php echo count($by_source['frp_regional']); ?>)</label>
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
                <button type="submit" style="width:100%; padding:10px; background:#1e3c72; color:#fff; border:0; border-radius:8px; cursor:pointer;">Показать</button>
                <div class="reset"><a href="/mery-podderzhki/">Сбросить фильтры</a></div>
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
                            $url = $slug ? '/mery-podderzhki/' . htmlspecialchars($slug) : '#';
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

    <div data-include="/footer.html"></div>
    <script src="/include.js"></script>
</body>
</html>
