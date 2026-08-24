<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/legal_page.php';
lormag_render_legal_html_page(
    'legal-consent.html',
    'Согласие на обработку персональных данных',
    'Согласие на обработку персональных данных'
);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
