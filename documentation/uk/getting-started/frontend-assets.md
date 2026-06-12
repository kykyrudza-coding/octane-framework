# Vite і frontend-ресурси

Skeleton використовує Vite 8 і Tailwind CSS 4. Точкою входу є
`ui/js/app.js`, а production-файли записуються до `public/build`.

```bash
npm run dev
npm run build
```

У Prism-шаблоні ресурси підключаються helper-ом:

```php
{!! vite('ui/js/app.js') !!}
```

`vite()` спочатку намагається відкрити TCP-з'єднання до `127.0.0.1:5173`.
Якщо порт доступний, helper повертає теги Vite client і entry script. Інакше
він читає:

1. `public/build/.vite/manifest.json`;
2. як fallback, `public/build/manifest.json`.

Якщо dev server не працює і manifest не знайдений, helper все одно поверне
посилання на `127.0.0.1:5173`. Тому для production перед deployment необхідно
виконати `npm run build`.

Порт, host і шлях до dev server у поточній реалізації helper-а жорстко
зафіксовані; значення `VITE_DEV_SERVER_URL` з `.env` не використовується.
