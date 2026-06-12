# Встановлення

## Готовий skeleton застосунку

Репозиторій `octane-application` вже містить bootstrap, маршрути, Prism-view і
Vite-конфігурацію:

```bash
git clone https://github.com/kykyrudza/octane-application.git my-app
cd my-app
composer install
npm install
```

У поточному `composer.json` skeleton framework підключено як локальний path
repository:

```json
{
  "repositories": [
    {"type": "path", "url": "../octane-framework"}
  ],
  "require": {
    "octane/framework": "@dev"
  }
}
```

Ця конфігурація працює, коли `octane-framework` і `octane-application`
розташовані поруч. Для окремого проєкту замініть repository на доступне вам
джерело пакета.

## Запуск HTTP-застосунку

```bash
php -S 127.0.0.1:8000 -t public
```

Front controller знаходиться у `public/index.php`. Вебсервер у production має
спрямовувати всі запити, для яких немає статичного файла, до цього front
controller.

## Frontend

```bash
npm run dev
```

або production-збірка:

```bash
npm run build
```

Файл `octane` поки не є робочою точкою входу CLI. Деталі:
[CLI: поточний стан](../cli/README.md).
