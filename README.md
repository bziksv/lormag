# lormag.ru

Bitrix-магазин LOR-оборудования, шаблон enext.

Полная документация: **[docs/PROJECT.md](docs/PROJECT.md)**

## Быстрый старт

```bash
cd lormag.ru
cp .local/db.env.example .local/db.env
./scripts/setup-local-db.sh --background
./scripts/start-dev.sh
```

Сайт: http://127.0.0.1:8085/

## Деплой

```bash
git push origin main
./scripts/deploy-prod.sh   # только по запросу
```
