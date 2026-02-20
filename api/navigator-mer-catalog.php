<?php
/**
 * Возвращает HTML-фрагмент каталога мер (фильтры + таблица) для вставки на страницу навигатора.
 * Ссылки в таблице — только /navigator-mer-podderzhki-gisp/{slug} (без редиректов).
 * GET: q, source, section, type
 */
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

define('MEASURES_BASE_URL', '/navigator-mer-podderzhki-gisp');

$data_file = dirname(__DIR__) . '/data/all_measures_combined.json';
if (!is_readable($data_file)) {
    echo '<div class="mp-layout"><p class="mp-count">Данные каталога временно недоступны.</p></div>';
    exit;
}

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

$catalog_action = MEASURES_BASE_URL;
?>
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
                        $url = $slug ? MEASURES_BASE_URL . '/' . htmlspecialchars($slug) : '#';
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
                        <td><a href="<?php echo $url; ?>" class="mp-btn-detail">Подробнее →</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
