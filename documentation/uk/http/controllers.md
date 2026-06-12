# Контролери та ін'єкція аргументів

Controller не повинен наслідувати framework base class:

```php
namespace App\Http\Controllers;

use Horizon\Http\Request\Request;
use Horizon\Http\Response\Response;

final class PostController
{
    public function __construct(
        private PostRepository $posts,
    ) {}

    public function show(Request $request, string $id): Response
    {
        return response(
            $request->method().':'.$this->posts->find($id)->title
        );
    }
}
```

Маршрут:

```php
Route::get('/posts/{id}', [PostController::class, 'show']);
```

Controller і constructor dependencies створюються container-ом. Аргументи
action resolved у такому порядку:

1. route parameter з таким самим ім'ям;
2. поточний request для сумісного class type;
3. інший class type через container;
4. positional route parameter;
5. default value;
6. `null`, якщо type nullable.

Допустиме повернення action:

- `ResponseContract`;
- scalar;
- `Stringable`, зокрема Prism view;
- `null`.

Scalar, `Stringable` і `null` автоматично загортаються у звичайну HTML
response зі status 200. Array або довільний object спричиняє
`RuntimeException`; для них використовуйте `response()->json(...)`.
