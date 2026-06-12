# Вимоги

Framework вимагає:

- PHP `^8.4`;
- Composer;
- увімкнені стандартні розширення, потрібні `symfony/error-handler` і
  `vlucas/phpdotenv`;
- Node.js та npm лише для збірки frontend-ресурсів застосунку.

Фактичні runtime-залежності framework:

```json
{
  "php": "^8.4",
  "symfony/error-handler": "^8.1",
  "vlucas/phpdotenv": "^5.6"
}
```

Для розробки framework використовуються PHPUnit 12, PHPStan 2 і Laravel Pint.

Перевірка середовища:

```bash
php -v
composer --version
node --version
npm --version
```

Застосунок очікує доступний базовий `.env`. Bootstrap використовує
`Dotenv::load()`, а не `safeLoad()`, тому відсутній файл середовища може
зупинити створення застосунку.
