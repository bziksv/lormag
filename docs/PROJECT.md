# lormag.ru — документация проекта

Bitrix-интернет-магазин LOR-оборудования (шаблон **enext**, модуль **altop.enext**).

## Репозиторий и окружения

| | |
|---|---|
| GitHub | https://github.com/bziksv/lormag |
| **Git root** | `lormag.ru/` (= корень сайта на prod) |
| Prod IP | `217.28.220.186` (SSH: `vilmed`) |
| Prod path | `/var/www/lormag_ru_usr/data/www/lormag.ru` |
| Домен | https://lormag.ru |
| Локально | http://127.0.0.1:8085/ |

Родительская папка `lormag/` — **не в git**: дампы БД (`lormag_ru.sql.gz`), `.cursorignore`.

## Структура

```
lormag/                        # workspace (Mac)
├── lormag_ru.sql.gz           # дамп БД (не в git)
├── .cursorignore
└── lormag.ru/                 # git root
    ├── .cursor/rules/         # правила Cursor
    ├── .local/                # nginx/php-fpm (Mac)
    ├── docs/                  # документация
    ├── scripts/               # dev + deploy
    ├── bitrix/
    ├── upload/                # не в git
    └── personal/
```

## Git — что в репозитории

**В git:** код сайта, `bitrix/modules/`, шаблоны, компоненты, `scripts/`, `docs/`, `.local/*.example`, `.local/nginx/`, `.local/php/`.

**Не в git:** `upload/`, кэш Bitrix, секреты (`.settings.php`, `dbconn.php`, `license_key.php`, `.htaccess`), дампы `*.sql`, `.local/db.env`, `.local/run/`.

## Локальная разработка (Mac, soft)

Порты: **8085** (nginx), **9085** (php-fpm). MySQL 3306 (Homebrew).

```bash
cd lormag.ru
cp .local/db.env.example .local/db.env   # один раз
./scripts/setup-local-db.sh --background # один раз, импорт дампа
./scripts/start-dev.sh
./scripts/stop-dev.sh
```

Soft-режим: php-fpm `ondemand`, max 2 workers, 256M RAM, opcache 64M.

### Занятые порты (соседние проекты)

| Порт | Проект |
|------|--------|
| 8080 | almamed |
| 8082 | vilmed |
| 8083 | kosmamed |
| 8084 | polimer |
| **8085** | **lormag** |
| 8086 | metplus-vrn |
| 8087 | oftalmag |
| 8088 | metprof-vrn |
| **9085** | **lormag php-fpm** |

## Деплой на prod

**Два отдельных шага — не путать:**

| Шаг | Когда | Команда |
|-----|-------|---------|
| 1. Git | После **любых** правок кода | `git commit` + `git push origin main` |
| 2. Prod | **Только** когда пользователь явно попросил | `./scripts/deploy-prod.sh` |

Push в GitHub **не выкатывает** сайт на prod автоматически.

```bash
cd lormag.ru
git add … && git commit -m "…" && git push origin main   # после правок

# только когда пользователь просит:
./scripts/deploy-prod.sh
```

**Запрещено:** автодеплой на prod, правки на prod без commit, `scp` файлов кода.

Скрипт деплоя:
- `git push` → на сервере `git fetch` + `merge --ff-only origin/main`
- бэкап и восстановление `.settings.php`, `dbconn.php`, `license_key.php`
- сброс кэша Bitrix

## Личный кабинет (ЧПУ)

Компонент `bitrix:sale.personal.section` с SEF в `/personal/index.php`.

Обязательные правила в `urlrewrite.php`:

```php
'#^/personal/order/#' → /personal/order/index.php
'#^/personal/#'       → /personal/index.php
```

Маршруты: `/personal/orders/`, `/personal/subscribe/`, `/personal/private/`.  
Корзина `/personal/cart/` — отдельный физический `index.php`.

## Кастомные модули (примеры)

- `altop.enext` — шаблон/магазин
- `isaev.seotemplate` — SEO-шаблоны
- `asd.iblock`, `asd.currencyrate`
- `arturgolubev.chatgpt`
- `niges.cookiesaccept`, `ismagin.filecleaner`

Шаблон сайта: `bitrix/templates/enext`.

## Инструменты

### Реестр картинок сайта

| | |
|---|---|
| URL | http://127.0.0.1:8085/tools/site-images.php |
| Prod | https://lormag.ru/tools/site-images.php |
| Файл | `tools/site-images.php` |

Собирает изображения из `b_file` / `upload/`. В таблице:

- где используется (элемент инфоблока, свойство, UF)
- прямая ссылка на файл
- кнопка «Скачать»
- даты создания/изменения (файл + БД)
- экспорт в Excel (`?export=excel`)

Доступ: localhost или админ Bitrix.

## Проверка после изменений

```bash
curl -sS -o /dev/null -w '%{http_code}\n' https://lormag.ru/
curl -sS -o /dev/null -w '%{http_code}\n' https://lormag.ru/personal/orders/
curl -sS -o /dev/null -w '%{http_code}\n' https://lormag.ru/personal/private/
```

Локально: `./scripts/start-dev.sh` и curl на `:8085`.
