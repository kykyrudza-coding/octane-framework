# Prism-компоненти

Компонент наслідує base class:

```php
use Horizon\Prism\Prism\Component\Component;

final class Button extends Component
{
    public string $text = '';
    public string $variant = 'primary';

    public function name(): string
    {
        return 'Button';
    }

    public function render(): string
    {
        return sprintf(
            '<button class="%s">%s%s</button>',
            htmlspecialchars($this->variant),
            htmlspecialchars($this->text),
            $this->slot,
        );
    }
}
```

Реєстрація у `boot()` provider-а:

```php
use Horizon\Contracts\Prism\PrismContract;

$prism = $this->app->make(PrismContract::class);
$prism->component('Button', Button::class);
```

Використання:

```prism
<Button text="Save" variant="primary" />

<Button text="Continue">Additional content</Button>
```

`withProps()` clone-ить component і записує значення лише у властивості, які
вже існують. Unknown props ігноруються.

Обмеження parser-а:

- component class створюється через `new $class()`, без container injection;
- alias/tag має починатися з великої літери й містити лише letters;
- attributes підтримують лише `word="literal"` або `word='literal'`;
- dynamic bindings, boolean attrs і hyphenated attrs відсутні;
- slot є literal string; складний nested Prism code у slot може не
  виконуватися так, як очікується;
- HTML escaping усередині `render()` є відповідальністю компонента.
