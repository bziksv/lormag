<?
/* universal */
$MESS["ARTURGOLUBEV_CHATGPT_CHECKDEMO_EXPIRED"] = "Тестовый период работы решения закончился. Для продолжения использования решения Вы можете приобрести полную версию решения в <a href=\"https://marketplace.1c-bitrix.ru/solutions/#module_id#/\" target=\"_blank\">официальном маркетплейсе 1c-Битрикс</a>";
$MESS["ARTURGOLUBEV_CHATGPT_CHECKDEMO_DEMO"] = "Решение установлено в демонстрационном режиме. До конца демонстрационного режима осталось #date_diff# дней. Приобрести полную версию решения можно в <a href=\"https://marketplace.1c-bitrix.ru/solutions/#module_id#/\" target=\"_blank\">официальном маркетплейсе 1c-Битрикс</a>";
$MESS["ARTURGOLUBEV_CHATGPT_CHECKDEMO_PROD"] = "Период технической поддержи и получения обновлений решения истёк #date_to#. Продлить техническую поддержку решения и период обновлений можно купив продление решения (за 50% от стоимости лицензии) в <a href=\"https://marketplace.1c-bitrix.ru/tobasket.php?ID=#module_id#&prolong_period=12\" target=\"_blank\">официальном маркетплейсе 1c-Битрикс</a> (информация в данном блоке обновляется один раз в 10 минут)";
$MESS["ARTURGOLUBEV_CHATGPT_CHECKDEMO_PROD_UPDATES"] = "Период технической поддержи и получения обновлений решения истёк #date_to#. Продлить техническую поддержку решения и период обновлений можно купив продление решения (за 50% от стоимости лицензии) в <a href=\"https://marketplace.1c-bitrix.ru/tobasket.php?ID=#module_id#&prolong_period=12\" target=\"_blank\">официальном маркетплейсе 1c-Битрикс</a>. У решения доступны новые обновления! (информация в данном блоке обновляется один раз в 10 минут)";
$MESS["ARTURGOLUBEV_CHATGPT_CHECKDEMO_HAVE_UPDATES"] = "У решения доступны новые обновления! (информация в данном блоке обновляется один раз в 10 минут)";

/* settings */
$MESS["ARTURGOLUBEV_CHATGPT_SELECTBOX_NO_SELECT"] = "Не выбрано";
$MESS["ARTURGOLUBEV_CHATGPT_RIGHTS_ERROR"] = "Недостаточно прав, доступ закрыт";
$MESS["ARTURGOLUBEV_CHATGPT_SETTING_MAIN_TAB"] = "Общие настройки";
$MESS["ARTURGOLUBEV_CHATGPT_SETTING_CHATGPT_TAB"] = "Настройки chatGPT";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_API_KEY"] = "<span data-hint='Можно указать как один, так и несколько ключей (каждый с новой строки). При использовании нескольких ключей, при достижении лимита ключа решение автоматически переключится на использование следующего ключа из списка.'></span>Ключи API:<br>(каждый с новой строки)";
// $MESS["ARTURGOLUBEV_CHATGPT_OPTION_API_KEY"] = "Ключ API:";
$MESS["ARTURGOLUBEV_CHATGPT_PROXY_SETTINGS"] = "Настройка proxy";
$MESS["ARTURGOLUBEV_CHATGPT_PROXY_SETTINGS_NOTE"] = "С 15 ноября 2023 года Open AI начали блокировать запросы с IP RU-региона. Если у вас сервер расположен в РФ, для запросов к chatgpt теперь нужен <a href=\"https://arturgolubev.ru/knowledge/course35/lesson224/\" target=\"_blank\">Прокси сервер (proxy)</a>";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY"] = "<span data-hint='В данный момент поддерживается указание одного proxy-сервера'></span>Прокси сервер:";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_NOTE"] = "В формате: ip:port login:password";

$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_IP"] = "IP прокси сервера:";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_IP_NOTE"] = "В формате: 123.123.123.123";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PORT"] = "Порт прокси сервера:";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PORT_NOTE"] = "В формате: 8080";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_LOGIN"] = "Логин прокси сервера:";
// $MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_LOGIN_NOTE"] = "В формате: login";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PASSWORD"] = "Пароль прокси сервера:";
// $MESS["ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PASSWORD_NOTE"] = "В формате: 123.123.123.123";

$MESS["ARTURGOLUBEV_CHATGPT_ALGORITM_SETTINGS"] = "Настройка алгоритмов";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_MODEL"] = "Языковая модель (model):";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE"] = "Роль автора сообщения (role):";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE_USER"] = "Пользователь (user)";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE_SYSTEM"] = "Система (system)";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE_ASSISTANT"] = "Помошник (assistant)";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_TEMPERATURE"] = "<span data-hint='Чем выше число тем более рандомный ответ. Например 0.2 - более четкий ответ, 0.8 - более размытый'></span> Размытость ответа (temperature):";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_MAX_TOKENS"] = "<span data-hint='В данном параметре мы указываем максимальное ограничение длины ответа gpt на вопрос в токенах. Если очень сильно упрощать 1 токен = 1-3 символа. Максимальная длина ответа у всех без исключений моделей - 4096 токенов. Не путайте данный параметр с длинной контекста, которая например у gpt-4o - 128000 токенов (что обозначает что она может принять на вход 123904, а отдать 4096 токенов текста).'></span> Максимальная длина ответа в токенах (max_tokens):";

$MESS["ARTURGOLUBEV_CHATGPT_OPTION_ALG_IMAGE_MODEL"] = "<span data-hint='Для бесплатных триал ключей рекомендуется dall-e-2, для платных аккаунтов OpenAI - dall-e-3'></span>Модель генерации изображений:";

$MESS["ARTURGOLUBEV_CHATGPT_SYSTEM_SETTINGS"] = "Системные";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_MAX_WAIT_TIME"] = "<span data-hint='Максимальное время до прерывания в течении которого система будет ждать ответ от GPT. Рекомендуется 180-300 секунд'></span>Максимальное время ожидания ответа (с):";

$MESS["ARTURGOLUBEV_CHATGPT_DEFAULT_FORM_SETTINGS"] = "Настройка форм генерации";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_SHOW_QUERY"] = "Отображать запросы к системам:";
$MESS["ARTURGOLUBEV_CHATGPT_OPTION_SHOW_TOKENS"] = "Отображать сколько потрачено токенов:";
$MESS["ARTURGOLUBEV_CHATGPT_SETTING_RIGHTS_TAB"] = "Права доступа";
$MESS["ARTURGOLUBEV_CHATGPT_RIGHTS_SETTINGS"] = "Настройка модуля";
$MESS["ARTURGOLUBEV_CHATGPT_RIGHTS_SETTINGS_TEXT"] = "Администраторам доступен весь функционал решения и настройка";
$MESS["ARTURGOLUBEV_CHATGPT_RIGHTS_QUESTION"] = "Генерация информации с помощью chartGPT:";

$MESS["ARTURGOLUBEV_CHATGPT_MODEL_GPT_35"] = "gpt 3.5 turbo";
$MESS["ARTURGOLUBEV_CHATGPT_MODEL_GPT_4"] = "gpt-4";
$MESS["ARTURGOLUBEV_CHATGPT_MODEL_GPT_4_TURBO"] = "gpt-4 turbo";
$MESS["ARTURGOLUBEV_CHATGPT_MODEL_GPT_4_O"] = "gpt-4o";
$MESS["ARTURGOLUBEV_CHATGPT_MODEL_GPT_4_O_MINI"] = "gpt-4o mini";

$MESS["ARTURGOLUBEV_CHATGPT_OPTION_CHATGPT_CUSTOM_LINK"] = "<span data-hint='Опция даёт возможность делать запросы к неофициальным chatgpt-подобным сервисам, вместо официального chatgpt. Обязательное условие - полное совпадение API запросов с официальным chatgpt за исключением адреса. Формат заполнения: https://api.proxyapi.ru/openai/v1'></span>Кастомный адрес сервера:";
 

/* sber */
$MESS["ARTURGOLUBEV_CHATGPT_SETTING_SBER_TAB"] = "Настройки Сбер GigaChat";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_CONNECTION_SETTINGS"] = "Настройки подключения";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_SCOPE"] = "Тип подключения (Scope):";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_SCOPE_PERS"] = "Физические лица (GIGACHAT_API_PERS)";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_SCOPE_CORP"] = "Юридические лица (GIGACHAT_API_CORP)";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_AUTHORIZATION"] = "Авторизационные данные:";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_ALG_MODEL"] = "Языковая модель (model):";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_ALG_MODEL_VALUE"] = "GigaChat:latest (последняя доступная)";
$MESS["ARTURGOLUBEV_CHATGPT_SBER_ALG_MAX_TOKENS"] = "<span data-hint='Рекомендуется не более 4096. Длина контекста модели Lite и Pro у сбера 8192 токенов, а Lite+ 32768 токенов.'></span> Максимальная длина ответа в токенах (max_tokens):";
/* sber */
$MESS["ARTURGOLUBEV_CHATGPT_SETTING_YANDEX_TAB"] = "Настройки YandexGPT";

/* ask page */
$MESS["ARTURGOLUBEV_CHATGPT_ASK_PAGE_TITLE"] = "Произвольный запрос";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_PAGE_TEXT"] = "С данной страницы вы можете отправить произвольный запрос к ChatGPT или GigaChat. Можно задавать любые вопросы, например:
<div class='agcg-example-queries'><span class='agcg-example-query'>Как тебя зовут?</span> <span class='agcg-example-query'>Какой длины жираф</span> <span class='agcg-example-query'>Напиши описание для товара \"Батон собственного производства\" не более 1000 символов</span></div>";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_INPUT_SYSTEM"] = "AI-модель:";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_INPUT_SYSTEM_CHATGTP"] = "ChatGPT";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_INPUT_SYSTEM_SBER"] = "Сбер GigaChat";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_INPUT_QUERY"] = "Ваш запрос:";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_QUERY_AREA"] = "Запрос:";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_RESULT_AREA"] = "Результат:";
$MESS["ARTURGOLUBEV_CHATGPT_ASK_SEND_QUERY"] = "Отправить запрос";

/* */
$MESS["ARTURGOLUBEV_CHATGPT_IMAGE_PAGE_TITLE"] = "Генерация изображений";
$MESS["ARTURGOLUBEV_CHATGPT_IMAGE_PAGE_TEXT"] = "С данной страницы вы можете отправить запрос на генерацию изображений к ChatGPT. Пример запроса:
<div class='agcg-example-queries'><span class='agcg-example-query'>Нарисуй жирафа</span></div>";
$MESS["ARTURGOLUBEV_CHATGPT_IMAGE_INPUT_QUERY"] = "Запрос на генерацию изображения";
$MESS["ARTURGOLUBEV_CHATGPT_IMAGE_INPUT_SIZE"] = "Размер изображения";

/* help tab */
$MESS["ARTURGOLUBEV_CHATGPT_HELP_TAB_TITLE"] = "Полезная информация";
$MESS["ARTURGOLUBEV_CHATGPT_HELP_TAB_VALUE"] = "
Карточка решения на Marketplace - <a href='https://marketplace.1c-bitrix.ru/solutions/arturgolubev.chatgpt/#tab-about-link' target='_blank'>ссылка</a><br/>
Видео-инструкция по установке и настройке - <a href='https://arturgolubev.ru/knowledge/course35/' target='_blank'>ссылка</a><br/>
Часто задаваемые вопросы - <a href='https://arturgolubev.ru/knowledge/course35/' target='_blank'>ссылка</a><br/>
Вопросы по покупке, оплате, активации модуля и т.п. - <a href='https://arturgolubev.ru/knowledge/course1/' target='_blank'>ссылка</a><br/>
Техническая поддержка - <a href='https://arturgolubev.ru/knowledge/course1/' target='_blank'>ссылка</a><br/>
";

/* checks */
$MESS["ARTURGOLUBEV_CHATGPT_CURL_NOT_FOUND"] = "Серверная библиотека CURL, необходимая для работы решения, не найдена! Обратитесь в техническую поддержку хостинга или сервера";
$MESS["ARTURGOLUBEV_CHATGPT_DEMO_IS_EXPIRED"] = "Демонстрационный период работы решения закончился. Для дальнейшего использования необходимо приобрести полую версию решения в <a href=\"http://marketplace.1c-bitrix.ru/solutions/arturgolubev.chatgpt/\" target=\"_blank\">marketplace.1c-bitrix.ru</a>";
?>