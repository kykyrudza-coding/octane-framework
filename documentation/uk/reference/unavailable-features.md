# Відсутні та незавершені можливості

## Відсутні підсистеми

- database connection/query builder/ORM/migrations;
- console kernel і commands;
- events;
- cache;
- logging;
- authentication/authorization;
- validation;
- sessions;
- queues;
- filesystem;
- OpenAPI.

## Порожні або непрацюючі заготовки

- facades `Auth`, `Cache`, `Config`, `DB`, `Event`, `Log`, `Prism`, `Route`;
- attributes `Cast`, `Guarded`, `Hidden`;
- helpers `Arr`, `Date`, `Path`, `Str`, `Uuid`;
- pagination classes;
- value objects `Interval`, `Money`;
- support exceptions, що не наслідують `Throwable`;
- `ItemsList` із невиконаними methods;
- classes `Observable`, `Singleton`, `Tappable` розміщені у namespace
  `Traits`, але оголошені як classes, не traits.

README верхнього рівня згадує Event System, Query Builder, Halcyon ORM,
Validation і CLI, однак поточний source code не надає ці можливості.
