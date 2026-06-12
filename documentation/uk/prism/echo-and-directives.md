# Виведення та директиви Prism

Escaped output:

```prism
<h1>{{ $title }}</h1>
```

Значення приводиться до string і проходить через
`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

Raw output:

```prism
{!! $trustedHtml !!}
```

Raw output не екранується. Не передавайте до нього неперевірені дані.

Реальний prefix директив Prism — `#`, не `@`.

```prism
#if($user !== null)
    <p>{{ $user->name }}</p>
#elseif($guest)
    <p>Guest</p>
#else
    <p>Unknown</p>
#endif
```

Єдиний вбудований loop:

```prism
#each($users as $user)
    <li>{{ $user->name }}</li>
#endforeach
```

Вбудовані directives: `if`, `elseif`, `else`, `endif`, `each`,
`endforeach`, `layout`, `block`, `endblock`, `slot`, `import`.

Custom directive рекомендовано реалізувати через `DirectiveContract`:

```php
final class UpperDirective implements DirectiveContract
{
    public function name(): string { return 'upper'; }

    public function compile(string $expression): string
    {
        return "<?php echo strtoupper($expression); ?>";
    }
}

app(PrismContract::class)->directive(new UpperDirective());
```

Parser expressions зупиняється на першій `)`, тому складні nested function
calls у directive expressions можуть компілюватися некоректно.
