# Огляд архітектури

Octane складається з невеликого application kernel і компонентів, які
автоматично виявляються через `src/Horizon/*/components.json`.

Робочі компоненти:

- `Arch`: application, container, bootstrap і pipeline;
- `Routing`: реєстрація та пошук маршрутів;
- `Http`: request, response, middleware collection;
- `Exception`: глобальний exception handler і renderers;
- `Prism`: views, compiler, layouts та компоненти;
- `Support`: hashing і низка допоміжних типів.

Основні контракти знаходяться у `Horizon\Contracts`. Framework реєструє
реалізації в container, тому application-код доцільно type-hint-ити
контрактами.

HTTP-запит проходить через `RequestContext`, а не передається між усіма
етапами окремими аргументами. Context зберігає `RequestContract`, знайдений
route DTO, параметри та остаточну response.

Framework не має окремих kernel-ів для console, queue або database. Наявність
фасадів чи директорій у skeleton не означає, що відповідна підсистема
реалізована.
