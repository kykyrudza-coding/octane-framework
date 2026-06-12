# Кеш Prism

Source view компілюється у PHP-файл:

```text
var/cache/prism/{sha1-absolute-source-path}.php
```

Recompile виконується, якщо:

- compiled file не існує;
- `filemtime(source) > filemtime(compiled)`.

Каталог кешу створюється автоматично з permissions `0755`. Окремої CLI-команди
для очищення кешу наразі немає.

Ручне очищення безпечне, коли application не обробляє запити:

```powershell
Remove-Item -Recurse -Force var\cache\prism
```

Наступний render створить файли повторно.

Cache key залежить від абсолютного path, а не від contents. Після переміщення
проєкту старі cache files можуть залишитися невикористаними. Також зміна
custom directive/component не оновлює `filemtime` source view автоматично;
у такому випадку очистьте Prism cache.
