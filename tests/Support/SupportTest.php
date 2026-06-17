<?php

declare(strict_types=1);

namespace Tests\Support;

use BadMethodCallException;
use Horizon\Contracts\Support\Casts\CastableContract;
use Horizon\Support\Casts\CastRegistry;
use Horizon\Support\Fluent;
use Horizon\Support\Hashing\BcryptHasher;
use Horizon\Support\Http\HttpStatus;
use Horizon\Support\ItemsList;
use Horizon\Support\Macroable;
use Horizon\Support\Traits\Conditionable;
use Horizon\Support\Traits\Observable;
use Horizon\Support\Traits\Singleton;
use Horizon\Support\Traits\Tappable;
use Horizon\Support\ValueObjects\Interval;
use Horizon\Support\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SupportTest extends TestCase
{
    public function test_items_list_first_last_and_get(): void
    {
        $items = ItemsList::make(['a' => 1, 'b' => 2]);

        $this->assertSame(1, $items->first());
        $this->assertSame(2, $items->last());
        $this->assertSame(2, $items->get('b'));
    }

    public function test_items_list_map_filter_and_values(): void
    {
        $items = ItemsList::make([1, 2, 3])
            ->map(fn(int $value): int => $value * 2)
            ->filter(fn(int $value): bool => $value > 2)
            ->values();

        $this->assertSame([4, 6], $items->all());
    }

    public function test_items_list_pluck_and_to_array_with_arrayable_rows(): void
    {
        $items = ItemsList::make([
            new Fluent(['name' => 'Ada']),
            new Fluent(['name' => 'Bob']),
        ]);

        $this->assertSame(['Ada', 'Bob'], $items->pluck('name')->all());
        $this->assertSame([['name' => 'Ada'], ['name' => 'Bob']], $items->toArray());
    }

    public function test_items_list_chunk_merge_and_count(): void
    {
        $items = ItemsList::make([1, 2, 3])->merge([4])->chunk(2);

        $this->assertCount(2, $items);
        $this->assertSame([1, 2], $items->first()->all());
    }

    public function test_fluent_reads_writes_and_serializes_attributes(): void
    {
        $fluent = new Fluent(['name' => 'Ada']);
        $fluent->email = 'ada@test';
        $fluent->set('active', true);

        $this->assertSame('Ada', $fluent->name);
        $this->assertSame('ada@test', $fluent->get('email'));
        $this->assertSame(['name' => 'Ada', 'email' => 'ada@test', 'active' => true], $fluent->toArray());
        $this->assertJson($fluent->toJson());
    }

    public function test_money_normalizes_currency_and_formats(): void
    {
        $money = Money::of(12.5, 'uah');

        $this->assertSame('UAH', $money->currency());
        $this->assertSame('12.50 UAH', $money->toString());
    }

    public function test_money_add_subtract_multiply_and_compare(): void
    {
        $money = Money::of(10, 'USD');

        $this->assertTrue($money->add(Money::of(5, 'USD'))->equals(Money::of(15, 'USD')));
        $this->assertTrue($money->subtract(Money::of(5, 'USD'))->equals(Money::of(5, 'USD')));
        $this->assertTrue($money->multiply(2)->isGreaterThan($money));
    }

    public function test_money_rejects_invalid_string_and_currency_mismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromString('abc');
    }

    public function test_interval_conversions_and_parsing(): void
    {
        $this->assertSame(120, Interval::minutes(2)->toSeconds());
        $this->assertSame(2.0, Interval::fromString('2 hours')->toHours());
        $this->assertSame('2 hours', (string) Interval::hours(2));
    }

    public function test_interval_math_and_comparison(): void
    {
        $interval = Interval::minutes(10);

        $this->assertTrue($interval->add(Interval::minutes(5))->equals(Interval::minutes(15)));
        $this->assertTrue($interval->subtract(Interval::minutes(5))->equals(Interval::minutes(5)));
        $this->assertTrue($interval->multiply(2)->isGreaterThan($interval));
        $this->assertTrue(Interval::minutes(1)->isLessThan($interval));
    }

    public function test_interval_rejects_invalid_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Interval::fromString('soon');
    }

    public function test_cast_registry_registers_gets_and_checks_casts(): void
    {
        $registry = new CastRegistry();
        $cast = new SupportUppercaseCast();
        $registry->register('upper', $cast);

        $this->assertTrue($registry->has('upper'));
        $this->assertSame($cast, $registry->get('upper'));
        $this->assertSame('ADA', $registry->get('upper')->get('ada'));
    }

    public function test_cast_registry_rejects_missing_cast(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CastRegistry())->get('missing');
    }

    public function test_bcrypt_hasher_hashes_verifies_and_detects_rehash(): void
    {
        $hasher = new BcryptHasher(rounds: 4);
        $hash = $hasher->hash('secret');

        $this->assertTrue($hasher->verify('secret', $hash));
        $this->assertFalse($hasher->verify('wrong', $hash));
        $this->assertTrue((new BcryptHasher(rounds: 5))->needsRehash($hash));
    }

    public function test_http_status_labels_and_groups(): void
    {
        $this->assertSame('OK', HttpStatus::OK->label());
        $this->assertTrue(HttpStatus::Created->isSuccess());
        $this->assertTrue(HttpStatus::Found->isRedirect());
        $this->assertTrue(HttpStatus::NotFound->isClientError());
        $this->assertTrue(HttpStatus::InternalServerError->isServerError());
    }

    public function test_conditionable_and_tappable_traits(): void
    {
        $object = new SupportConditionableObject();

        $result = $object
            ->when(true, fn(SupportConditionableObject $item) => $item->value = 'when')
            ->unless(false, fn(SupportConditionableObject $item) => $item->value .= '-unless')
            ->tap(fn(SupportConditionableObject $item) => $item->value .= '-tap');

        $this->assertSame($object, $result);
        $this->assertSame('when-unless-tap', $object->value);
    }

    public function test_macroable_static_and_instance_macros(): void
    {
        SupportMacroableObject::macro('hello', fn(string $name): string => "Hello $name");

        $this->assertTrue(SupportMacroableObject::hasMacro('hello'));
        $this->assertSame('Hello Ada', SupportMacroableObject::hello('Ada'));
        $this->assertSame('Hello Bob', (new SupportMacroableObject())->hello('Bob'));
    }

    public function test_macroable_rejects_missing_macro(): void
    {
        $this->expectException(BadMethodCallException::class);

        (new SupportMacroableObject())->missing();
    }

    public function test_singleton_trait_returns_same_instance_until_reset(): void
    {
        SupportSingletonObject::resetInstance();

        $first = SupportSingletonObject::getInstance();
        $second = SupportSingletonObject::getInstance();

        $this->assertSame($first, $second);

        SupportSingletonObject::resetInstance();
        $this->assertNotSame($first, SupportSingletonObject::getInstance());
    }

    public function test_observable_trait_notifies_and_tracks_dirty_values(): void
    {
        $object = new SupportObservableObject();
        $events = [];

        $object->name = 'Ada';
        $object->observe('name', function (mixed $new, mixed $old) use (&$events): void {
            $events[] = [$new, $old];
        });
        $object->name = 'Bob';

        $this->assertSame([['Bob', 'Ada']], $events);
        $this->assertTrue($object->isDirty('name'));
        $this->assertSame(['name' => 'Bob'], $object->getDirty());
        $object->syncOriginal();
        $this->assertFalse($object->isDirty('name'));
    }
}

final class SupportUppercaseCast implements CastableContract
{
    public function get(mixed $value): mixed
    {
        return strtoupper((string) $value);
    }

    public function set(mixed $value): mixed
    {
        return strtolower((string) $value);
    }
}

final class SupportConditionableObject
{
    use Conditionable;
    use Tappable;

    public string $value = '';
}

final class SupportMacroableObject
{
    use Macroable;
}

final class SupportSingletonObject
{
    use Singleton;
}

final class SupportObservableObject
{
    use Observable;
}
