# DTO, Validation, FormRequest, Resources

## DTO

DTO is separate from Model and Halcyon.

DTO purpose:

- input/output data
- application services
- actions
- API communication

Model is not DTO. DTO is not Model.

DTO package is deferred until after validation/FormRequest direction is clearer.

## DTO Attributes

Only mapping convenience attributes are accepted right now:

- `MapFrom`
- `MapTo`

Do not make DTO attributes the main validation system.

Example use case: map `userName` from input to `name` in DTO, or output `name` as a different key.

## Validation

Accepted future direction:

```php
Rule::required()->exists()->min(3)->max(5)->rule(PasswordRule::class)
```

Validation should support:

- fluent rules
- custom rule classes
- FormRequest
- direct request validation

## FormRequest

Accepted target DX:

```php
class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => Rule::required()->min(3)->max(255),
            'email' => Rule::required()->email(),
            'password' => Rule::required()->password(),
            'password_confirm' => Rule::required()->same('password'),
        ];
    }

    public function dto(): string
    {
        return CreateUserDto::class;
    }
}
```

Controller target:

```php
$dto = $request->validated()->toDto();
```

Without FormRequest:

```php
$dto = $request->validate([
    // rules
])->toDto(UserDto::class);
```

## ApiResource

ApiResource is not ORM.

Resources live outside model/ORM folders.

Accepted resource style is stateless transform:

```php
final class UserResource extends ApiResource
{
    public function transform(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
```

Target ergonomics:

```php
UserResource::make($user)
```

Do not design resources around `$this->model` or `$this->resource` as internal state.

`visible()` belongs in ApiResource if output whitelisting is needed.
