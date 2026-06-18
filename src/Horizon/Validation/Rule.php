<?php

declare(strict_types=1);

namespace Horizon\Validation;

use Horizon\Contracts\Validation\ValidationRuleContract;

final class Rule
{
    public static function make(): RuleSet
    {
        return new RuleSet;
    }

    public static function required(): RuleSet
    {
        return self::make()->required();
    }

    public static function nullable(): RuleSet
    {
        return self::make()->nullable();
    }

    public static function optional(): RuleSet
    {
        return self::make()->optional();
    }

    public static function string(): RuleSet
    {
        return self::make()->string();
    }

    public static function integer(): RuleSet
    {
        return self::make()->integer();
    }

    public static function numeric(): RuleSet
    {
        return self::make()->numeric();
    }

    public static function boolean(): RuleSet
    {
        return self::make()->boolean();
    }

    public static function array(): RuleSet
    {
        return self::make()->array();
    }

    public static function email(): RuleSet
    {
        return self::make()->email();
    }

    public static function min(int|float $min): RuleSet
    {
        return self::make()->min($min);
    }

    public static function max(int|float $max): RuleSet
    {
        return self::make()->max($max);
    }

    public static function between(int|float $min, int|float $max): RuleSet
    {
        return self::make()->between($min, $max);
    }

    public static function same(string $field): RuleSet
    {
        return self::make()->same($field);
    }

    public static function exists(string $table, ?string $column = null): RuleSet
    {
        return self::make()->exists($table, $column);
    }

    /**
     * @param  class-string<ValidationRuleContract>|ValidationRuleContract  $rule
     */
    public static function rule(string|ValidationRuleContract $rule): RuleSet
    {
        return self::make()->rule($rule);
    }
}
