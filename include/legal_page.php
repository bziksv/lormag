<?php

function lormag_render_legal_html_page(string $htmlFile, string $pageTitle, ?string $metaTitle = null): void
{
    global $APPLICATION;

    $metaTitle = $metaTitle ?: $pageTitle;
    $APPLICATION->SetTitle($pageTitle);
    $APPLICATION->SetPageProperty('title', $metaTitle . ' — Лормаг');
    $APPLICATION->SetPageProperty('description', $pageTitle . ' интернет-магазина Лормаг.');

    $path = $_SERVER['DOCUMENT_ROOT'] . '/legal-html/' . ltrim($htmlFile, '/');
    if (!is_readable($path)) {
        echo '<div class="container"><p>Документ не найден.</p></div>';
        return;
    }

    $html = file_get_contents($path);

    if (preg_match('/<style>(.*?)<\/style>/s', $html, $styleMatch)) {
        echo '<style>' . $styleMatch[1] . '</style>';
    }

    echo '<div class="container">';

    if (preg_match('/<article class="legal-page">(.*)<\/article>/s', $html, $articleMatch)) {
        echo '<article class="legal-page">' . $articleMatch[1] . '</article>';
    } else {
        echo '<p>Не удалось загрузить документ.</p>';
    }

    $legalConfig = include $_SERVER['DOCUMENT_ROOT'] . '/include/legal_config.php';
    ?>
    <div class="legal-page__nav">
        <a href="<?= htmlspecialcharsbx($legalConfig['urls']['cookie']) ?>">Политика cookie</a>
        <a href="<?= htmlspecialcharsbx($legalConfig['urls']['recommendation']) ?>">Рекомендательные технологии</a>
        <a href="<?= htmlspecialcharsbx($legalConfig['urls']['personal_data']) ?>">Политика обработки персональных данных</a>
        <a href="<?= htmlspecialcharsbx($legalConfig['urls']['consent']) ?>">Согласие на обработку ПДн</a>
    </div>
    <?php
    echo '</div>';
}
