<?php

declare(strict_types=1);

namespace Horizon\Console\Output;

final class Ansi
{
    // Reset
    public const string RESET = "\033[0m";

    // Styles
    public const string BOLD = "\033[1m";
    public const string DIM = "\033[2m";
    public const string ITALIC = "\033[3m";
    public const string UNDERLINE = "\033[4m";
    public const string BLINK = "\033[5m";
    public const string REVERSE = "\033[7m";
    public const string HIDDEN = "\033[8m";

    // Foreground — normal
    public const string BLACK = "\033[30m";
    public const string RED = "\033[31m";
    public const string GREEN = "\033[32m";
    public const string YELLOW = "\033[33m";
    public const string BLUE = "\033[34m";
    public const string MAGENTA = "\033[35m";
    public const string CYAN = "\033[36m";
    public const string WHITE = "\033[37m";
    public const string DEFAULT = "\033[39m";

    // Foreground — bright
    public const string BRIGHT_BLACK = "\033[90m";
    public const string BRIGHT_RED = "\033[91m";
    public const string BRIGHT_GREEN = "\033[92m";
    public const string BRIGHT_YELLOW = "\033[93m";
    public const string BRIGHT_BLUE = "\033[94m";
    public const string BRIGHT_MAGENTA = "\033[95m";
    public const string BRIGHT_CYAN = "\033[96m";
    public const string BRIGHT_WHITE = "\033[97m";

    // Background — normal
    public const string BG_BLACK = "\033[40m";
    public const string BG_RED = "\033[41m";
    public const string BG_GREEN = "\033[42m";
    public const string BG_YELLOW = "\033[43m";
    public const string BG_BLUE = "\033[44m";
    public const string BG_MAGENTA = "\033[45m";
    public const string BG_CYAN = "\033[46m";
    public const string BG_WHITE = "\033[47m";

    // Background — bright
    public const string BG_BRIGHT_BLACK = "\033[100m";
    public const string BG_BRIGHT_RED = "\033[101m";
    public const string BG_BRIGHT_GREEN = "\033[102m";
    public const string BG_BRIGHT_YELLOW = "\033[103m";
    public const string BG_BRIGHT_BLUE = "\033[104m";
    public const string BG_BRIGHT_MAGENTA = "\033[105m";
    public const string BG_BRIGHT_CYAN = "\033[106m";
    public const string BG_BRIGHT_WHITE = "\033[107m";

    /**
     * Combine multiple ANSI codes into one sequence.
     * Ansi::combine(Ansi::BOLD, Ansi::BRIGHT_GREEN) => "\033[1;92m"
     */
    public static function combine(string ...$codes): string
    {
        $nums = array_map(
            static fn(string $code): string => trim($code, "\033[]m"),
            $codes,
        );

        return "\033[" . implode(';', $nums) . 'm';
    }
}
