# Layouts, blocks та imports

Layout:

```prism
<!-- ui/views/layouts/app.prism.php -->
<!doctype html>
<html>
<head>
    <title>#slot('title')</title>
</head>
<body>
    #slot('content')
</body>
</html>
```

Child view:

```prism
#layout('layouts.app')

#block('title')
Dashboard
#endblock

#block('content')
<h1>{{ $heading }}</h1>
#endblock
```

Missing slot повертає порожній рядок. `#endblock` без відкритого block кидає
`RuntimeException`.

Import рендерить окремий view:

```prism
#import('partials.alert', ['message' => $message])
```

Imported view отримує лише data, передані другим argument. Поточний local
scope parent view автоматично не успадковується.

View names у layout/import підтримують dot notation та ті самі extensions:
`.prism.php`, `.php`, `.html`.

На відміну від Blade/Twig, Prism не підтримує `@extends`, `@section`,
`@yield` або `@include`. Використовуйте `#layout`, `#block`, `#slot`,
`#import`.
