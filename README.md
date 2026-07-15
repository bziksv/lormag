# lormag.ru

Сайт на **1C-Bitrix** — интернет-магазин (LOR-оборудование, шаблон `enext`).

## Репозиторий

- GitHub: https://github.com/bziksv/lormag

## Сервер (production)

| Параметр | Значение |
|----------|----------|
| IP | `217.28.220.186` |
| Путь на сервере | `/var/www/lormag_ru_usr/data/www/lormag.ru` |
| Домен | `lormag.ru` |

## Локальный soft-запуск (Mac)

Скрипты и дамп БД лежат на уровень выше, в `../` (папка проекта `lormag/`).

Порты:

| Сервис | Порт |
|--------|------|
| HTTP (nginx) | **8085** |
| PHP-FPM | **9085** |
| MySQL | 3306 (общий soft-инстанс) |

```bash
# из папки проекта lormag/
cd ..

# один раз — импорт дампа (фон, soft)
./scripts/setup-local-db.sh --background

# старт
./scripts/start-dev.sh

# стоп
./scripts/stop-dev.sh
```

Сайт: http://127.0.0.1:8085/

Soft-режим: php-fpm `ondemand` (max 2 воркера, 256M), nginx 1 worker, opcache 64M.

Локальная БД: `lormag_ru` / пользователь `lormag_local`. Дамп: `../lormag_ru.sql.gz`.

## Занятые порты других проектов (локально)

| Порт | Проект |
|------|--------|
| 3002 | titlo / cabinet |
| 8080 | almamed |
| 8083 | kosmamed (php built-in) |
| 8084 | polimer |
| **8085** | **lormag** |
| 8086 | metplus-vrn |
| 8088 | metprof-vrn |
| 9084 | polimer php-fpm |
| **9085** | **lormag php-fpm** |
| 9086 | metplus php-fpm |
| 9088 | metprof php-fpm |

## Что НЕ коммитить

Секреты исключены в `.gitignore`:

- `bitrix/.settings.php`
- `bitrix/license_key.php`
- `bitrix/php_interface/dbconn.php`, `dbconn.local.php`
- `upload/`, кеш Bitrix, дампы `*.sql` / `*.sql.gz`
