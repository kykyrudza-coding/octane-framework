# Validation

Validation пакет надає fluent rule builder, validator, `Request::validate()` і `FormRequest`.

## Direct request validation

```php
use Horizon\Validation\Rule;

$validated = $request->validate([
    'name' => Rule::required()->string()->min(3),
    'email' => Rule::required()->email(),
]);

$name = $validated->get('name');
```

`Request::validate()` використовує container-bound `ValidatorFactoryContract`, якщо application завантажений. Тому config validation і presence verifier застосовуються і для direct request validation, і для FormRequest.

Якщо validation fails, кидається `ValidationException`.

## Rules

```php
Rule::required();
Rule::optional();
Rule::nullable();
Rule::string();
Rule::integer();
Rule::numeric();
Rule::boolean();
Rule::array();
Rule::email();
Rule::min(3);
Rule::max(255);
Rule::between(3, 255);
Rule::same('password_confirmation');
Rule::exists('users', 'id');
Rule::custom(MyRule::class);
```

`RuleSet` immutable: кожен chained call повертає новий набір правил.

## Validated data

```php
$validated->all();
$validated->get('profile.name');
$validated->has('email');
$validated->only(['name', 'email']);
$validated->except(['password']);
$validated->toDto(CreateUserDto::class);
```

## FormRequest

```php
use Horizon\Validation\FormRequest;
use Horizon\Validation\Rule;

final class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => Rule::required()->string()->min(3),
            'email' => Rule::required()->email(),
        ];
    }

    public function dto(): ?string
    {
        return CreateUserDto::class;
    }
}
```

Controller:

```php
public function store(CreateUserRequest $request): Response
{
    $dto = $request->toDto();

    return response()->json($dto->toArray(), 201);
}
```

`InvokeController` розпізнає subclasses of `FormRequest`, створює request з поточного HTTP request, викликає `validateResolved()` і inject-ить уже validated instance.

## Конфігурація

`config/validation.php`:

```php
return [
    'stop_on_first_failure' => false,

    'presence' => [
        'driver' => 'null',
        'verifier' => null,
        'tables' => [],
    ],

    'messages' => [],
    'attributes' => [],
];
```

Активні ключі:

- `stop_on_first_failure`: зупиняє validator після першої помилки;
- `presence.driver`: `null` або `array`;
- `presence.verifier`: custom class, що реалізує `PresenceVerifierContract`;
- `presence.tables`: in-memory tables для `array` driver;
- `messages`: повідомлення за rule name або `field.rule`;
- `attributes`: human-readable names для повідомлень.

Приклад custom messages:

```php
'messages' => [
    'required' => 'Поле :attribute обовʼязкове.',
    'email.email' => 'Email має бути валідним.',
],

'attributes' => [
    'email' => 'електронна пошта',
],
```

## Presence verifier

`Rule::exists('users', 'id')` використовує `PresenceVerifierContract`. Default `null` verifier завжди повертає `false`, тому для production треба підключити custom verifier або майбутній database-backed verifier.

Для тестів можна використати `array` driver:

```php
'presence' => [
    'driver' => 'array',
    'tables' => [
        'users' => [
            ['id' => 1],
        ],
    ],
],
```
