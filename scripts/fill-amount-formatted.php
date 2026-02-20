<?php
/**
 * Заполняет пустые amount_formatted в all_measures_combined.json
 * из support_size, full_description и известных лимитов программ.
 */
$path = dirname(__DIR__) . '/data/all_measures_combined.json';
$json = file_get_contents($path);
$data = json_decode($json, true);
if (!is_array($data)) {
    fwrite(STDERR, "Invalid JSON\n");
    exit(1);
}

// Проверка по началу title_normal для известных программ (официальные лимиты)
function getKnownAmountByTitle($titleNormal) {
    $t = mb_strtolower($titleNormal ?? '');
    if (strpos($t, 'ниокр по современным технологиям') !== false) return 'до 1 млрд руб. (до 70% затрат)';
    if (strpos($t, 'разработку конструкторской документации на комплектующие') !== false) return 'до 100 млн руб. (до 100% затрат)';
    if (strpos($t, 'возмещение части затрат на уплату процентов по кредитам') !== false && strpos($t, 'комплексн') !== false) return 'субсидия на проценты по кредиту';
    if (strpos($t, 'компенсацию процентов по кредитам на инновационные') !== false) return 'до 3/4 ставки ЦБ по кредиту';
    if (strpos($t, 'опк на создание и развитие системы повышения квалификации') !== false) return 'до 100% собственных средств';
    if (strpos($t, 'транспортировку продукции') !== false && strpos($t, 'лес') !== false) return 'до 30% затрат (ЛПК)';
    if (strpos($t, 'освобождения от уплаты ввозной таможенной пошлины') !== false) return 'освобождение от ввозной пошлины';
    if (strpos($t, 'освобождения от уплаты ввозного НДС') !== false) return 'освобождение от ввозного НДС';
    if (strpos($t, 'вэб.рф организаций ОПК') !== false) return 'субсидия на разницу ставок';
    if (strpos($t, 'российскими банками организаций ОПК') !== false) return 'до 90% ставки ЦБ по кредиту';
    if (strpos($t, 'вэб.рф организаций при реализации') !== false) return 'льготная ставка ВЭБ.РФ';
    if (strpos($t, 'росэксимбанк') !== false) return 'льготное кредитование';
    if (strpos($t, 'специальные инвестиционные контракты') !== false) return 'по условиям СПИК';
    if (strpos($t, 'единое окно поиска технологического партнера') !== false) return 'информационный сервис';
    return null;
}

function deriveAmountFromSupportSize($supportSize, $fullDesc) {
    $text = $supportSize . ' ' . $fullDesc;
    if (preg_match('/не более\s+(\d+(?:[.,]\d+)?)\s*млн/i', $text, $m)) return 'до ' . str_replace(',', '.', $m[1]) . ' млн руб.';
    if (preg_match('/до\s+(\d+(?:[.,]\d+)?)\s*млн/i', $text, $m)) return 'до ' . str_replace(',', '.', $m[1]) . ' млн руб.';
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*млн\s*руб/i', $text, $m)) return 'до ' . str_replace(',', '.', $m[1]) . ' млн руб.';
    if (preg_match('/(\d+)\s*млрд/i', $text, $m)) return 'до ' . $m[1] . ' млрд руб.';
    if (preg_match('/До\s+(\d+)\s*%\s*затрат/i', $text, $m)) return 'до ' . $m[1] . '% затрат';
    if (preg_match('/до\s+(\d+)\s*%\s*затрат/i', $text, $m)) return 'до ' . $m[1] . '% затрат';
    if (preg_match('/(\d+)\s*\/\s*4\s*базового индикатора/i', $text, $m)) return 'до 3/4 ставки ЦБ (проценты по кредиту)';
    if (preg_match('/90%\s*базового индикатора/i', $text)) return 'до 90% ставки ЦБ';
    if (preg_match('/Освобождение от уплаты ввозной таможенной пошлины/i', $text)) return 'освобождение от ввозной пошлины';
    if (preg_match('/Освобождение от уплаты ввозного НДС/i', $text)) return 'освобождение от ввозного НДС';
    if (preg_match('/Разница между расчетной и фактической процентной ставкой/i', $text)) return 'субсидия на разницу ставок';
    if (preg_match('/Максимальный размер субсидии не может превышать 100%/i', $text)) return 'до 100% собственных средств';
    if (preg_match('/от 10% до 15%/i', $text)) return '10–15% от стоимости единицы';
    if (preg_match('/до 15% цены/i', $text)) return 'до 15% цены техники';
    if (preg_match('/До 60% затрат/i', $text)) return 'до 60% затрат';
    if (preg_match('/До 90% затрат/i', $text)) return 'до 90% затрат';
    if (preg_match('/В размере предоставленной скидки/i', $text)) return 'в размере скидки';
    if (preg_match('/Субсидия в размере займа/i', $text)) return 'в размере займа ФРП';
    if (preg_match('/не может превышать 15% лимитов/i', $text)) return 'до 15% лимита на производителя';
    if (preg_match('/предельные размеры субсидии/i', $text)) return 'предельные размеры по категориям';
    if (preg_match('/Определяется в соответствии с формулой/i', $text)) return 'по формуле (10–15% от стоимости)';
    if (preg_match('/Максимальный размер предоставляемой скидки/i', $text)) return 'до 15% цены техники (лизинг)';
    if (preg_match('/льготн/i', $text) && preg_match('/кредит/i', $text)) return 'льготная ставка по кредиту';
    if (mb_strlen(trim($supportSize)) > 15) return mb_substr(trim($supportSize), 0, 55) . (mb_strlen(trim($supportSize)) > 55 ? '…' : '');
    return null;
}

$updated = 0;
foreach ($data as $i => &$m) {
    $af = trim($m['amount_formatted'] ?? '');
    if ($af !== '') continue;

    $titleNormal = $m['title_normal'] ?? '';
    $supportSize = $m['support_size'] ?? '';
    $fullDesc = $m['full_description'] ?? '';

    $known = getKnownAmountByTitle($titleNormal);
    if ($known !== null) {
        $m['amount_formatted'] = $known;
        $updated++;
        continue;
    }

    $derived = deriveAmountFromSupportSize($supportSize, $fullDesc);
    if ($derived !== null) {
        $m['amount_formatted'] = $derived;
        $updated++;
    } else {
        $m['amount_formatted'] = 'по условиям программы';
        $updated++;
    }
}
unset($m);

$out = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($out === false) {
    fwrite(STDERR, "JSON encode error\n");
    exit(1);
}
file_put_contents($path, $out);
echo "Updated amount_formatted for $updated measures.\n";
